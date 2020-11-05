<?php

namespace Lle\CredentialBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Lle\CredentialBundle\Entity\Credential;
use Doctrine\ORM\EntityRepository;
use Lle\CredentialBundle\Entity\Group;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class GroupRepository extends ServiceEntityRepository
{

    private $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        $this->security = $security;
        parent::__construct($registry, Group::class);
    }

    public function findMineQb() {
        $role = $this->security->getToken()->getRoles()[0]->getRole();
        $qb = $this->createQueryBuilder('entity')
            ->andWhere(' FIND_IN_SET (:roles, entity.requiredRole) >0')
            ->setParameter("roles", $role);
        return $qb;
    }

    public function findMine() {
        $qb = $this->findMineQb();
        $qb->orderBy('entity.tri');
        return $qb->getQuery()->getResult();
    }

    public function findAllOrdered()
    {
        return $this->createQueryBuilder('g')//, 'c.rubrique')
        ->orderBy('g.tri', 'ASC')->where('g.actif = 1')
            ->getQuery()
            ->getResult()
            ;
    }
}
