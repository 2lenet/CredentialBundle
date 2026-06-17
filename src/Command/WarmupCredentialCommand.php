<?php

namespace Lle\CredentialBundle\Command;

use Lle\CredentialBundle\Exception\ConfigurationClientUrlNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectCodeNotDefinedException;
use Lle\CredentialBundle\Exception\ConfigurationProjectTokenNotDefinedException;
use Lle\CredentialBundle\Exception\RemoteApiException;
use Lle\CredentialBundle\Service\WarmupCredentialService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'lle:credential:warmup',
    description: 'Update credentials list',
)]
class WarmupCredentialCommand extends Command
{
    public function __construct(
        protected WarmupCredentialService $warmupCredentialService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->warmupCredentialService->warmup();
            $output->writeln('<info>Credentials warmed up successfully.</info>');
        } catch (ConfigurationProjectCodeNotDefinedException | ConfigurationClientUrlNotDefinedException | ConfigurationProjectTokenNotDefinedException) {
            $output->writeln('<error>You must define client configuration.</error>');
            return Command::FAILURE;
        } catch (RemoteApiException $e) {
            $output->writeln('<comment>Remote sync failed: ' . $e->getMessage() . '</comment>');
        }

        return Command::SUCCESS;
    }
}
