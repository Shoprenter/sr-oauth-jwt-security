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
            ->addDefaultsIfNotSet() // A teljes shoprenter konfig is opcionális
                ->children()
                    ->arrayNode('oauth_jwt_security')
                    ->addDefaultsIfNotSet() // Ez biztosítja, hogy az array létezzen, még ha üres is
                    ->children()
                        ->scalarNode('public_key_path')
                        ->info('Path to the public key file used to verify JWT tokens')
                        ->defaultValue('') // Alapértelmezett null érték
                        ->end()
                    ->end()
                ->end() // End of oauth_jwt_security
            ->end();

        return $treeBuilder;
    }
}
