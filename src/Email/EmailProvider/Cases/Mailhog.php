<?php

namespace Hizech\Bliss\Email\EmailProvider\Cases;

use Hizech\Bliss\Email\Data\EmailAddress\EmailAddress;
use Hizech\Bliss\Email\Data\EmailBody\EmailBody;
use Hizech\Bliss\Email\Data\EmailBody\EmailBodyType;
use Hizech\Bliss\Email\Data\EmailRecipients\EmailRecipients;
use Hizech\Bliss\Email\EmailProvider\EmailProvider;
use ErrorException;

class Mailhog implements EmailProvider
{
    public function __construct(
        private string $host,
        private int $port,
    ) {}

    public function testConnection(): bool
    {
        $socket = null;

        try {
            // Connect
            $socket = @fsockopen($this->host, $this->port, $errno, $errstr, 10);
            if (!$socket) {
                throw new ErrorException(
                    "Failed to connect to Mailhog SMTP server at {$this->host}:{$this->port} - Error $errno: $errstr"
                );
            }

            stream_set_timeout($socket, 10);

            // Handshake
            $this->readResponse($socket, [220]);
            $this->write($socket, 'EHLO localhost', [250]);

            // Quit
            $this->write($socket, 'QUIT', [221]);
            fclose($socket);

        } catch (ErrorException $e) {
            if (is_resource($socket)) {
                fclose($socket);
            }
            return false;
        }

        return true;
    }

    public function sendEmail(
        EmailAddress $from,
        EmailRecipients $to,
        string $subject,
        EmailBody $body
    ): bool {

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

        // Envelope
        $this->write($socket, "MAIL FROM:<{$from->email}>", [250]);

        // Send RCPT TO for all recipients (to, cc, bcc)
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

        // Note: BCC not included in headers (that's the point of BCC)

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
        $start_time = microtime(true);
        $timeout = 10; // seconds

        while (($line = fgets($socket, 512)) !== false) {
            $response .= $line;

            // Check if we got a complete response (3-digit code followed by space)
            if (preg_match('/^\d{3} /', $line)) {
                break;
            }

            // Safety check for timeout
            if ((microtime(true) - $start_time) > $timeout) {
                break;
            }
        }

        // Check if we got any response at all
        if (empty($response)) {
            // Check socket status
            $meta = stream_get_meta_data($socket);
            if ($meta['timed_out']) {
                throw new ErrorException('SMTP read timeout - no response from server');
            }
            if ($meta['eof']) {
                throw new ErrorException('SMTP connection closed by server before sending response');
            }
            throw new ErrorException('SMTP read failed - no data received from server');
        }

        $code = (int)substr($response, 0, 3);
        if (!in_array($code, $expect_codes, true)) {
            throw new ErrorException('Unexpected SMTP response ' . $code . ': ' . trim($response));
        }

        return $response;
    }

}