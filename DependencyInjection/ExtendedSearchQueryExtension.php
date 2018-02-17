<?php

namespace ExtendedSearchQueryBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

/**
 * Injects configuration from this bundle
 */
class ExtendedSearchQueryExtension extends Extension
{
    /**
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $path = realpath(__DIR__ . '/../Resources/config');

        if (is_dir($path)) {
            $loader = new YamlFileLoader($container, new FileLocator($path));
            $loader->load('services.yml');
        }
    }
}