<?php

namespace Hizech\Bliss\Email\EmailProvider\Cases;

use Hizech\Bliss\Email\Data\EmailAddress\EmailAddress;
use Hizech\Bliss\Email\Data\EmailBody\EmailBody;
use Hizech\Bliss\Email\Data\EmailBody\EmailBodyType;
use Hizech\Bliss\Email\Data\EmailRecipients\EmailRecipients;
use Hizech\Bliss\Email\EmailProvider\EmailProvider;

class DummyEmail implements EmailProvider
{
    public function __construct(
        private string $storage_dir,
    ) {}

    public function sendEmail(
        EmailAddress $from,
        EmailRecipients $to,
        string $subject,
        EmailBody $body
    ): bool {

        // Ensure directory exists
        if (!is_dir($this->storage_dir)) {
            mkdir($this->storage_dir, 0755, true);
        }

        // Build RFC 2822 compliant email (same format sent to SMTP servers)
        $headers = [];
        $headers[] = "From: " . $this->formatAddress($from);
        $headers[] = "To: " . $this->formatAddressList($to->to);

        if (!empty($to->cc)) {
            $headers[] = "Cc: " . $this->formatAddressList($to->cc);
        }

        if (!empty($to->bcc)) {
            $headers[] = "Bcc: " . $this->formatAddressList($to->bcc);
        }

        $headers[] = "Subject: {$subject}";
        $headers[] = "Date: " . date('r'); // RFC 2822 date format
        $headers[] = "Message-ID: <" . bin2hex(random_bytes(16)) . "@dummy.local>";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: " . ($body->type === EmailBodyType::Html
            ? "text/html; charset=UTF-8"
            : "text/plain; charset=UTF-8");

        $header_str = implode("\r\n", $headers);

        // Full email: headers + blank line + body
        $content = $header_str . "\r\n\r\n" . $body->content;

        // Write to .eml file (standard email format, openable by email clients)
        $filename_timestamp = date('Y-m-d_H-i-s') . '_' . bin2hex(random_bytes(4));
        $filename = "{$filename_timestamp}.eml";
        $filepath = $this->storage_dir . DIRECTORY_SEPARATOR . $filename;

        $result = file_put_contents($filepath, $content);

        return $result !== false ? true : false;
    }

    private function formatAddress(EmailAddress $address): string
    {
        if ($address->name !== null) {
            return "\"{$address->name}\" <{$address->email}>";
        }
        return $address->email;
    }

    /**
     * @param EmailAddress[] $addresses
     */
    private function formatAddressList(array $addresses): string
    {
        return implode(', ', array_map(
            fn(EmailAddress $addr) => $this->formatAddress($addr),
            $addresses
        ));
    }

    public function testConnection(): bool
    {
        // Check if we can write to the directory
        if (!is_dir($this->storage_dir)) {
            $created = @mkdir($this->storage_dir, 0755, true);
            if (!$created) {
                return false;
            }
        }

        return is_writable($this->storage_dir);
    }
}
