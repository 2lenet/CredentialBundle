<?php

namespace Lle\CredentialBundle\Repository;

use Lle\CredentialBundle\Entity\Credential;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\EntityRepository;

class CredentialRepository extends EntityRepository
{
    public function findAllOrdered()
    {
        return $this->createQueryBuilder('c')//, 'c.rubrique')
        ->andWhere('c.visible = true')
            ->orWhere('c.visible IS NULL')
            ->orderBy('c.rubrique', 'ASC')
            ->addOrderBy('c.role', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
