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
     * @ORM\JoinColumn(nullable=false)
     */     
    private $credential;

    /**
     * @ORM\ManyToOne(targetEntity="Group")
     * @ORM\JoinColumn(nullable=false)
     */
    private $groupe;

   /**
     * @ORM\Column(type="boolean")
     */
    private $allowed = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the value of allowed
     */ 
    public function isAllowed()
    {
        return $this->allowed;
    }

    /**
     * Set the value of allowed
     *
     * @return  self
     */ 
    public function setAllowed($allowed)
    {
        $this->allowed = $allowed;

        return $this;
    }

    /**
     * Get the value of credential
     */ 
    public function getCredential()
    {
        return $this->credential;
    }

    /**
     * Set the value of credential
     *
     * @return  self
     */ 
    public function setCredential($credential)
    {
        $this->credential = $credential;

        return $this;
    }

    /**
     * Get the value of groupe
     */ 
    public function getGroupe()
    {
        return $this->groupe;
    }

    /**
     * Set the value of groupe
     *
     * @return  self
     */ 
    public function setGroupe($groupe)
    {
        $this->groupe = $groupe;

        return $this;
    }
}
