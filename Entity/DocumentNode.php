<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 22/01/2016
 * Time: 14:48
 */

namespace Erichard\DmsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class DocumentNode
 *
 * @package Erichard\DmsBundle\Entity
 */
class DocumentNode extends AbstractDms implements DocumentNodeInterface
{
    /**
     * Nodes
     *
     * @var ArrayCollection
     */
    protected $nodes;

    /**
     * Docuements
     *
     * @var ArrayCollection
     */
    protected $documents;

    /**
     * Depth
     *
     * @var int
     */
    protected $depth;

    /**
     * uniqReference for gedable
     *
     * @var string
     */
    protected $uniqRef;

    /**
     * is user Node
     *
     * @var boolean
     */
    protected $userNode;

    /**
     * DocumentNode constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->documents = new ArrayCollection();
        $this->nodes     = new ArrayCollection();
        $this->depth     = 1;
    }

    /**
     * {@inheritDoc}
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * {@inheritDoc}
     */
    public function addDocument(DocumentInterface $document)
    {
        if (!$this->documents->contains($document)) {
            $document->setParent($this);
            $this->documents->add($document);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removeDocument(DocumentInterface $document)
    {
        if ($this->documents->contains($document)) {
            $this->documents->removeElement($document);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getNodes()
    {
        return $this->nodes;
    }

    /**
     * {@inheritDoc}
     */
    public function addNode(DocumentNodeInterface $node)
    {
        if (!$this->nodes->contains($node)) {
            $node->setParent($this);
            $this->nodes->add($node);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removeNode(DocumentNodeInterface $node)
    {
        if ($this->nodes->contains($node)) {
            $this->nodes->removeElement($node);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        $path = null !== $this->parent ? $this->parent->getPath() : new ArrayCollection();
        $path->add($this);

        return $path;
    }

    /**
     * {@inheritDoc}
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * {@inheritDoc}
     */
    public function setUniqRef($uniqRef)
    {
        $this->uniqRef = $uniqRef;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getUniqRef()
    {
        return $this->uniqRef;
    }

    /**
     * {@inheritDoc}
     */
    public function setUserNode($userNode)
    {
        $this->userNode = $userNode;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isUserNode()
    {
        return $this->userNode;
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        $name = $this->name;
        if (!$name) {
            $name = 'new_node';
        }

        return $name;
    }
}
