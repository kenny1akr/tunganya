<?php
/**
 * config/Database.php
 * Classe PDO Singleton - DH TELECOM
 * PHP pur - Sans framework
 */

require_once __DIR__ . '/config.php';

class Database
{
    /** @var Database|null Instance unique */
    private static $instance = null;

    /** @var PDO Connexion PDO */
    private $pdo;

    /** @var int Compteur de transactions imbriquées */
    private $transactionLevel = 0;

    /** @var array Statistiques de requêtes (debug) */
    private $queryStats = [
        'count'   => 0,
        'time'    => 0.0,
        'queries' => [],
    ];

    /**
     * Constructeur privé (Singleton)
     */
    private function __construct()
    {
        $dsn = 'mysql:host=' . DB_HOST
             . ';port=' . DB_PORT
             . ';dbname=' . DB_NAME
             . ';charset=' . DB_CHARSET;

        $options = [
            // Mode d'erreur : exceptions
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            // Mode de récupération : tableau associatif
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Vraies requêtes préparées (protection injection SQL)
            PDO::ATTR_EMULATE_PREPARES   => false,
            // Pas de connexion persistante
            PDO::ATTR_PERSISTENT         => false,
            // Timeout de connexion (secondes)
            PDO::ATTR_TIMEOUT            => 5,
            // Ne pas convertir les entiers en chaînes
            PDO::ATTR_STRINGIFY_FETCHES  => false,
            // Encodage + fuseau horaire côté serveur
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
                                          . " COLLATE " . DB_COLLATION
                                          . ", time_zone = '+02:00'",
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $this->logError($e);
            die('Erreur de connexion à la base de données : ' . $e->getMessage());
        }
    }

    /**
     * Empêche le clonage
     */
    private function __clone() {}

    /**
     * Empêche la désérialisation
     */
    public function __wakeup()
    {
        throw new RuntimeException("Impossible de désérialiser un Singleton");
    }

    /**
     * Récupère l'instance unique
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Récupère la connexion PDO brute
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    // ============================================================
    // REQUÊTES PRÉPARÉES
    // ============================================================

    /**
     * Exécute une requête préparée et retourne le statement
     */
    public function query($sql, $params = [])
    {
        $start = microtime(true);

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            // Statistiques
            $this->queryStats['count']++;
            $this->queryStats['time'] += microtime(true) - $start;
            if (APP_DEBUG) {
                $this->queryStats['queries'][] = [
                    'sql'    => $sql,
                    'params' => $params,
                    'time'   => round((microtime(true) - $start) * 1000, 2) . 'ms',
                ];
            }

            return $stmt;
        } catch (PDOException $e) {
            $this->logError($e, $sql, $params);
            throw $e;
        }
    }

    /**
     * Récupère une seule ligne
     */
    public function fetch($sql, $params = [])
    {
        $result = $this->query($sql, $params)->fetch();
        return $result === false ? null : $result;
    }

    /**
     * Récupère toutes les lignes
     */
    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Récupère une seule valeur (1ère colonne de la 1ère ligne)
     */
    public function fetchColumn($sql, $params = [], $column = 0)
    {
        $result = $this->query($sql, $params)->fetchColumn($column);
        return $result === false ? null : $result;
    }

    /**
     * Récupère une colonne complète (tableau 1D)
     */
    public function fetchCol($sql, $params = [], $column = 0)
    {
        return $this->query($sql, $params)->fetchAll(PDO::FETCH_COLUMN, $column);
    }

    /**
     * Récupère une paire clé => valeur
     */
    public function fetchPairs($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Exécute INSERT/UPDATE/DELETE, retourne le nombre de lignes affectées
     */
    public function execute($sql, $params = [])
    {
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * INSERT et retourne l'ID généré
     */
    public function insert($sql, $params = [])
    {
        $this->query($sql, $params);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Vérifie si un enregistrement existe
     */
    public function exists($sql, $params = [])
    {
        return $this->fetchColumn($sql, $params) !== null;
    }

    // ============================================================
    // TRANSACTIONS
    // ============================================================

    /**
     * Démarre une transaction (supporte l'imbrication via SAVEPOINT)
     */
    public function beginTransaction()
    {
        if ($this->transactionLevel === 0) {
            $this->pdo->beginTransaction();
        } else {
            $this->pdo->exec("SAVEPOINT trans_{$this->transactionLevel}");
        }
        $this->transactionLevel++;
        return true;
    }

    /**
     * Valide la transaction courante
     */
    public function commit()
    {
        if ($this->transactionLevel === 0) {
            throw new RuntimeException("Aucune transaction active");
        }
        $this->transactionLevel--;

        if ($this->transactionLevel === 0) {
            return $this->pdo->commit();
        }
        $this->pdo->exec("RELEASE SAVEPOINT trans_{$this->transactionLevel}");
        return true;
    }

    /**
     * Annule la transaction courante
     */
    public function rollback()
    {
        if ($this->transactionLevel === 0) {
            throw new RuntimeException("Aucune transaction active");
        }
        $this->transactionLevel--;

        if ($this->transactionLevel === 0) {
            return $this->pdo->rollBack();
        }
        $this->pdo->exec("ROLLBACK TO SAVEPOINT trans_{$this->transactionLevel}");
        return true;
    }

    /**
     * Exécute une closure dans une transaction
     */
    public function transaction($callback)
    {
        $this->beginTransaction();
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Vérifie si une transaction est en cours
     */
    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    // ============================================================
    // UTILITAIRES
    // ============================================================

    /**
     * Récupère le nom de la base actuelle
     */
    public function getDatabaseName()
    {
        return (string) $this->fetchColumn("SELECT DATABASE()");
    }

    /**
     * Vérifie si une table existe
     */
    public function tableExists($table)
    {
        try {
            $result = $this->fetch(
                "SELECT 1 FROM information_schema.tables 
                 WHERE table_schema = ? AND table_name = ? LIMIT 1",
                [DB_NAME, $table]
            );
            return $result !== null;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Test de connexion (ping)
     */
    public function ping()
    {
        try {
            $this->pdo->query("SELECT 1");
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Récupère les statistiques de requêtes (debug)
     */
    public function getStats()
    {
        return [
            'query_count'    => $this->queryStats['count'],
            'total_time_ms'  => round($this->queryStats['time'] * 1000, 2),
            'queries'        => APP_DEBUG ? $this->queryStats['queries'] : [],
        ];
    }

    /**
     * Réinitialise les statistiques
     */
    public function resetStats()
    {
        $this->queryStats = ['count' => 0, 'time' => 0.0, 'queries' => []];
    }

    /**
     * Journalise une erreur PDO
     */
    private function logError($e, $sql = '', $params = [])
    {
        $logDir = dirname(LOG_PATH);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0775, true);
        }

        $message = sprintf(
            "[%s] PDO ERROR: %s\n  SQL: %s\n  Params: %s\n  File: %s:%d\n\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $sql ?: 'N/A',
            json_encode($params, JSON_UNESCAPED_UNICODE),
            $e->getFile(),
            $e->getLine()
        );

        @file_put_contents(LOG_PATH, $message, FILE_APPEND | LOCK_EX);
    }
}