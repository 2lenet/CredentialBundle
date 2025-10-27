<?php

namespace Lle\CredentialBundle\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Dto\GroupDto;
use Lle\CredentialBundle\Entity\Group;

class GroupFactory
{
    public function __construct(
        protected EntityManagerInterface $em,
    ) {
    }

    public function createFromDto(GroupDto $groupDto): Group
    {
        $group = new Group();
        $group
            ->setName($groupDto->name)
            ->setLabel($groupDto->label)
            ->setIsRole($groupDto->isRole)
            ->setActive($groupDto->active)
            ->setRequiredRole($groupDto->requiredRole)
            ->setRank($groupDto->rank ?? $this->getRank());

        $this->em->persist($group);

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
