<?php

namespace Lle\CredentialBundle\Repository;

use Lle\CredentialBundle\Entity\Credential;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\EntityRepository;


class CredentialRepository extends EntityRepository
{

//    /**
//     * @return Credential[] Returns an array of Credential objects
//     */
    
    public function findAllOrdered()
    {
        return $this->createQueryBuilder('c', 'c.rubrique')
            ->orderBy('c.rubrique', 'ASC')
            ->addOrderBy('c.role', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    

    /*
    public function findOneBySomeField($value): ?Credential
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
