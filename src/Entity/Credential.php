<?php

namespace Lle\CredentialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Lle\CredentialBundle\Repository\CredentialRepository;

#[ORM\Table(name: 'lle_credential_credential')]
#[ORM\Entity(repositoryClass: CredentialRepository::class)]
class Credential
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', length: 255)]
    private ?string $role = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $libelle = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $rubrique = null;

    #[ORM\Column(type: 'integer')]
    private ?int $tri = null;

    #[ORM\Column(type: 'boolean', nullable: true, options: ['default' => true])]
    private ?bool $visible = true;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $listeStatus = [];

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

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

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

    public function setListeStatus(array $listeStatus): self
    {
        $this->listeStatus = $listeStatus;

        return $this;
    }
}
