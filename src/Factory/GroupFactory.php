<?php

namespace Lle\CredentialBundle\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Group;

class GroupFactory
{
    public function __construct(
        protected EntityManagerInterface $em,
    ) {
    }

    public function createFromArray(array $groupArray): Group
    {
        $group = $this->em->getRepository(Group::class)->findOneBy(['name' => $groupArray['name']]);
        if (!$group) {
            $group = new Group();
            $group->setName($groupArray['name']);

            $this->em->persist($group);
        }

        $group
            ->setLabel($groupArray['label'])
            ->setIsRole($groupArray['isRole'])
            ->setActive($groupArray['active'])
            ->setRequiredRole($groupArray['requiredRole'])
            ->setRank($groupArray['rank'] ?? $this->getRank());

        return $group;
    }

    public function getRank(): int
    {
        $lastGroup = $this->em->getRepository(Group::class)->findOneBy([], ['rank' => 'DESC']);
        if ($lastGroup) {
            return $lastGroup->getRank() + 1;
        }

        return 0;
    }
}
