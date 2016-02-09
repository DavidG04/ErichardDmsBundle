<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 16:07
 */

namespace Erichard\DmsBundle\Tests\Controller\App;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Class TestKernel
 *
 * @package Erichard\DmsBundle\Tests\Controller\App
 */
class TestKernel extends Kernel
{
    /**
     * registerBundles
     *
     * @return array
     */
    public function registerBundles()
    {
        return array(
            // Dependencies
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),

            // Current Bundle to test
            new \Erichard\DmsBundle\ErichardDmsBundle(),
        );
    }

    /**
     * registerContainerConfiguration
     *
     * @param LoaderInterface $loader
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config.yml');
    }
}
