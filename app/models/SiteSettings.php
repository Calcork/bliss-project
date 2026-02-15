<?php

namespace App\Models;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'site_settings')]
class SiteSettings
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id; // @phpstan-ignore property.onlyRead

    #[ORM\Column(type: 'string', length: 255)]
    private string $site_name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $contact_email;

    public function __construct(
        string $site_name,
        string $contact_email
    ) {
        $this->site_name = $site_name;
        $this->contact_email = $contact_email;
    }

    public function getId(): int
    {
        return $this->id;
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
}
