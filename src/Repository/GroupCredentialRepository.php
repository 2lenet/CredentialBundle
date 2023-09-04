<?php

namespace Lle\CredentialBundle\Repository;

use Lle\CredentialBundle\Entity\Group;
use Doctrine\ORM\EntityRepository;
use Lle\CredentialBundle\Entity\GroupCredential;

class GroupCredentialRepository extends EntityRepository
{
    public function findOneGroupCred(string|int|Group $group, ?string $cred): ?GroupCredential
    {
        $qb = $this->createQueryBuilder('l')
            ->join('l.groupe', 'g')
            ->join('l.credential', 'c')
            ->where('c.role = :cred')
            ->setParameter("cred", $cred)
            ->andWhere('g.name = :group')
            ->setParameter("group", $group);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function updateCredentials(int|Group $group, array $credentials, bool $allowed): void
    {
        $this->createQueryBuilder("gc")
            ->update()
            ->set('gc.allowed', ':allowed')
            ->andWhere('gc.groupe = :group')
            ->andWhere('gc.credential IN (:credentials)')
            ->setParameters([
                'allowed' => $allowed,
                'group' => $group,
                'credentials' => $credentials,
            ])
            ->getQuery()
            ->execute();
    }

    public function findByGroup(int|Group $group): mixed
    {
        return $this->createQueryBuilder("gc")
            ->andWhere("gc.groupe = :group")
            ->setParameter("group", $group)
            ->getQuery()
            ->getResult();
    }
}
