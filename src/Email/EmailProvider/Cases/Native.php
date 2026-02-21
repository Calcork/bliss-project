<?php

namespace Hizech\Bliss\Email\EmailProvider\Cases;

use Hizech\Bliss\Email\Data\EmailAddress\EmailAddress;
use Hizech\Bliss\Email\Data\EmailBody\EmailBody;
use Hizech\Bliss\Email\Data\EmailBody\EmailBodyType;
use Hizech\Bliss\Email\Data\EmailRecipients\EmailRecipients;
use Hizech\Bliss\Email\EmailProvider\EmailProvider;

class Native implements EmailProvider
{
    public function sendEmail(
        EmailAddress $from,
        EmailRecipients $to,
        string $subject,
        EmailBody $body
    ): bool {

        // Check if mail function is available
        if (!function_exists('mail')) {
            throw new \RuntimeException('PHP mail() function is not available');
        }

        // Build headers
        $headers = [];
        $headers[] = "From: " . $this->formatHeaderAddress($from);

        if (!empty($to->cc)) {
            $headers[] = "Cc: " . $this->formatAddressList($to->cc);
        }

        if (!empty($to->bcc)) {
            $headers[] = "Bcc: " . $this->formatAddressList($to->bcc);
        }

        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: " . ($body->type === EmailBodyType::Html
                ? "text/html; charset=UTF-8"
                : "text/plain; charset=UTF-8");

        $headers_str = implode("\r\n", $headers);

        // Get primary recipients
        $to_list = $this->formatAddressList($to->to);

        // Capture any errors from mail()
        $error_message = null;
        set_error_handler(function(int $errno, string $errstr) use (&$error_message) {
            $error_message = $errstr;
            return true;
        });

        // Use -f to set envelope sender (Return-Path) for proper mail routing
        $success = mail($to_list, $subject, $body->content, $headers_str, "-f{$from->email}");

        restore_error_handler();

        if (!$success) {
            $msg = $error_message ?? 'mail() returned false - check server mail configuration (sendmail_path in php.ini)';
            throw new \RuntimeException('Native mail failed: ' . $msg);
        }

        return true;
    }

    /**
     * Format address for header (with display name if present)
     */
    private function formatHeaderAddress(EmailAddress $address): string
    {
        if ($address->name !== null) {
            return "\"{$address->name}\" <{$address->email}>";
        }
        return $address->email;
    }

    /**
     * Format list of addresses for header
     * @param EmailAddress[] $addresses
     */
    private function formatAddressList(array $addresses): string
    {
        return implode(', ', array_map(
            fn(EmailAddress $addr) => $this->formatHeaderAddress($addr),
            $addresses
        ));
    }

    public function testConnection(): bool
    {
        // Native mail() doesn't have a "connection" to test
        // Just check if the function exists
        return function_exists('mail');
    }
    
}