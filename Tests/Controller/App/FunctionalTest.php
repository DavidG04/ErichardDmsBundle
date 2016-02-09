<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 16:07
 */

namespace Erichard\DmsBundle\Tests\Controller\App;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class FunctionalTest
 *
 * @package Erichard\DmsBundle\Tests\Controller\App
 */
class FunctionalTest extends WebTestCase
{
    /**
     * setupBeforeClass
     *
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public static function setupBeforeClass()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        // Creates the database schema
        $emn = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $schemaTool = new SchemaTool($emn);
        $cmf        = $emn->getMetadataFactory();
        $classes    = $cmf->getAllMetadata();
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($classes);
    }

    /**
     * get
     *
     * @param mixed $service
     *
     * @return object
     */
    protected function get($service)
    {
        return $this->getContainer()->get($service);
    }

    /**
     * getContainer
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getKernel()->getContainer();
    }

    /**
     * getKernel
     *
     * @return \Symfony\Component\HttpKernel\KernelInterface
     */
    protected function getKernel()
    {
        return static::$kernel;
    }

    /**
     * teardown
     */
    protected function teardown()
    {
        static::$kernel->shutdown();
    }
}
