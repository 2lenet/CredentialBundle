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
            ->end();
        $children
            ->scalarNode('client_public_url')
            ->end();
        $children
            ->scalarNode('project_code')
            ->end();

        return $treeBuilder;
    }
}
