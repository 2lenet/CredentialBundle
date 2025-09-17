<?php

namespace Lle\CredentialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Lle\CredentialBundle\Repository\GroupCredentialRepository;

#[ORM\Table(name: 'lle_credential_group_credential')]
#[ORM\Entity(repositoryClass: GroupCredentialRepository::class)]
#[ORM\UniqueConstraint(
    name: 'groupe_cred_unique_idx',
    columns: ['groupe_id', 'credential_id']
)]
class GroupCredential
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Credential::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Credential $credential = null;

    #[ORM\ManyToOne(targetEntity: Group::class, inversedBy: 'credentials')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Group $groupe = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $allowed = false;

    #[ORM\Column(type: 'boolean', nullable: true, options: ['default' => false])]
    private ?bool $statusAllowed = false;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function isAllowed(): ?bool
    {
        return $this->allowed;
    }

    public function setAllowed(bool $allowed): self
    {
        $this->allowed = $allowed;

        return $this;
    }

    public function getCredential(): ?Credential
    {
        return $this->credential;
    }

    public function setCredential(Credential $credential): self
    {
        $this->credential = $credential;

        return $this;
    }

    public function getGroupe(): ?Group
    {
        return $this->groupe;
    }

    public function setGroupe(Group $groupe): self
    {
        $this->groupe = $groupe;

        return $this;
    }

    public function isStatusAllowed(): ?bool
    {
        return $this->statusAllowed;
    }

    public function setStatusAllowed(bool $statusAllowed): self
    {
        $this->statusAllowed = $statusAllowed;

        return $this;
    }
}
