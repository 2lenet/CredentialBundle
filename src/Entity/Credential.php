<?php

namespace Lle\CredentialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Lle\CredentialBundle\Repository\CredentialRepository")
 * @ORM\Table(name="lle_credential_credential")
 * 
 */
class Credential
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $role;

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
}
