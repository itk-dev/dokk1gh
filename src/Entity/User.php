<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Entity;

use App\Repository\UserRepository;
use App\Trait\AeosDataEntity;
use App\Trait\BlameableEntity;
use App\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\SoftDeleteable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[SoftDeleteable]
#[UniqueEntity(fields: 'email', message: 'This email is already in use.')]
#[UniqueEntity(fields: 'aeosId', message: 'This aeosId is already in use.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, AeosEntityInterface, \Stringable, Timestampable
{
    use AeosDataEntity;
    use BlameableEntity;
    use SoftDeleteableEntity;
    use TimestampableEntity;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTime $lastLoggedInAt = null;

    #[ORM\Column(name: 'gdpr_accepted_at', type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?\DateTime $gdprAcceptedAt = null;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\Template>
     */
    #[ORM\ManyToMany(targetEntity: Template::class)]
    protected Collection $templates;

    #[ORM\Column(type: Types::BOOLEAN)]
    protected bool $enabled = true;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\AeosPersonId]
    private ?string $aeosId = null;

    #[ORM\Column(type: Types::STRING, unique: true, nullable: true)]
    private ?string $apiKey;

    /**
     * The hashed password.
     */
    #[ORM\Column]
    private ?string $password = null;

    public function __construct()
    {
        $this->templates = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getUserIdentifier();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = Role::USER->value;

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastLoggedInAt(): ?\DateTime
    {
        return $this->lastLoggedInAt;
    }

    public function setLastLoggedInAt(?\DateTime $lastLoggedInAt): static
    {
        $this->lastLoggedInAt = $lastLoggedInAt;

        return $this;
    }

    public function getGdprAcceptedAt(): ?\DateTime
    {
        return $this->gdprAcceptedAt;
    }

    public function setGdprAcceptedAt(\DateTime $gdprAcceptedAt): void
    {
        $this->gdprAcceptedAt = $gdprAcceptedAt;
    }

    public function getTemplates(): Collection
    {
        return $this->templates;
    }

    public function addTemplate(Template $template): static
    {
        if (!$this->templates->contains($template)) {
            $this->templates->add($template);
        }

        return $this;
    }

    public function removeTemplate(Template $template): static
    {
        $this->templates->removeElement($template);

        return $this;
    }

    public function getAeosId(): ?string
    {
        return $this->aeosId;
    }

    public function setAeosId(string $aeosId): static
    {
        $this->aeosId = $aeosId;

        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): static
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    #[Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if (0 === $this->getTemplates()->count()) {
            $context->buildViolation('At least one template is required.')
                ->atPath('templates')
                ->addViolation();
        }
    }
}
