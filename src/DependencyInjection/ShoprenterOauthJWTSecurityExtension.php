<?php

namespace Shoprenter\OauthJWTSecurity\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ShoprenterOauthJWTSecurityExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        if (isset($config['oauth_jwt_security'])) {
            $oauthConfig = $config['oauth_jwt_security'];
            $container->setParameter('shoprenter.oauth_jwt_security.public_key_path', $oauthConfig['public_key_path']);
        }
    }

    /**
     * This extension is loaded under the "shoprenter" namespace.
     */
    public function getAlias(): string
    {
        return 'shoprenter_oauth_jwt_security';
    }
}
