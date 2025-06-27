<?php

namespace Shoprenter\OauthJWTSecurity\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('shoprenter');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('oauth_jwt_security')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('public_key_path')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->info('Path to the public key file used to verify JWT tokens')
                        ->end()
                    ->end()
                ->end() // End of oauth_jwt_security
            ->end()
        ;

        return $treeBuilder;
    }
}
