<?php

namespace Lle\CredentialBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lle\CredentialBundle\Entity\Group;

/**
 * @method Group|null find($id, $lockMode = null, $lockVersion = null)
 * @method Group|null findOneBy(array $criteria, array $orderBy = null)
 * @method Group[]    findAll()
 * @method Group[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);
    }

    public function findAllOrdered(): mixed
    {
        return $this->createQueryBuilder('g')
            ->orderBy('g.rank', 'ASC')
            ->where('g.active = 1')
            ->getQuery()
            ->getResult();
    }

    public function findByProjectExceptSuperAdmin(): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.name != :superadmin')
            ->setParameter('superadmin', 'SUPER_ADMIN')
            ->orderBy('g.rank', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
