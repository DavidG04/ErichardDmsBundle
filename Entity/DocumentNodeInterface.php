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
 * Interface DocumentNodeInterface
 *
 * @package Erichard\DmsBundle\Entity
 */
interface DocumentNodeInterface
{
    /**
     * get documents
     *
     * @return ArrayCollection
     */
    public function getDocuments();

    /**
     * add interface
     *
     * @param DocumentInterface $document
     *
     * @return $this
     */
    public function addDocument(DocumentInterface $document);

    /**
     * remove document
     *
     * @param DocumentInterface $document
     *
     * @return $this
     */
    public function removeDocument(DocumentInterface $document);

    /**
     * get nodes
     *
     * @return ArrayCollection
     */
    public function getNodes();
    /**
     * add node
     *
     * @param DocumentNodeInterface $node
     *
     * @return $this
     */
    public function addNode(DocumentNodeInterface $node);

    /**
     * remove node
     *
     * @param DocumentNodeInterface $node
     *
     * @return $this
     */
    public function removeNode(DocumentNodeInterface $node);

    /**
     * get path
     *
     * @return ArrayCollection
     */
    public function getPath();

    /**
     * set depth
     *
     * @param int $depth
     *
     * @return $this
     */
    public function setDepth($depth);

    /**
     * get depth
     *
     * @return int
     */
    public function getDepth();

    /**
     * Set uniq ref for gedable
     *
     * @param string $uniqRef
     *
     * @return $this
     */
    public function setUniqRef($uniqRef);

    /**
     * Get uniq ref for gedable
     *
     * @return string
     */
    public function getUniqRef();

    /**
     * Set user node
     *
     * @param boolean $userNode
     *
     * @return $this
     */
    public function setUserNode($userNode);

    /**
     * Get user node
     *
     * @return boolean
     */
    public function isUserNode();
}
