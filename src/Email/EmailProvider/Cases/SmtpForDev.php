<?php

namespace Hizech\Bliss\Email\EmailProvider\Cases;

use Hizech\Bliss\Email\Data\EmailAddress\EmailAddress;
use Hizech\Bliss\Email\Data\EmailBody\EmailBody;
use Hizech\Bliss\Email\Data\EmailBody\EmailBodyType;
use Hizech\Bliss\Email\Data\EmailRecipients\EmailRecipients;
use Hizech\Bliss\Email\EmailProvider\EmailProvider;
use Hizech\Bliss\Email\EmailProvider\EmailStatus;
use ErrorException;

class SmtpForDev implements EmailProvider
{
    public function __construct(
        private string $host,
        private int $port,
        private bool $tls,
        private string $user,
        private string $pass,
    ) {}


    public function testConnection(): bool
    {
        $socket = null;

        try {
            // Connect
            $socket = fsockopen($this->host, $this->port, $errno, $errstr, 10);
            if (!$socket) {
                return false;
            }

            stream_set_timeout($socket, 10);

            // Handshake
            $this->readResponse($socket, [220]);
            $this->write($socket, 'EHLO localhost', [250]);

            // Quit
            $this->write($socket, 'QUIT', [221]);
            fclose($socket);

            return true;
        } catch (ErrorException) {
            if (is_resource($socket)) fclose($socket);
            return false;
        }
    }

        public function sendEmail(
        EmailAddress $from,
        EmailRecipients $to,
        string $subject,
        EmailBody $body
    ): EmailStatus {

        $socket = null;

        // Connect
        $socket = fsockopen($this->host, $this->port, $errno, $errstr, 10);
        if (!$socket) {
            throw new ErrorException("Connection failed: {$this->host}:{$this->port} ($errno) $errstr");
        }

        stream_set_timeout($socket, 10);

        // Handshake
        $this->readResponse($socket, [220]);
        $this->write($socket, 'EHLO localhost', [250]);

        // TLS
        if ($this->tls) {
            $this->write($socket, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
                throw new ErrorException('STARTTLS negotiation failed');
            }
            $this->write($socket, 'EHLO localhost', [250]);
        }

        // Auth (only if credentials are provided)
        if ($this->user !== '' && $this->pass !== '') {
            $this->write($socket, 'AUTH LOGIN', [334]);
            $this->write($socket, base64_encode($this->user), [334]);
            $this->write($socket, base64_encode($this->pass), [235]);
        }

        // Envelope
        $this->write($socket, "MAIL FROM:<{$from->email}>", [250]);

        // Send RCPT TO for all recipients
        foreach ($this->getAllRecipientEmails($to) as $recipient_email) {
            $this->write($socket, "RCPT TO:<{$recipient_email}>", [250, 251]);
        }

        $this->write($socket, 'DATA', [354]);

        // Build headers
        $headers = [
            'From' => $this->formatHeaderAddress($from),
            'To' => $this->formatAddressList($to->to),
            'Subject' => $subject,
            'MIME-Version' => '1.0',
            'Content-Type' => $body->type === EmailBodyType::Html
                ? 'text/html; charset=UTF-8'
                : 'text/plain; charset=UTF-8',
        ];

        // Add CC header if present
        if (!empty($to->cc)) {
            $headers['Cc'] = $this->formatAddressList($to->cc);
        }

        $header_str = implode("\r\n", array_map(
            fn($k, $v) => "$k: $v",
            array_keys($headers),
            $headers
        ));

        // Process body
        $body_text = str_replace(["\r\n", "\r"], "\n", $body->content);
        $lines = explode("\n", $body_text);
        foreach ($lines as &$line) {
            if (isset($line[0]) && $line[0] === '.') {
                $line = '.' . $line;
            }
        }
        unset($line);
        $body_processed = implode("\r\n", $lines);

        $data = $header_str . "\r\n\r\n" . $body_processed . "\r\n.";
        $this->write($socket, $data, [250]);

        // Close
        $this->write($socket, 'QUIT', [221]);
        fclose($socket);

        return EmailStatus::Sent;
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

    /**
     * Get all unique recipient email addresses (for RCPT TO commands)
     * @return string[]
     */
    private function getAllRecipientEmails(EmailRecipients $recipients): array
    {
        $emails = [];

        foreach ($recipients->to as $addr) {
            $emails[] = $addr->email;
        }
        foreach ($recipients->cc as $addr) {
            $emails[] = $addr->email;
        }
        foreach ($recipients->bcc as $addr) {
            $emails[] = $addr->email;
        }

        return array_unique($emails);
    }

    /**
     * @param resource $socket
     * @param array<int, int> $expect_codes
     */
    private function write($socket, string $cmd, array $expect_codes): void
    {
        fwrite($socket, $cmd . "\r\n");
        $this->readResponse($socket, $expect_codes);
    }

    /**
     * @param resource $socket
     * @param array<int, int> $expect_codes
     */
    private function readResponse($socket, array $expect_codes): string
    {
        $response = '';
        while (($line = fgets($socket, 512)) !== false) {
            $response .= $line;
            if (preg_match('/^\d{3} /', $line)) {
                break;
            }
        }

        $code = (int)substr($response, 0, 3);
        if (!in_array($code, $expect_codes, true)) {
            throw new ErrorException('Unexpected SMTP response ' . $code . ': ' . trim($response));
        }
        return $response;
    }
}