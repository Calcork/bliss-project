<?php

namespace App\Models;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id; /** @phpstan-ignore-line */

    #[ORM\Column(type: 'string', length: 255)]
    private string $first_name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $last_name;

    #[ORM\Column(type: 'boolean')]
    private bool $is_admin = false;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password_hash;

    #[ORM\Column(type: 'string', length: 64, unique: true, nullable: true)]
    private ?string $email_verification_token = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $email_verified_at = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $created_at;

    #[ORM\ManyToOne(targetEntity: Language::class)]
    #[ORM\JoinColumn(name: 'language_id', nullable: false)]
    private Language $language;

    #[ORM\Column(type: 'string', length: 64, unique: true, nullable: true)]
    private ?string $password_reset_token = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $password_reset_expires_at = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    public function __construct(string $first_name, string $last_name, string $email, string $password_hash, Language $language)
    {
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->email = $email;
        $this->password_hash = $password_hash;
        $this->language = $language;
        $this->created_at = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): void
    {
        $this->first_name = $first_name;
        $this->updated_at = new \DateTimeImmutable();
    }

    public function getLastName(): string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): void
    {
        $this->last_name = $last_name;
        $this->updated_at = new \DateTimeImmutable();
    }


    public function getEmail(): string
    {
        return $this->email;
    }

    public function getIsAdmin(): bool
    {
        return $this->is_admin;
    }

    public function setIsAdmin(bool $is_admin): void {

        $this->is_admin = $is_admin;
        $this->updated_at = new \DateTimeImmutable();

    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
        $this->updated_at = new \DateTimeImmutable();
    }

    public function getPasswordHash(): string
    {
        return $this->password_hash;
    }

    public function setPasswordHash(string $password_hash): void
    {
        $this->password_hash = $password_hash;
        $this->updated_at = new \DateTimeImmutable();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function getEmailVerificationToken(): ?string
    {
        return $this->email_verification_token;
    }

    public function setEmailVerificationToken(?string $email_verification_token): void
    {
        $this->email_verification_token = $email_verification_token;
        $this->updated_at = new \DateTimeImmutable();
    }

    public function getEmailVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->email_verified_at;
    }

    public function setEmailVerifiedAt(?\DateTimeImmutable $email_verified_at): void
    {
        $this->email_verified_at = $email_verified_at;
        $this->updated_at = new \DateTimeImmutable();
    }

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function getLanguage(): Language
    {
        return $this->language;
    }

    public function setLanguage(Language $language): void
    {
        $this->language = $language;
        $this->updated_at = new \DateTimeImmutable();
    }

    public function getPasswordResetToken(): ?string
    {
        return $this->password_reset_token;
    }

    public function setPasswordResetToken(?string $password_reset_token): void
    {
        $this->password_reset_token = $password_reset_token;
        $this->updated_at = new \DateTimeImmutable();
    }

    public function getPasswordResetExpiresAt(): ?\DateTimeImmutable
    {
        return $this->password_reset_expires_at;
    }

    public function setPasswordResetExpiresAt(?\DateTimeImmutable $password_reset_expires_at): void
    {
        $this->password_reset_expires_at = $password_reset_expires_at;
        $this->updated_at = new \DateTimeImmutable();
    }
}
