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
use Erichard\DmsBundle\Entity\DocumentMetadata;

/**
 * Class RootNodeLoadData
 *
 * @package Erichard\DmsBundle\DataFixture\ORM
 */
class MetadatasLoadData implements FixtureInterface, OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $metadataArray = array(
            0 => array(
                'setName' => 'DESCRIPTION',
                'setLabel' => 'description_label',
                'setType' => 'textarea',
                'setScope' => 'document',
                'setRequired' => false,
                'setVisible' => true,
            ),
            1 => array(
                'setName' => 'AUTHOR',
                'setLabel' => 'author_label',
                'setType' => 'text',
                'setScope' => 'document',
                'setRequired' => false,
                'setVisible' => false,
            ),
            2 => array(
                'setName' => 'FILESIZE',
                'setLabel' => 'filesize_label',
                'setType' => 'text',
                'setScope' => 'document',
                'setRequired' => false,
                'setVisible' => false,
            ),
            3 => array(
                'setName' => 'VERSION',
                'setLabel' => 'version_label',
                'setType' => 'text',
                'setScope' => 'document',
                'setRequired' => false,
                'setVisible' => false,
            ),
        );
        foreach ($metadataArray as $meta) {
            $metadata = new DocumentMetadata();
            foreach ($meta as $method => $value) {
                $metadata->$method($value);
            }
            $manager->persist($metadata);
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
