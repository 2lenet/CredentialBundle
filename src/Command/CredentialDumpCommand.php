<?php

namespace Lle\CredentialBundle\Command;

use Lle\CredentialBundle\Repository\CredentialRepository;
use Lle\CredentialBundle\Repository\GroupCredentialRepository;
use Lle\CredentialBundle\Repository\GroupRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'credential:dump',
    description: 'Dump credential configuration',
)]
class CredentialDumpCommand extends Command
{
    public function __construct(
        protected CredentialRepository $credentialRepository,
        protected GroupCredentialRepository $groupCredentialRepository,
        protected GroupRepository $groupRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filename = "config/credentials.json";
        $output->writeln("Dump Credentials to file $filename");
        $creds = $this->credentialRepository->findAllOrdered();
        $groups = $this->groupRepository->findAll();
        $groupCredentials = $this->groupCredentialRepository->findAll();
        $f = fopen($filename, "wb");
        fwrite($f, json_encode(["credential" => $creds, "group" => $groups, "group_credential" => $groupCredentials]));
        fclose($f);

        return Command::SUCCESS;
    }
}
