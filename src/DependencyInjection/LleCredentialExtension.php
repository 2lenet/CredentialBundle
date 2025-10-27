<?php

namespace Lle\CredentialBundle\DependencyInjection;

use Lle\CredentialBundle\Contracts\CredentialWarmupInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class LleCredentialExtension extends Extension implements ExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
        $container->registerForAutoconfiguration(CredentialWarmupInterface::class)->addTag('credential.warmup');

        $configuration = new Configuration();
        $processedConfig = $this->processConfiguration($configuration, $configs);

        $container->setParameter('lle_credential.client_url', $processedConfig['client_url']);
        $container->setParameter('lle_credential.client_public_url', $processedConfig['client_public_url']);
        $container->setParameter('lle_credential.project_code', $processedConfig['project_code']);
        $container->setParameter('lle_credential.project_token', $processedConfig['project_token']);
    }
}
