<?php

namespace Lle\CredentialBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;

class LleCredentialExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        // SERVICES
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
    }
}
