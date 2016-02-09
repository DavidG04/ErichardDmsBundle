<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 04/02/2016
 * Time: 12:11
 */

namespace Erichard\DmsBundle\Entity;

/**
 * Class AbstractMetadataLnk
 *
 * @package Erichard\DmsBundle\Entity
 */
abstract class AbstractMetadataLnk implements MetadataLnkInterface
{
    /**
     * id
     *
     * @var integer
     */
    protected $id;

    /**
     * metadata
     *
     * @var \Erichard\DmsBundle\Entity\DocumentMetadata
     */
    protected $metadata;

    /**
     * value
     *
     * @var mixed
     */
    protected $value;

    /**
     * DocumentMetadataLnk constructor.
     *
     * @param \Erichard\DmsBundle\Entity\DocumentMetadata $metadata
     */
    public function __construct(DocumentMetadata $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritDoc}
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
