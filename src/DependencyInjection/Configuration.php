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
        $children = $rootNode->children();
        $children
            ->scalarNode('client_url')
            ->defaultNull()
            ->end();
        $children
            ->scalarNode('client_public_url')
            ->defaultNull()
            ->end();
        $children
            ->scalarNode('project_code')
            ->defaultNull()
            ->end();
        $children
            ->scalarNode('project_token')
            ->defaultNull()
            ->end();

        return $treeBuilder;
    }
}
