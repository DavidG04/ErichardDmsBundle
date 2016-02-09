<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 04/02/2016
 * Time: 12:16
 */

namespace Erichard\DmsBundle\Entity;

/**
 * Interface DocumentNodeMetadataLnkInterface
 *
 * @package Erichard\DmsBundle\Entity
 */
interface DocumentNodeMetadataLnkInterface
{
    /**
     * set node
     *
     * @param DocumentNodeInterface $node
     *
     * @return $this
     */
    public function setNode(DocumentNodeInterface $node);
}
