<?php

namespace Lle\CredentialBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Lle\CredentialBundle\Entity\Credential;

/**
 * @method Credential|null find($id, $lockMode = null, $lockVersion = null)
 * @method Credential|null findOneBy(array $criteria, array $orderBy = null)
 * @method Credential[]    findAll()
 * @method Credential[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CredentialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Credential::class);
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.visible = true')
            ->orWhere('c.visible IS NULL')
            ->orderBy('c.rubrique', 'ASC')
            ->addOrderBy('c.tri', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByLatestTri(): ?Credential
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.tri IS NOT NULL')
            ->orderBy('c.tri', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
