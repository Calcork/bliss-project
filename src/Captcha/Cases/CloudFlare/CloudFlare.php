<?php

namespace Hizech\Bliss\Captcha\Cases\CloudFlare;

use Hizech\Bliss\Captcha\CaptchaProvider;
use Hizech\Bliss\Captcha\ValidationStatus;

class CloudFlare implements CaptchaProvider
{

    function __construct(
        private string $site_key,
        private string $secret_key,
    ) {}

    function headHtml() : ?string {
        // Return null - script loading is deferred to FormHtml()
        return null;
    }

    function testConnection(): bool
    {
        $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
        if ($ch === false) {
            return false;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'secret' => $this->secret_key,
                'response' => 'test-token',
            ]),
            CURLOPT_TIMEOUT => 5,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            error_log("Turnstile connection error: " . curl_error($ch));
            return false;
        }

        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        if ($code !== 200) {
            error_log("Turnstile connection HTTP code: $code, response: $response");
            return false;
        }

        return true;
    }

    function FormHtml(): string {
        $site_key = htmlspecialchars($this->site_key, ENT_QUOTES, 'UTF-8');
        $str = <<<HTML
<div
    class="cf-turnstile"
    data-sitekey="{$site_key}"
    data-size="normal"
    data-callback="onSuccess"
></div>
<script>
(function() {
    if (window.turnstileLoaded) return;
    window.turnstileLoaded = true;

    window.onSuccess = function(captcha_token) {
        var captcha_token_input = document.getElementById("captcha_token");
        if (captcha_token_input) {
            captcha_token_input.value = captcha_token;
        }
    };

    function loadTurnstile() {
        var script = document.createElement('script');
        script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
        script.async = true;
        document.head.appendChild(script);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadTurnstile);
    } else {
        loadTurnstile();
    }
})();
</script>
HTML;
        return $str;
    }

    function verifyToken(?string $token, ?string $user_ip = null): ValidationStatus
    {
        // Missing/empty token from client is an invalid attempt, not a transport error
        if ($token === null || $token === '') {
            return ValidationStatus::Invalid;
        }

        $secret = $this->secret_key;

        if ($secret === '') {
            // Misconfiguration — cannot verify
            return ValidationStatus::Error;
        }

        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

        $data = [
            'secret' => $secret,
            'response' => $token,
        ];

        if ($user_ip !== null) {
            $data['remoteip'] = $user_ip;
        }

        $ch = curl_init($url);
        if ($ch === false) {
            return ValidationStatus::Error;
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        if ($response === false || $code !== 200) {
            return ValidationStatus::Error;
        }

        $json = json_decode($response, true);

        if (!is_array($json) || !array_key_exists('success', $json)) {
            return ValidationStatus::Error;
        }

        return $json['success'] ? ValidationStatus::Valid : ValidationStatus::Invalid;
    }

}