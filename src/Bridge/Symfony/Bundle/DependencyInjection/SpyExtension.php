<?php

namespace Eniams\Spy\Bridge\Symfony\Bundle\DependencyInjection;

use Eniams\Spy\Cloner\ClonerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * The extension of this bundle.
 *
 * @author Smaine Milianni <contact@smaine.me>
 */
final class SpyExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../../Resources/config')
        );

        $loader->load('services.xml');

        $container->registerForAutoconfiguration(ClonerInterface::class)
            ->addTag('spy.cloner');
    }
}
