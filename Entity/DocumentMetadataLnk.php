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
 * Class DocumentMetadataLnk
 *
 * @package Erichard\DmsBundle\Entity
 */
class DocumentMetadataLnk extends AbstractMetadataLnk implements DocumentMetadataLnkInterface
{

    /**
     * document
     *
     * @var DocumentInterface
     */
    protected $document;

    /**
     * DocumentMetadataLnk constructor.
     *
     * @param \Erichard\DmsBundle\Entity\DocumentMetadata $metadata
     */
    public function __construct(DocumentMetadata $metadata)
    {
        parent::__construct($metadata);
    }

    /**
     * {@inheritDoc}
     */
    public function setDocument(DocumentInterface $document)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->document->getName().' - '.$this->metadata->getName();
    }
}
