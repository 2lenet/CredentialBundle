<?php

namespace Lle\CredentialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Lle\CredentialBundle\Repository\GroupRepository")
 * @ORM\Table(name="lle_credential_group")
 * 
 */
class Group
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
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="GroupCredential", mappedBy="groupe")
     * @ORM\JoinColumn(nullable=false)
     */
    private $credentials;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isRole;
    

    public function __toString(){
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the value of credentials
     */ 
    public function getCredentials()
    {
        return $this->credentials;
    }

    public function getRoles(){
        $roles = [];
        foreach($this->getCredentials() as $gCredential){
            /* @var GroupCredential $gCredential */
            $roles[] = $gCredential->getCredential()->getRole();
        }
        return $roles;
    }

    /**
     * Set the value of credentials
     *
     * @return  self
     */ 
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;

        return $this;
    }

    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function isRole()
    {
        return $this->isRole;
    }

    /**
     * @param mixed $isRole
     */
    public function setIsRole($isRole)
    {
        $this->isRole = $isRole;
    }


}
