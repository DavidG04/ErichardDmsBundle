<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 04/02/2016
 * Time: 12:12
 */

namespace Erichard\DmsBundle\Entity;

/**
 * Interface MetadataLnkInterface
 *
 * @package Erichard\DmsBundle\Entity
 */
interface MetadataLnkInterface
{
    /**
     * getId
     *
     * @return int
     */
    public function getId();

    /**
     * getValue
     *
     * @return mixed
     */
    public function getValue();

    /**
     * setValue
     *
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value);

    /**
     * getMetadata
     *
     * @return \Erichard\DmsBundle\Entity\DocumentMetadata
     */
    public function getMetadata();
}
