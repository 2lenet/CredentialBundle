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
            ->orderBy('c.section', 'ASC')
            ->addOrderBy('c.label', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
