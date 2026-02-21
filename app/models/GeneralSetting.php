<?php

namespace App\Models;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'general_settings')]
class GeneralSetting
{
    private const SINGLETON_ID = 1;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private int $id; /** @phpstan-ignore-line */

    #[ORM\Column(type: 'string', length: 255)]
    private string $site_name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $contact_email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $autosystem_email;

    public function __construct(string $site_name, string $contact_email, string $autosystem_email)
    {
        $this->id = self::SINGLETON_ID;
        $this->site_name = $site_name;
        $this->contact_email = $contact_email;
        $this->autosystem_email = $autosystem_email;
    }

    public static function getSingletonId(): int
    {
        return self::SINGLETON_ID;
    }

    public function getSiteName(): string
    {
        return $this->site_name;
    }

    public function setSiteName(string $site_name): void
    {
        $this->site_name = $site_name;
    }

    public function getContactEmail(): string
    {
        return $this->contact_email;
    }

    public function setContactEmail(string $contact_email): void
    {
        $this->contact_email = $contact_email;
    }

    public function getAutosystemEmail(): string
    {
        return $this->autosystem_email;
    }

    public function setAutosystemEmail(string $autosystem_email): void
    {
        $this->autosystem_email = $autosystem_email;
    }
}
