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
            ->scalarNode('project_name')
            ->isRequired()
            ->end();
        $children
            ->scalarNode('crudit_studio_url')
            ->isRequired()
            ->end();
        $children
            ->scalarNode('crudit_studio_public_url')
            ->isRequired()
            ->end();

        return $treeBuilder;
    }
}