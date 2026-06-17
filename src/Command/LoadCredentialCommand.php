<?php

namespace Lle\CredentialBundle\Command;

use Lle\CredentialBundle\Exception\ConfigurationClientUrlNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectCodeNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectTokenNotDefinedException;
use Lle\CredentialBundle\Exception\RemoteApiException;
use Lle\CredentialBundle\Service\LoadCredentialService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'lle:credential:load',
    description: 'Load credentials configuration',
)]
class LoadCredentialCommand extends Command
{
    public function __construct(
        protected LoadCredentialService $loadCredentialService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->loadCredentialService->load();
            $output->writeln('<info>Credentials loaded successfully.</info>');
        } catch (ConfigurationProjectCodeNotDefinedException | ConfigurationClientUrlNotDefinedException | ConfigurationProjectTokenNotDefinedException) {
            $output->writeln('<error>You must define client configuration.</error>');
            return Command::FAILURE;
        } catch (RemoteApiException $e) {
            $output->writeln('<error>Remote error: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
