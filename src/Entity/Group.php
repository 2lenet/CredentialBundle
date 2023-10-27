<?php

namespace Lle\CredentialBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Lle\CredentialBundle\Repository\GroupRepository;

#[ORM\Table(name: 'lle_credential_group')]
#[ORM\Entity(repositoryClass: GroupRepository::class)]
class Group implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'groupe', targetEntity: GroupCredential::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $credentials;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isRole = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $actif = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $requiredRole = null;

    #[ORM\Column(type: 'integer')]
    private ?int $tri = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $libelle = null;

    public function __toString(): string
    {
        return $this->libelle ?? '';
    }

    public function __construct()
    {
        $this->credentials = new ArrayCollection();
    }

    public function jsonSerialize(): mixed
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "isRole" => $this->isRole,
            "libelle" => $this->libelle,
            "requiredRole" => $this->requiredRole,
            "tri" => $this->tri,
            "actif" => $this->actif,
        ];
    }

    public function fromArray(array $data): void
    {
        $this->id = $data["id"];
        $this->name = $data["name"];
        $this->libelle = $data["libelle"];
        $this->isRole = $data["isRole"];
        $this->tri = $data["tri"];
        $this->requiredRole = $data["requiredRole"];
        $this->actif = $data["actif"];
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

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(?bool $actif): self
    {
        $this->actif = $actif;

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

    public function getTri(): ?int
    {
        return $this->tri;
    }

    public function setTri(?int $tri): self
    {
        $this->tri = $tri;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(?string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }
}
