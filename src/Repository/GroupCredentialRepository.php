<?php

namespace Lle\CredentialBundle\Repository;

use Lle\CredentialBundle\Entity\UserGroup;
use Symfony\Bridge\Doctrine\RegistryInterface;

use Doctrine\ORM\EntityRepository;

class GroupCredentialRepository extends EntityRepository
{

    public function findOneGroupCred($group, $cred) {
        $qb = $this->createQueryBuilder('l')
            ->join('l.groupe','g')
            ->join('l.credential','c')
            ->where('c.role = :cred')
            ->setParameter("cred", $cred)
            ->andWhere('g.name = :group')
            ->setParameter("group", $group)
        ;
        return $qb->getQuery()->getOneOrNullResult();
    }




}
