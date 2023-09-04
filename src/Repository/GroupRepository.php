<?php

namespace Lle\CredentialBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Lle\CredentialBundle\Entity\Group;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

class GroupRepository extends ServiceEntityRepository
{
    private Security $security;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Group::class);
        $this->security = $security;
    }

    public function findMineQb(): QueryBuilder
    {
        $token = $this->security->getToken();
        $role = $token->getRoles()[0]->getRole();

        return $this->createQueryBuilder('entity')
            ->andWhere('FIND_IN_SET (:roles, entity.requiredRole) > 0')
            ->setParameter("roles", $role);
    }

    public function findMine(): mixed
    {
        return $this->findMineQb()
            ->orderBy('entity.tri')
            ->getQuery()
            ->getResult();
    }

    public function findAllOrdered(): mixed
    {
        return $this->createQueryBuilder('g')
            ->orderBy('g.tri', 'ASC')->where('g.actif = 1')
            ->getQuery()
            ->getResult();
    }

    public function findRolesQb(): QueryBuilder
    {
        return $this
            ->findMineQb()
            ->andWhere('entity.isRole = 1')
            ->andWhere('entity.actif = 1')
            ->orderBy('entity.tri');
    }
}
