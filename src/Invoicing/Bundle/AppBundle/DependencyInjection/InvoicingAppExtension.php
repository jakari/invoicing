<?php

namespace Invoicing\Bundle\AppBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Loader;

class InvoicingAppExtension extends ConfigurableExtension
{
    /**
     * Configures the passed container according to the merged configuration.
     *
     * @param array $mergedConfig
     * @param ContainerBuilder $container
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yml');

        $container->setParameter('invoicing.database_host', $mergedConfig['database_host']);
        $container->setParameter('invoicing.database_name', $mergedConfig['database_name']);
        $container->setParameter('invoicing.database_user', $mergedConfig['database_user']);
        $container->setParameter('invoicing.database_password', $mergedConfig['database_password']);
    }
}
