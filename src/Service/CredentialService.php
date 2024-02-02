<?php

namespace Lle\CredentialBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;

class CredentialService
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    public function toggleAll(array $groupCredentials, array $credentials, Group $group, bool $checked): void
    {
        $existingCredentials = [];
        foreach ($groupCredentials as $groupCredential) {
            $existingCredentials[$groupCredential->getCredential()->getId()] = $groupCredential;
        }

        /** @var Credential $credential */
        foreach ($credentials as $credential) {
            if (!array_key_exists((int)$credential->getId(), $existingCredentials)) {
                $groupCredential = new GroupCredential();
                $groupCredential->setGroupe($group);
                $groupCredential->setCredential($credential);
                $groupCredential->setAllowed($checked);

                $this->em->persist($groupCredential);
            }
        }
    }

    public function allowedByStatus(?string $group, ?string $statusCred, ?string $parentCred): GroupCredential
    {
        /** @var Group $groupObj */
        $groupObj = $this->em->getRepository(Group::class)->findOneBy(['name' => $group]);
        $parentCred = $this->em->getRepository(Credential::class)->findOneBy(['role' => $parentCred]);

        $credential = $this->em->getRepository(Credential::class)->findOneBy(['role' => $statusCred]);

        if (!$credential) {
            $credential = new Credential();
            $credential->setRole((string)$statusCred);
            $credential->setLibelle((string)$statusCred);
            $credential->setRubrique($parentCred?->getRubrique());
            $credential->setTri(0);
            $credential->setVisible(false);

            $this->em->persist($credential);
        }

        $groupCred = new GroupCredential();
        $groupCred->setGroupe($groupObj);
        $groupCred->setCredential($credential);
        $groupCred->setAllowed(true);

        return $groupCred;
    }

    public function dumpCredentials(string $filename): void
    {
        $credentials = $this->em->getRepository(Credential::class)->findAll();
        $groups = $this->em->getRepository(Group::class)->findAll();
        $groupCredentials = $this->em->getRepository(GroupCredential::class)->findAll();

        $file = fopen($filename, 'wb');
        if ($file) {
            fwrite(
                $file,
                (string)json_encode(
                    ['credential' => $credentials, 'group' => $groups, 'group_credential' => $groupCredentials],
                    JSON_PRETTY_PRINT
                )
            );
            fclose($file);
        }
    }

    public function loadCredentials(string $filename): void
    {
        $data = json_decode((string)file_get_contents($filename), true);

        $this->em->getRepository(Credential::class)->createQueryBuilder('c')->delete()->getQuery()->execute();
        $this->em->getRepository(GroupCredential::class)->createQueryBuilder('c')->delete()->getQuery()->execute();

        // keep the ids
        $metadata = $this->em->getClassMetaData(Credential::class);
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());

        $metadata = $this->em->getClassMetaData(Group::class);
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());

        $metadata = $this->em->getClassMetaData(GroupCredential::class);
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());

        foreach ($data['credential'] as $cred) {
            $c = new Credential();
            $c->fromArray($cred);

            $this->em->persist($c);
        }

        foreach ($data['group'] as $group) {
            $g = $this->em->getRepository(Group::class)->find($group['id']);
            if ($g === null) {
                $g = new Group();
            }

            $g->fromArray($group);

            $this->em->persist($g);
        }

        foreach ($data['group_credential'] as $groupcred) {
            $gc = new GroupCredential();
            $gc->fromArray($groupcred);

            /** @var Credential $c */
            $c = $this->em->getReference(Credential::class, $groupcred['credential']);
            /** @var Group $g */
            $g = $this->em->getReference(Group::class, $groupcred['group']);

            $gc
                ->setGroupe($g)
                ->setCredential($c);

            $this->em->persist($gc);
        }

        $this->em->flush();
    }
}
