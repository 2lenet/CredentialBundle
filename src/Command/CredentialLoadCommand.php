<?php

namespace Lle\CredentialBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Entity\Group;
use Lle\CredentialBundle\Entity\GroupCredential;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'credential:load',
    description: 'Load Credential configuration',
)]
class CredentialLoadCommand extends Command
{
    public function __construct(
        protected EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = "config/credentials.json";
        $output->writeln("Load Credentials from file $filename");
        $data = json_decode(file_get_contents($filename), true);
        $this->em->getRepository(Credential::class)->createQueryBuilder("c")->delete()->getQuery()->execute();
        $this->em->getRepository(GroupCredential::class)->createQueryBuilder("c")->delete()->getQuery()->execute();
        $this->em->getRepository(Group::class)->createQueryBuilder("c")->delete()->getQuery()->execute();

        // keep the ids
        $metadata = $this->em->getClassMetaData(Credential::class);
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
        $metadata = $this->em->getClassMetaData(Group::class);
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());

        foreach ($data["credential"] as $cred) {
            $c = new Credential();
            $c->fromArray($cred);
            $this->em->persist($c);
        }
        foreach ($data["group"] as $group) {
            $g = new Group();
            $g->fromArray($group);
            $this->em->persist($g);
        }
        foreach ($data["group_credential"] as $groupcred) {
            $gc = new GroupCredential();
            $c = $this->em->getReference(Credential::class, $groupcred["credential"]);
            $g = $this->em->getReference(Group::class, $groupcred["group"]);
            $gc->setCredential($c);
            $gc->setGroupe($g);
            $gc->setAllowed($groupcred["allowed"]);
            $gc->setStatusAllowed($groupcred["statusAllowed"]);
            $this->em->persist($gc);
        }
        $this->em->flush();

        return Command::SUCCESS;
    }
}
