<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 22/01/2016
 * Time: 14:48
 */

namespace Erichard\DmsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface DocumentInterface
 *
 * @package Erichard\DmsBundle\Entity
 */
interface DocumentInterface
{
    /**
     * const TYPE_FILE
     */
    const TYPE_FILE = 'file';

    /**
     * getContent
     *
     * @return string
     */
    public function getContent();

    /**
     * getNode
     *
     * @return DocumentNodeInterface
     */
    public function getNode();

    /**
     * setNode
     *
     * @param DocumentNodeInterface $node
     *
     * @return $this
     */
    public function setNode(DocumentNodeInterface $node);

    /**
     * getPath
     *
     * @return mixed
     */
    public function getPath();

    /**
     * getFilename
     *
     * @return string
     */
    public function getFilename();

    /**
     * setFilename
     *
     * @param string $filename
     */
    public function setFilename($filename);

    /**
     * getMimeType
     *
     * @return string
     */
    public function getMimeType();

    /**
     * setMimeType
     *
     * @param string $mimeType
     *
     * @return $this
     */
    public function setMimeType($mimeType);

    /**
     * getType
     *
     * @return string
     */
    public function getType();

    /**
     * setType
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType($type);

    /**
     * getOriginalName
     *
     * @return string
     */
    public function getOriginalName();

    /**
     * setOriginalName
     *
     * @param string $originalName
     *
     * @return $this
     */
    public function setOriginalName($originalName);

    /**
     * isEnabled
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * setEnabled
     *
     * @param boolean $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled);

    /**
     * getAliases
     *
     * @return ArrayCollection
     */
    public function getAliases();

    /**
     * addAlias
     *
     * @param DocumentInterface $document
     *
     * @return $this
     */
    public function addAlias(DocumentInterface $document);

    /**
     * removeAlias
     *
     * @param DocumentInterface $document
     *
     * @return $this
     */
    public function removeAlias(DocumentInterface $document);

    /**
     * isLink
     *
     * @return bool
     */
    public function isLink();

    /**
     * getComputedFilename
     *
     * @return string
     */
    public function getComputedFilename();

    /**
     * getExtension
     *
     * @return mixed
     */
    public function getExtension();

    /**
     * setFilesize
     *
     * @param string $filesize
     *
     * @return $this
     */
    public function setFilesize($filesize);

    /**
     * getFilesize
     *
     * @return string
     */
    public function getFilesize();

    /**
     * getThumbnail
     *
     * @return string
     */
    public function getThumbnail();

    /**
     * setThumbnail
     *
     * @param string $thumbnail
     *
     * @return $this
     */
    public function setThumbnail($thumbnail);
}
