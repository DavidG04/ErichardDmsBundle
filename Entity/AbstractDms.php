<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 04/02/2016
 * Time: 10:17
 */

namespace Erichard\DmsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class AbstractDms
 *
 * @package Erichard\DmsBundle\Entity
 */
abstract class AbstractDms implements DmsInterface
{
    /**
     * id
     *
     * @var integer
     */
    protected $id;

    /**
     * Name
     *
     * @var string
     */
    protected $name;

    /**
     * Slug
     *
     * @var string
     */
    protected $slug;

    /**
     * Metadatas
     *
     * @var ArrayCollection
     */
    protected $metadatas;

    /**
     * Updated At
     *
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * created  At
     *
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * Parent
     *
     * @var \Erichard\DmsBundle\Entity\DocumentNode
     */
    protected $parent;

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->metadatas = new ArrayCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritDoc}
     */
    public function setParent(DmsInterface $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * {@inheritDoc}
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadatas()
    {
        return $this->metadatas;
    }

    /**
     * {@inheritDoc}
     */
    public function addMetadata($metadata)
    {
        if (!$this->hasMetadata($metadata->getMetadata()->getName())) {
            $method = 'setDocument';
            if ($metadata instanceof DocumentNodeMetadataLnk) {
                $method = 'setNode';
            }
            $metadata->$method($this);
            $this->metadatas->add($metadata);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getMetadata($name)
    {
        foreach ($this->metadatas as $m) {
            if ($m->getMetadata()->getName() === $name) {
                return $m;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function removeMetadata($metadata)
    {
        if ($this->metadatas->contains($metadata)) {
            $this->metadatas->removeElement($metadata);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removeMetadataByName($metadataName)
    {
        if ($this->hasMetadata($metadataName)) {
            $this->removeMetadata($this->getMetadata($metadataName));
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasMetadata($name)
    {
        foreach ($this->metadatas as $m) {
            if ($m->getMetadata()->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function removeEmptyMetadatas($strict = false)
    {
        foreach ($this->metadatas as $m) {
            if (($strict && null === $m->getId()) || null === $m->getValue()) {
                $this->metadatas->removeElement($m);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
