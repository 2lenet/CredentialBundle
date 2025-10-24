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
            ->setLibelle($groupDto->libelle)
            ->setIsRole($groupDto->isRole)
            ->setActif($groupDto->active)
            ->setRequiredRole($groupDto->requiredRole)
            ->setTri($groupDto->tri ?? $this->getTri());

        $this->em->persist($group);

        return $group;
    }

    public function getTri(): int
    {
        $lastGroup = $this->em->getRepository(Group::class)->findOneBy([], ['tri' => 'DESC']);
        if ($lastGroup) {
            return $lastGroup->getTri() + 1;
        }

        return 0;
    }
}
