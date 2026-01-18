<?php

namespace Hizech\Bliss\Email;

class EmailManager
{

    /**
     * Sanitize email address
     */
    static function sanitizeEmail(string $str): string {

        // Trim and lowercase
        $str = strtolower(trim($str));

        // Use PHP's built-in email filter
        $sanitized = filter_var($str, FILTER_SANITIZE_EMAIL);

        // Additional header injection prevention
        $sanitized = str_replace(["\r", "\n", "\0", "%0a", "%0d", "%00"], '', $sanitized);

        return $sanitized ?: '';

    }

    /**
     * Sanitize email subject line
     */
    static function sanitizeSubject(string $str): string {

        // Remove control characters and header injection attempts
        $str = preg_replace('/[\x00-\x1F\x7F]/', '', $str);

        // Remove encoded newlines
        $str = str_replace(["\r", "\n", "\0", "%0a", "%0d", "%00"], '', $str);

        // Trim and limit length
        $str = trim($str);

        return mb_substr($str, 0, 120);

    }

    /**
     * Sanitize email body (plain text)
     */
    static function sanitizeBody(string $str): string {

        // Remove null bytes
        $str = str_replace("\0", '', $str);

        // Normalize line endings to \n
        $str = str_replace(["\r\n", "\r"], "\n", $str);

        // Remove any embedded headers (lines starting with common header names after newlines)
        $str = preg_replace('/\n(To|From|Cc|Bcc|Subject|Content-Type|MIME-Version):/i', "\n", $str);

        return trim($str);

    }

    /**
     * General sanitize method (legacy/fallback)
     */
    static function sanitize(string $str): string {
        return self::sanitizeSubject($str);
    }

}