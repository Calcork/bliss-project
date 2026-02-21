<?php

namespace App\AppCode\Actions;

use App\Base\App;
use App\Models\GeneralSetting;
use App\Models\User;
use Hizech\Bliss\Email\Data\EmailAddress\EmailAddress;
use Hizech\Bliss\Email\Data\EmailBody\EmailBody;
use Hizech\Bliss\Email\Data\EmailBody\EmailBodyType;
use Hizech\Bliss\Email\Data\EmailRecipients\EmailRecipients;

class Email
{

    function __construct(private App $app) {}

   private function getAutosystemAddress(): EmailAddress {

       $settings = $this->app->getEntityManager()->find(GeneralSetting::class, GeneralSetting::getSingletonId());
       return new EmailAddress($settings->getAutosystemEmail(), $settings->getSiteName());

   }

    /**
     * @param array<int, int> $user_ids
     * @return EmailRecipients
     */
   private function getUsersEmailRecipient(array $user_ids) : EmailRecipients {

       $recipients_addresses = [];

        foreach($user_ids as $user_id) {

            $em = $this->app->getEntityManager();
            $user = $em->find(User::class, $user_id);


            $recipients_addresses[] = new EmailAddress($user->getEmail(), $user->getFirstName() . ' ' . $user->getLastName());

        }

        return new EmailRecipients($recipients_addresses);

   }

   private function getCenterMessageBody(string $center_message, ?int $user_id = null) : EmailBody {

       $context = [
           'site_name' => $this->app->getEntityManager()->find(GeneralSetting::class, GeneralSetting::getSingletonId())->getAutosystemEmail()
       ];

       if(isset($user_id)) {

           $em = $this->app->getEntityManager();
           $user = $em->find(User::class, $user_id);

           $context['user_first_name'] = $user->getFirstName();

           $path = 'email/user/center_message.html.twig';

       }

       else {
           $path = 'email/center_message.html.twig';
       }

       $twig = $this->app->getTemplateMaster()->twigCustomRender($path, $context);

       $body = new EmailBody(EmailBodyType::Html, $twig);
       return $body;

   }

   function sendUserEmail(string $subject, string $center_message, int $user_id) : void {

       $body = $this->getCenterMessageBody($center_message, $user_id);
       $from = $this->getAutosystemAddress();
       $recipients = $this->getUsersEmailRecipient([$user_id]);

       $this->app->getEmailProvider()->sendEmail($from, $recipients, $subject, $body);

   }

}
