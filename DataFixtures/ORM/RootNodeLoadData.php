<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 15:25
 */

namespace Erichard\DmsBundle\DataFixture\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Erichard\DmsBundle\Entity\DocumentNode;

/**
 * Class RootNodeLoadData
 *
 * @package Erichard\DmsBundle\DataFixture\ORM
 */
class RootNodeLoadData implements FixtureInterface, OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $rootNode = new DocumentNode();
        $rootNode
            ->setName('ROOT')
            ->setUniqRef('ROOT');
        $manager->persist($rootNode);
        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }
}
