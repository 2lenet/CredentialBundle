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
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'credential:dump',
    description: 'Dump credential configuration',
)]
class CredentialDumpCommand extends Command
{
    public function __construct(
        protected KernelInterface $kernel,
        protected EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = $this->kernel->getProjectDir() . '/data/credential/credentials.json';
        $output->writeln("Dump Credentials to file $filename");

        $credentials = $this->em->getRepository(Credential::class)->findAllOrdered();
        $groups = $this->em->getRepository(Group::class)->findAll();
        $groupCredentials = $this->em->getRepository(GroupCredential::class)->findAll();

        $file = fopen($filename, 'wb');
        if ($file) {
            fwrite(
                $file,
                (string)json_encode(
                    ['credential' => $credentials, 'group' => $groups, 'group_credential' => $groupCredentials]
                )
            );
            fclose($file);
        }

        return Command::SUCCESS;
    }
}
