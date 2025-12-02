<?php

namespace Lle\CredentialBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('lle_credential');
        $rootNode = $treeBuilder->getRootNode();
        /** @phpstan-ignore-next-line */
        $rootNode
            ->children()
                ->scalarNode('client_url')
                    ->defaultNull()
                ->end()
                ->scalarNode('client_public_url')
                    ->defaultNull()
                ->end()
                ->scalarNode('project_code')
                    ->defaultNull()
                ->end()
                ->scalarNode('project_token')
                    ->defaultNull()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
