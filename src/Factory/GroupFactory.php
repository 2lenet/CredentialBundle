<?php

namespace Lle\CredentialBundle\Factory;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Dto\GroupDto;
use Lle\CredentialBundle\Entity\Group;

class GroupFactory
{
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    public function createGroup(
        string $name,
        string $libelle = null,
        bool $isRole = true,
        bool $active = true,
        ?string $requiredRole = null,
        ?int $tri = null,
        ?bool $isNew = true
    ): Group {
        if ($isNew) {
            $group = new Group();
        } else {
            /** @var Group $group */
            $group = $this->em->getRepository(Group::class)->findOneBy(['name' => $name]);
        }
        $group->setName($name);
        $group->setLibelle($libelle);
        $group->setIsRole($isRole);
        $group->setActif($active);
        $group->setRequiredRole($requiredRole);

        if (!$tri) {
            $lastGroup = $this->em->getRepository(Group::class)->findByLatestTri();
            if ($lastGroup) {
                $group->setTri($lastGroup->getTri() + 1);
            } else {
                $group->setTri(0);
            }
        } else {
            $group->setTri($tri);
        }

        $this->em->persist($group);
        $this->em->flush();

        return $group;
    }


    public function createGroupDto(Group $group): GroupDto
    {
        $groupDto = new GroupDto();
        $groupDto->name = $group->getName() ?? '';
        $groupDto->libelle = $group->getLibelle() ?? '';
        $groupDto->isRole = $group->isRole() ?? true;
        $groupDto->active = $group->isActif() ?? true;
        $groupDto->requiredRole = $group->getRequiredRole() ?? false;
        $groupDto->tri = $group->getTri();

        return $groupDto;
    }
}
