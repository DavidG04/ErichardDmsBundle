<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 04/02/2016
 * Time: 10:18
 */

namespace Erichard\DmsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface DmsInterface
 *
 * @package Erichard\DmsBundle\Entity
 */
interface DmsInterface
{
    /**
     * set id
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * get id
     *
     * @return int
     */
    public function getId();

    /**
     * get parent
     *
     * @return DocumentNode
     */
    public function getParent();

    /**
     * set parent
     *
     * @param DmsInterface|null $parent
     *
     * @return $this
     */
    public function setParent(DmsInterface $parent = null);

    /**
     * get Name
     *
     * @return string
     */
    public function getName();

    /**
     * set name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * get slug
     *
     * @return string
     */
    public function getSlug();

    /**
     * set slug
     *
     * @param string $slug
     *
     * @return $this
     */
    public function setSlug($slug);

    /**
     * get metadatas
     *
     * @return ArrayCollection
     */
    public function getMetadatas();

    /**
     * add metadata
     *
     * @param DocumentNodeMetadataLnk|DocumentMetadataLnk $metadata
     *
     * @return $this
     */
    public function addMetadata($metadata);

    /**
     * get metadata
     *
     * @param string $name
     *
     * @return bool|DocumentNodeMetadataLnk|DocumentMetadataLnk|null
     */
    public function getMetadata($name);

    /**
     * remove metadata
     *
     * @param DocumentNodeMetadataLnk|DocumentMetadataLnk $metadata
     *
     * @return $this
     */
    public function removeMetadata($metadata);

    /**
     * remove metadata by name
     *
     * @param string $metadataName
     *
     * @return $this
     */
    public function removeMetadataByName($metadataName);

    /**
     * has metadata
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasMetadata($name);

    /**
     * remove metadata empty
     *
     * @param bool $strict
     */
    public function removeEmptyMetadatas($strict = false);

    /**
     * Sets createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * Returns createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * Sets updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(\DateTime $updatedAt);

    /**
     * Returns updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt();
}
