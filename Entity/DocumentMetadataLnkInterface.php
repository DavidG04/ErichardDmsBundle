<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 04/02/2016
 * Time: 12:18
 */

namespace Erichard\DmsBundle\Entity;

/**
 * Interface DocumentMetadataLnkInterface
 *
 * @package Erichard\DmsBundle\Entity
 */
interface DocumentMetadataLnkInterface
{
    /**
     * setDocument
     *
     * @param DocumentInterface $document
     *
     * @return $this
     */
    public function setDocument(DocumentInterface $document);
}
