<?php

namespace Lle\CredentialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Lle\CredentialBundle\Repository\GroupCredentialRepository")
 * @ORM\Table(name="lle_credential_group_credential")
 *
 */
class GroupCredential
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\ManyToOne(targetEntity="Credential")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $credential;
    /**
     * @ORM\ManyToOne(targetEntity="Group", inversedBy="credentials")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $groupe;
    /**
     * @ORM\Column(type="boolean")
     */
    private $allowed = false;
    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default": false})
     */
    private $statusAllowed = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the value of allowed
     */
    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    /**
     * Set the value of allowed
     *
     * @return  self
     */
    public function setAllowed(bool $allowed): self
    {
        $this->allowed = $allowed;

        return $this;
    }

    /**
     * Get the value of credential
     */
    public function getCredential(): ?Credential
    {
        return $this->credential;
    }

    /**
     * Set the value of credential
     *
     * @return  self
     */
    public function setCredential(Credential $credential): self
    {
        $this->credential = $credential;

        return $this;
    }

    /**
     * Get the value of groupe
     */
    public function getGroupe(): ?Group
    {
        return $this->groupe;
    }

    /**
     * Set the value of groupe
     *
     * @return  self
     */
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
