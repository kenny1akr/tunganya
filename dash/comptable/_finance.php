<?php
if (!function_exists('financeEscape')) {
    function financeEscape($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('financeMonthRange')) {
    function financeMonthRange(string $month): array
    {
        $parsed = DateTime::createFromFormat('!Y-m', $month);
        if (!$parsed || $parsed->format('Y-m') !== $month) {
            $parsed = new DateTime('first day of this month');
        }

        return [$parsed->format('Y-m-01'), $parsed->format('Y-m-t'), $parsed->format('Y-m')];
    }
}

if (!function_exists('financeValidDate')) {
    function financeValidDate(string $value): bool
    {
        $date = DateTime::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }
}