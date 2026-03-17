<?php

use App\Services\BusinessSettings;


if (! function_exists('status_color')) {
    /**
     * Get hex color code for a status
     *
     * @param string|null $status
     * @return string
     */
    function status_color(?string $status): string
    {
        return match ($status) {
            'pending'   => '#fbbf24', // amber
            'confirmed' => '#4ade80', // green
            'canceled'  => '#f87171', // red
            'completed' => '#60a5fa', // blue
            default     => '#9ca3af', // gray
        };
    }
}

if (! function_exists('business_label')) {
    function business_label(string $key, string $default): string
    {
        try {
            return app(BusinessSettings::class)->getLabel($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
