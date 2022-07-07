<?php

namespace Lle\CredentialBundle\Repository;

use Lle\CredentialBundle\Entity\GroupCredential;
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

    public function updateCredentials($group, $credentials, $allowed)
    {
        $queryBuilder = $this->createQueryBuilder("gc")
            ->update()
            ->set('gc.allowed', ':allowed')
            ->andWhere('gc.groupe = :group')
            ->andWhere('gc.credential IN (:credentials)')
            ->setParameters([
                'allowed' => $allowed,
                'group' => $group,
                'credentials' => $credentials,
            ])->getQuery()->execute();
    }

    public function findByGroup($group)
    {
        return $this->createQueryBuilder("gc")
            ->andWhere("gc.groupe = :group")
            ->setParameter("group", $group)
            ->getQuery()
            ->getResult();
    }
}
