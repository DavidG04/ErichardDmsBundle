<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 15:25
 */

namespace Erichard\DmsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class ErichardDmsExtension
 *
 * @package Erichard\DmsBundle\DependencyInjection
 */
class ErichardDmsExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if (count($config['permission']['roles_node']) === 0 && empty($config['permission']['role_provider'])) {
            throw new \RuntimeException("The DMS need to know which roles it can use. Please configure 'erichard_dms.permission.roles' or 'erichard_dms.permission.role_provider.");
        }

        foreach ($config as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $subName => $subValue) {
                    $container->setParameter('dms.'.$name.'.'.$subName, $subValue);
                }
            } else {
                $container->setParameter('dms.'.$name, $value);
            }
        }
    }
}
