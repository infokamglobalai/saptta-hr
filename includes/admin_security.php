<?php
declare(strict_types=1);

/**
 * Security headers for admin area — prevents clickjacking via iframe embed.
 */
if (!headers_sent()) {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
