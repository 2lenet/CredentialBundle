<?php

namespace Lle\CredentialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Lle\CredentialBundle\Repository\CredentialRepository;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Table(name: 'lle_credential_credential')]
#[ORM\Entity(repositoryClass: CredentialRepository::class)]
class Credential
{
    public const string CREDENTIAL_API_GROUP = 'crendential-api';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::CREDENTIAL_API_GROUP])]
    private ?string $role = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups([self::CREDENTIAL_API_GROUP])]
    private ?string $label = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups([self::CREDENTIAL_API_GROUP])]
    private ?string $section = null;

    #[ORM\Column(type: 'boolean', nullable: true, options: ['default' => true])]
    #[Groups([self::CREDENTIAL_API_GROUP])]
    private ?bool $visible = true;

    #[ORM\Column(type: 'json', nullable: true)]
    #[Groups([self::CREDENTIAL_API_GROUP])]
    private ?array $statusList = [];

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $createdAt;


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

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $lable): self
    {
        $this->label = $lable;

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
    public function setLibelle(string $label): self
    {
        $this->label = $label;

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

    public function getSection(): ?string
    {
        return $this->section;
    }

    public function setSection(?string $section): self
    {
        $this->section = $section;

        return $this;
    }

    /**
     * @deprecated use getSection
     */
    public function getRubrique(): ?string
    {
        return $this->section;
    }

    /**
     * @deprecated use setSection
     */
    public function setRubrique(?string $section): self
    {
        $this->section = $section;

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

    public function getStatusList(): ?array
    {
        return $this->statusList;
    }

    public function setStatusList(array $statusList,): self
    {
        $this->statusList = $statusList;

        return $this;
    }

    /**
     * @deprecated use getStatusList
     */
    public function getListeStatus(): ?array
    {
        return $this->statusList;
    }

    /**
     * @deprecated use setStatusList
     */
    public function setListeStatus(array $statusList,): self
    {
        $this->statusList = $statusList;

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
