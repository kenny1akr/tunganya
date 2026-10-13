<?php
if (!function_exists('inventoryEscape')) {
    function inventoryEscape($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('inventoryEnsureCsrf')) {
    function inventoryEnsureCsrf(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('inventoryValidDate')) {
    function inventoryValidDate(string $value): bool
    {
        $date = DateTime::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }
}

if (!function_exists('inventoryLogMovement')) {
    function inventoryLogMovement(
        $db,
        array $product,
        string $type,
        float $quantity,
        float $before,
        float $after,
        float $unitCost,
        ?string $reference,
        string $reason,
        string $operator
    ): void {
        $db->execute(
            'INSERT INTO stock_movements (product_id, product_name, movement_type, quantity, quantity_before, quantity_after, unit_cost, total_amount, reference, reason, operator)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                (int) $product['id'],
                $product['medicine_name'],
                $type,
                $quantity,
                $before,
                $after,
                $unitCost,
                $quantity * $unitCost,
                $reference,
                $reason,
                mb_substr($operator, 0, 100),
            ]
        );
    }
}