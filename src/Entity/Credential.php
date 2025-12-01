<?php

namespace Lle\CredentialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Lle\CredentialBundle\Repository\CredentialRepository;

#[ORM\Table(name: 'lle_credential_credential')]
#[ORM\Entity(repositoryClass: CredentialRepository::class)]
class Credential implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $role = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $libelle = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $rubrique = null;

    #[ORM\Column(type: 'integer')]
    private ?int $tri = null;

    #[ORM\Column(type: 'boolean', nullable: true, options: ['default' => true])]
    private ?bool $visible = true;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $listeStatus = [];

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $createdAt;

    public function jsonSerialize(): mixed
    {
        $data = [
            "id" => $this->id,
            "role" => $this->role,
            "libelle" => $this->libelle,
            "rubrique" => $this->rubrique,
            "visible" => $this->visible,
            "tri" => $this->tri,
            "listeStatus" => $this->listeStatus,
        ];
        if ($this->createdAt !== null) {
            $data["createdAt"] = $this->createdAt->format("Y-m-d H:i:s");
        }

        return $data;
    }

    public function fromArray(array $data): void
    {
        $this->id = $data["id"];
        $this->role = $data["role"];
        $this->libelle = $data["libelle"];
        $this->rubrique = $data["rubrique"];
        $this->tri = $data["tri"];
        $this->visible = $data["visible"];
        $this->listeStatus = $data["listeStatus"];
        if (array_key_exists("createdAt", $data)) {
            $this->createdAt = new \DateTimeImmutable($data["createdAt"]);
        }
    }

    public function __toString()
    {
        return (string)$this->role;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getRubrique(): ?string
    {
        return $this->rubrique;
    }

    public function setRubrique(?string $rubrique): self
    {
        $this->rubrique = $rubrique;

        return $this;
    }

    public function getTri(): ?int
    {
        return $this->tri;
    }

    public function setTri(int $tri): self
    {
        $this->tri = $tri;

        return $this;
    }

    public function isVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    public function getListeStatus(): ?array
    {
        return $this->listeStatus;
    }

    public function setListeStatus(array $listeStatus,): self
    {
        $this->listeStatus = $listeStatus;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
