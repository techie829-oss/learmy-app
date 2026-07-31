<?php

if (!function_exists('is_feature_enabled')) {
    /**
     * Check if a specific feature is enabled.
     *
     * @param string $feature
     * @return bool
     */
    function is_feature_enabled(string $feature): bool
    {
        return config('all.features.' . $feature, false);
    }
}
