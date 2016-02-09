<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 22/01/2016
 * Time: 14:48
 */

namespace Erichard\DmsBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class DocumentNodeMetadataLnk
 *
 * @package Erichard\DmsBundle\Entity
 */
class DocumentNodeMetadataLnk extends AbstractMetadataLnk implements DocumentNodeMetadataLnkInterface
{
    /**
     * node
     *
     * @var \Erichard\DmsBundle\Entity\DocumentNode
     */
    protected $node;

    /**
     * DocumentNodeMetadataLnk constructor.
     *
     * @param \Erichard\DmsBundle\Entity\DocumentMetadata $metaData
     */
    public function __construct(DocumentMetadata $metaData)
    {
        parent::__construct($metaData);
    }

    /**
     * {@inheritDoc}
     */
    public function setNode(DocumentNodeInterface $node)
    {
        $this->node = $node;

        return $this;
    }
}
