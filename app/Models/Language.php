<?php

namespace App\Models;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'languages')]
class Language
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id; /** @phpstan-ignore-line */

    #[ORM\Column(type: 'string', length: 100)]
    private string $name_t;

    #[ORM\Column(type: 'string', length: 2, unique: true)]
    private string $locale;

    public function __construct(string $name_t, string $locale)
    {
        $this->name_t = $name_t;
        $this->locale = $locale;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNameT(): string
    {
        return $this->name_t;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setName(string $name_t): void
    {
        $this->name_t = $name_t;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }
}
