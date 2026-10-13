<?php
if (!function_exists('receptionEscape')) {
    function receptionEscape($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('receptionEnsureCsrf')) {
    function receptionEnsureCsrf(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('receptionValidDate')) {
    function receptionValidDate(string $value): bool
    {
        $date = DateTime::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }
}

if (!function_exists('receptionEnsureOpenCashRegister')) {
    function receptionEnsureOpenCashRegister($db, string $operator): array
    {
        $db->execute(
            'INSERT INTO caisse (initial, montant_in, montant_out, date, status, opened_by)
             VALUES (0, 0, 0, CURDATE(), 0, ?)
             ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)',
            [mb_substr($operator, 0, 100)]
        );
        $register = $db->query('SELECT id, status FROM caisse WHERE date = CURDATE() FOR UPDATE')->fetch();
        if (!$register || (int) $register['status'] !== 0) {
            throw new RuntimeException('La caisse du jour est fermée. Contactez le comptable pour enregistrer cette opération.');
        }

        return $register;
    }
}

if (!function_exists('receptionRecordFinancialTransaction')) {
    function receptionRecordFinancialTransaction($db, string $username, string $action, string $reference, int $amount, string $direction): void
    {
        $db->execute(
            'INSERT INTO transactions (user_id, action_type, ref_id, montant, type, status)
             VALUES (?, ?, ?, ?, ?, \'Active\')',
            [mb_substr($username, 0, 100), mb_substr($action, 0, 100), mb_substr($reference, 0, 25), (string) $amount, $direction]
        );
    }
}