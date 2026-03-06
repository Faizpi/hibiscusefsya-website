<?php

if (!function_exists('formatJson')) {
    /**
     * Format a JSON string with syntax highlighting for display.
     */
    function formatJson($jsonString)
    {
        $decoded = json_decode($jsonString);
        if ($decoded === null) {
            return e($jsonString);
        }

        $pretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Apply syntax highlighting
        $pretty = e($pretty);
        $pretty = preg_replace('/&quot;([^&]+?)&quot;\s*:/', '<span style="color:#be185d">"$1"</span>:', $pretty);
        $pretty = preg_replace('/:\s*&quot;(.*?)&quot;/', ': <span style="color:#0369a1">"$1"</span>', $pretty);
        $pretty = preg_replace('/:\s*(\d+\.?\d*)/', ': <span style="color:#b45309">$1</span>', $pretty);
        $pretty = preg_replace('/:\s*(true|false|null)/', ': <span style="color:#7c3aed">$1</span>', $pretty);

        return $pretty;
    }
}
