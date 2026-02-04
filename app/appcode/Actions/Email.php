<?php

namespace App\AppCode\Actions;

use App\Base\App;
use Hizech\Bliss\Email\Data\EmailAddress\EmailAddress;
use Hizech\Bliss\Email\Data\EmailBody\EmailBody;
use Hizech\Bliss\Email\Data\EmailBody\EmailBodyType;
use Hizech\Bliss\Email\Data\EmailRecipients\EmailRecipients;
use Hizech\Bliss\Email\EmailProvider\EmailStatus;

class Email
{

    function __construct(private App $app) {}

    /**
     * @param array<string, string> $context
     */
    private function sendHtmlEmail(string $template, array $context, string $user_email, string $user_name): EmailStatus
    {
        $from = new EmailAddress($this->app->getEnv()['EMAIL_FROM'], 'Bliss');
        $to = new EmailRecipients(new EmailAddress($user_email, $user_name));
        $subject = $context['subject'];

        $html = $this->app->getTemplateMaster()->twigCustomRender($template, $context);
        $body = new EmailBody(EmailBodyType::Html, $html);

        return $this->app->getEmailProvider()->sendEmail($from, $to, $subject, $body);
    }

    function sendWelcome(string $user_email, string $user_name): EmailStatus
    {
        $translator = $this->app->getTranslator();

        return $this->sendHtmlEmail('emails/welcome.twig', [
            'subject' => $translator->trans('main.email.welcome.subject', [], 'en'),
            'greeting' => $translator->trans('main.email.welcome.greeting', ['name' => $user_name], 'en'),
            'message' => $translator->trans('main.email.welcome.message', [], 'en'),
        ], $user_email, $user_name);
    }

    function sendVerification(string $user_email, string $user_name, string $verification_url): EmailStatus
    {
        $translator = $this->app->getTranslator();

        return $this->sendHtmlEmail('emails/verification.twig', [
            'subject' => $translator->trans('main.email.verification.subject', [], 'en'),
            'greeting' => $translator->trans('main.email.verification.greeting', ['name' => $user_name], 'en'),
            'message' => $translator->trans('main.email.verification.message', [], 'en'),
            'button' => $translator->trans('main.email.verification.button', [], 'en'),
            'url' => $verification_url,
        ], $user_email, $user_name);
    }

    function sendPasswordReset(string $user_email, string $user_name, string $reset_url): EmailStatus
    {
        $translator = $this->app->getTranslator();

        return $this->sendHtmlEmail('emails/password-reset.twig', [
            'subject' => $translator->trans('main.email.password_reset.subject', [], 'en'),
            'greeting' => $translator->trans('main.email.password_reset.greeting', ['name' => $user_name], 'en'),
            'message' => $translator->trans('main.email.password_reset.message', [], 'en'),
            'button' => $translator->trans('main.email.password_reset.button', [], 'en'),
            'url' => $reset_url,
        ], $user_email, $user_name);
    }

    function send(int $user_id, string $message): void
    {
        // TODO: Implement email sending
    }

    function revoke(int $email_id): void
    {
        // TODO: Implement email revocation
    }

    function removeFromNewsletter(int $user_id): void
    {
        // TODO: Implement newsletter removal
    }

}
