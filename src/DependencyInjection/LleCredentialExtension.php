<?php

namespace Lle\CredentialBundle\DependencyInjection;

use Lle\CredentialBundle\Contracts\CredentialWarmupInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class LleCredentialExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
        $container->registerForAutoconfiguration(CredentialWarmupInterface::class)->addTag('credential.warmup');
    }
}
