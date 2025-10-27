<?php

namespace Lle\CredentialBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lle\CredentialBundle\Repository\GroupRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ORM\Table(name: 'lle_credential_group')]
#[ORM\Entity(repositoryClass: GroupRepository::class)]
class Group
{
    public const string GROUP_API_GROUP = 'group-api';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::GROUP_API_GROUP])]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'groupe', targetEntity: GroupCredential::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $credentials;

    #[ORM\Column(type: 'boolean')]
    #[Groups([self::GROUP_API_GROUP])]
    private ?bool $isRole = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups([self::GROUP_API_GROUP])]
    #[SerializedName('active')]
    private ?bool $active = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::GROUP_API_GROUP])]
    private ?string $requiredRole = null;

    #[ORM\Column(type: 'integer')]
    #[Groups([self::GROUP_API_GROUP])]
    private ?int $rank = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups([self::GROUP_API_GROUP])]
    private ?string $label = null;

    public function __toString(): string
    {
        return $this->libelle ?? '';
    }

    public function __construct()
    {
        $this->credentials = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCredentials(): Collection
    {
        return $this->credentials;
    }

    public function getRoles(): array
    {
        $roles = [];

        foreach ($this->getCredentials() as $gCredential) {
            /* @var GroupCredential $gCredential */
            $roles[] = $gCredential->getCredential()->getRole();
        }

        return $roles;
    }

    public function setCredentials(Collection $credentials): self
    {
        $this->credentials = $credentials;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function isRole(): ?bool
    {
        return $this->isRole;
    }

    public function setIsRole(?bool $isRole): self
    {
        $this->isRole = $isRole;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @deprecated use isActive
     */
    public function isActif(): ?bool
    {
        return $this->active;
    }

    /**
     * @deprecated use setActive
     */
    public function setActif(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getRequiredRole(): ?string
    {
        return $this->requiredRole;
    }

    public function setRequiredRole(?string $requiredRole): self
    {
        $this->requiredRole = $requiredRole;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(?int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * @deprecated use getRank
     */
    public function getTri(): ?int
    {
        return $this->rank;
    }

    /**
     * @deprecated use setRank
     */
    public function setTri(?int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @deprecated use getLabel
     */
    public function getLibelle(): ?string
    {
        return $this->label;
    }

    /**
     * @deprecated use setLabel
     */
    public function setLibelle(?string $label): self
    {
        $this->label = $label;

        return $this;
    }
}
