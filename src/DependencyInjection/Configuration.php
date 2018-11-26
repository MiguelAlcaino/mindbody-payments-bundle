<?php

namespace MiguelAlcaino\MindbodyPaymentsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('miguel_alcaino_mindbody_payments');

        $rootNode
            ->children()
                ->arrayNode('handler')
                    ->children()
                        ->scalarNode('refund_handler')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

}