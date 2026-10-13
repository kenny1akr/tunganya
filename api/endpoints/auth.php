<?php
/**
 * api/endpoints/auth.php
 * Endpoint d'authentification
 *
 * Routes :
 *   POST /api/auth.php?action=login    → Connexion
 *   POST /api/auth.php?action=logout   → Déconnexion
 *   GET  /api/auth.php?action=me       → Infos utilisateur connecté
 *   POST /api/auth.php?action=forgot   → Mot de passe oublié
 */

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../helpers/Response.php';
require_once __DIR__ . '/../helpers/Jwt.php';

// ============================================================
// CORS
// ============================================================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ============================================================
// ROUTING
// ============================================================
$action = $_GET['action'] ?? 'login';

try {
    switch ($action) {
        case 'login':  login();  break;
        case 'logout': logout(); break;
        case 'me':     me();     break;
        case 'forgot': forgot(); break;
        default:
            Response::error('Action inconnue', 404);
    }
} catch (Exception $e) {
    Response::error(
        APP_DEBUG ? $e->getMessage() : 'Erreur serveur',
        500
    );
}

// ============================================================
// LOGIN
// ============================================================
function login()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        Response::error('Méthode non autorisée', 405);
    }

    // Récupérer les données (JSON ou formulaire)
    $input = getInput();
    $identifier = trim($input['identifier'] ?? '');
    $password   = $input['password'] ?? '';

    // ============================================================
    // VALIDATION
    // ============================================================
    $errors = [];
    if (empty($identifier)) {
        $errors['identifier'] = 'L\'identifiant est requis';
    }
    if (empty($password)) {
        $errors['password'] = 'Le mot de passe est requis';
    }
    if (!empty($errors)) {
        Response::validation($errors);
    }

    $db = Database::getInstance();

    // ============================================================
    // RECHERCHE DE L'UTILISATEUR
    // Accepte : user_name OU email
    // ============================================================
    $user = $db->fetch(
        "SELECT id, user_name, password, category, f_name, l_name, 
                email, telephone, status, profil
         FROM users 
         WHERE (user_name = ? OR email = ?) 
         LIMIT 1",
        [$identifier, $identifier]
    );

    // ============================================================
    // UTILISATEUR INTROUVABLE
    // ============================================================
    if (!$user) {
        logLoginAttempt($db, $identifier, 'Échec', 'Utilisateur inconnu');
        Response::unauthorized('Identifiant ou mot de passe incorrect');
    }

    // ============================================================
    // COMPTE VERROUILLÉ ?
    // ============================================================
    if (isset($user['locked_until']) && $user['locked_until'] !== null) {
        if (strtotime($user['locked_until']) > time()) {
            $remaining = ceil((strtotime($user['locked_until']) - time()) / 60);
            Response::error(
                "Compte verrouillé. Réessayez dans $remaining minute(s).",
                423
            );
        }
    }

    // ============================================================
    // VÉRIFICATION DU MOT DE PASSE
    // ============================================================
    if (!password_verify($password, $user['password'])) {
        // Incrémenter les tentatives échouées
        $attempts = ($user['failed_attempts'] ?? 0) + 1;
        $lockUntil = null;

        if ($attempts >= MAX_LOGIN_ATTEMPTS) {
            $lockUntil = date('Y-m-d H:i:s', time() + LOGIN_LOCK_TIME);
            $attempts = 0; // Reset après verrouillage
        }

        $db->execute(
            "UPDATE users SET failed_attempts = ?, locked_until = ? WHERE id = ?",
            [$attempts, $lockUntil, $user['id']]
        );

        logLoginAttempt($db, $user['user_name'], 'Échec', 'Mot de passe incorrect');

        if ($lockUntil) {
            Response::error(
                "Trop de tentatives. Compte verrouillé pendant 15 minutes.",
                423
            );
        }

        $remaining = MAX_LOGIN_ATTEMPTS - $attempts;
        Response::unauthorized(
            "Identifiant ou mot de passe incorrect. $remaining tentative(s) restante(s)."
        );
    }

    // ============================================================
    // VÉRIFICATION DU STATUT
    // ============================================================
    if (isset($user['status']) && strtolower($user['status']) === 'désactivé') {
        logLoginAttempt($db, $user['user_name'], 'Échec', 'Compte désactivé');
        Response::forbidden('Votre compte est désactivé. Contactez l\'administrateur.');
    }

    // ============================================================
    // CONNEXION RÉUSSIE
    // ============================================================

    // Réinitialiser les tentatives échouées
    $db->execute(
        "UPDATE users SET failed_attempts = 0, locked_until = NULL, 
                          last_login = NOW(), session_id = ?
         WHERE id = ?",
        [session_id() ?: bin2hex(random_bytes(16)), $user['id']]
    );

    $role = normalizeUserRole($user['category'] ?? '');

    // Générer le token JWT
    $tokenPayload = [
        'user_id'  => (int) $user['id'],
        'username' => $user['user_name'],
        'role'     => $role,
        'email'    => $user['email'],
    ];
    $token = Jwt::encode($tokenPayload);

    // Journaliser la connexion (logintos)
    logLoginAttempt($db, $user['user_name'], 'Connexion au système', 'Succès');

    // Démarrer la session PHP
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['user_name'];
    $_SESSION['role']     = $role;
    $_SESSION['logged_in'] = true;

    // ============================================================
    // RÉPONSE
    // ============================================================
    Response::success([
        'token' => $token,
        'user'  => [
            'id'        => (int) $user['id'],
            'username'  => $user['user_name'],
            'full_name' => trim(($user['f_name'] ?? '') . ' ' . ($user['l_name'] ?? '')),
            'email'     => $user['email'],
            'phone'     => $user['telephone'],
            'role'      => $role,
            'role_label' => getRoleLabel($role),
            'avatar'    => $user['profil'] ?: null,
        ],
        'expires_in' => JWT_EXPIRATION,
    ], 'Connexion réussie');
}

// ============================================================
// LOGOUT
// ============================================================
function logout()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $username = $_SESSION['username'] ?? 'Inconnu';

    // Journaliser
    $db = Database::getInstance();
    logLoginAttempt($db, $username, 'Déconnexion', 'Succès');

    // Vider la session
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();

    Response::success(null, 'Déconnexion réussie');
}

// ============================================================
// ME (infos utilisateur connecté)
// ============================================================
function me()
{
    // Essayer d'abord via JWT
    $token = Jwt::getBearerToken();
    if ($token) {
        $payload = Jwt::decode($token);
        if ($payload) {
            $db = Database::getInstance();
            $user = $db->fetch(
                "SELECT id, user_name, category, f_name, l_name, email, telephone, profil 
                 FROM users WHERE id = ?",
                [$payload['user_id']]
            );
            if ($user) {
                Response::success([
                    'id'        => (int) $user['id'],
                    'username'  => $user['user_name'],
                    'full_name' => trim(($user['f_name'] ?? '') . ' ' . ($user['l_name'] ?? '')),
                    'email'     => $user['email'],
                    'phone'     => $user['telephone'],
                    'role'      => $user['category'],
                    'role_label' => getRoleLabel($user['category']),
                ]);
            }
        }
    }

    // Fallback session PHP
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!empty($_SESSION['user_id'])) {
        $db = Database::getInstance();
        $user = $db->fetch(
            "SELECT id, user_name, category, f_name, l_name, email 
             FROM users WHERE id = ?",
            [$_SESSION['user_id']]
        );
        if ($user) {
            Response::success($user);
        }
    }

    Response::unauthorized('Non authentifié');
}

// ============================================================
// FORGOT PASSWORD
// ============================================================
function forgot()
{
    $input = getInput();
    $email = trim($input['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        Response::validation(['email' => 'Email invalide']);
    }

    $db = Database::getInstance();
    $user = $db->fetch("SELECT id, user_name FROM users WHERE email = ?", [$email]);

    // Toujours retourner succès (sécurité : ne pas révéler si l'email existe)
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1h

        $db->execute(
            "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)",
            [$user['id'], $token, $expiresAt]
        );

        // TODO : Envoyer email avec le lien
        // $resetLink = APP_URL . "/reset-password.php?token=$token";
    }

    Response::success(null, 'Si cet email existe, un lien de réinitialisation a été envoyé.');
}

// ============================================================
// HELPERS
// ============================================================

function getInput()
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        return json_decode($raw, true) ?: [];
    }
    return $_POST;
}

function logLoginAttempt($db, $username, $actionType, $descrip)
{
    try {
        $sessionId = session_id() ?: '-';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '-';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '-';

        $db->execute(
            "INSERT INTO logintos (user_id, action_type, session_id, ip_adress, user_agent, descrip) 
             VALUES (?, ?, ?, ?, ?, ?)",
            [$username, $actionType, $sessionId, $ip, $ua, $descrip . ' - ' . date('d/m/Y H:i')]
        );
    } catch (Exception $e) {
        // Silencieux : on ne veut pas bloquer le login à cause du log
    }
}

function getRoleLabel($role)
{
    $labels = [
        'super_admin'       => 'Super Administrateur',
        'directeur_general' => 'Directeur Général',
        'comptable'         => 'Comptable',
        'receptionniste'    => 'Réceptionniste',
        'magasinier'        => 'Magasinier',
        'travailleur'       => 'Travailleur',
        // Anciens rôles (compatibilité)
        'Super Admin'       => 'Super Administrateur',
        'Admin'             => 'Administrateur',
        'caissier'          => 'Caissier',
        'agent'             => 'Agent',
        'Utilisateur'       => 'Utilisateur',
    ];
    return $labels[$role] ?? ucfirst(str_replace('_', ' ', $role));
}

function normalizeUserRole($role)
{
    $aliases = [
        'Super Admin' => 'super_admin',
        'Admin' => 'directeur_general',
        'caissier' => 'comptable',
        'agent' => 'magasinier',
        'Utilisateur' => 'travailleur',
    ];

    return $aliases[$role] ?? $role;
}