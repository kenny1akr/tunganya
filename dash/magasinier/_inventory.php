<?php
if (!function_exists('inventoryEscape')) {
    function inventoryEscape($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('inventoryCategoryIconColumnExists')) {
    function inventoryCategoryIconColumnExists($db): bool
    {
        return (int) $db->fetchColumn(
            'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            ['cat_prod', 'icon']
        ) > 0;
    }
}

if (!function_exists('inventoryCategoryIconOptions')) {
    function inventoryCategoryIconOptions(): array
    {
        return ['box' => 'Boîte', 'printer' => 'Impression', 'image' => 'Support visuel', 'roll' => 'Rouleau', 'tool' => 'Pièce / outil', 'ink' => 'Encre', 'truck' => 'Logistique', 'tag' => 'Général'];
    }
}

if (!function_exists('inventoryCategoryIcon')) {
    function inventoryCategoryIcon($icon, string $class = 'h-5 w-5'): string
    {
        $paths = [
            'box' => '<path d="m3 7 9-4 9 4-9 4-9-4Z"/><path d="M3 7v10l9 4 9-4V7M12 11v10"/>',
            'printer' => '<path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v7H6z"/><path d="M18 12h.01"/>',
            'image' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9" r="1.5"/><path d="m21 15-5-5L5 20"/>',
            'roll' => '<circle cx="7" cy="7" r="4"/><path d="M11 7h9v14H7v-10"/>',
            'tool' => '<path d="M14.7 6.3a5 5 0 0 0-6.4 6.4L3 18l3 3 5.3-5.3a5 5 0 0 0 6.4-6.4L14 13l-3-3 3.7-3.7Z"/>',
            'ink' => '<path d="M12 22a7 7 0 0 0 7-7c0-4-7-13-7-13S5 11 5 15a7 7 0 0 0 7 7Z"/>',
            'truck' => '<path d="M3 6h12v12H3zM15 10h4l3 3v5h-7"/><circle cx="7.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
            'tag' => '<path d="M20 13 11 22l-9-9V2h11l7 7a3 3 0 0 1 0 4Z"/><circle cx="7" cy="7" r="1"/>',
        ];
        $key = is_string($icon) && isset($paths[$icon]) ? $icon : 'tag';
        return '<svg aria-hidden="true" class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . ' shrink-0 text-green-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $paths[$key] . '</svg>';
    }
}

if (!function_exists('inventoryMovementIcon')) {
    function inventoryMovementIcon(string $direction): string
    {
        $path = $direction === 'out'
            ? '<path d="M12 3v12m0 0 5-5m-5 5-5-5"/><path d="M5 19h14"/>'
            : '<path d="M12 15V3m0 0 5 5m-5-5L7 8"/><path d="M5 19h14"/>';
        return '<svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
    }
}

if (!function_exists('inventoryLineActionIcon')) {
    function inventoryLineActionIcon(string $action): string
    {
        $paths = [
            'remove' => '<path d="M3 6h18"/><path d="M8 6V4h8v2m3 0-1 14H6L5 6"/><path d="M10 11v5m4-5v5"/>',
            'add' => '<path d="M12 5v14M5 12h14"/>',
        ];
        $path = $paths[$action] ?? $paths['add'];
        return '<svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
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
