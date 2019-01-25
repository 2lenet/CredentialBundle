<?php

namespace Lle\CredentialBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Lle\CredentialBundle\Entity\Credential;
use Doctrine\ORM\EntityRepository;
use Lle\CredentialBundle\Entity\Group;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Security;

class GroupRepository extends ServiceEntityRepository
{

    private $security;

    public function __construct(RegistryInterface $registry, Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Group::class);
    }

    public function findMineQb() {
        $roles = array_map(function($r) { return $r->getRole(); }, $this->security->getToken()->getRoles());
        $qb = $this->createQueryBuilder('g')
            ->where('g.requiredRole in (:roles)')
            ->setParameter("roles", $roles);
        return $qb;
    }
}
