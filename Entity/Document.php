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
 * Class Document
 *
 * @package Erichard\DmsBundle\Entity
 */
class Document extends AbstractDms implements DocumentInterface
{
    /**
     * node
     *
     * @var DocumentNodeInterface
     */
    protected $node;

    /**
     * filename
     *
     * @var string
     */
    protected $filename;

    /**
     * thumbnail
     *
     * @var string
     */
    protected $thumbnail;

    /**
     * original name
     *
     * @var string
     */
    protected $originalName;

    /**
     * mimetype
     *
     * @var string
     */
    protected $mimeType;

    /**
     * type
     *
     * @var string
     */
    protected $type;

    /**
     * enabled
     *
     * @var bool
     */
    protected $enabled;

    /**
     * aliases
     *
     * @var ArrayCollection
     */
    protected $aliases;

    /**
     * filesize
     *
     * @var string
     */
    protected $filesize;

    /**
     * Document constructor.
     *
     * @param DocumentNodeInterface $node
     */
    public function __construct(DocumentNodeInterface $node)
    {
        parent::__construct();
        $this->node = $node;
        $this->type = DocumentInterface::TYPE_FILE;
        $this->enabled = true;
        $this->aliases = new ArrayCollection();
    }

    /**
     * clone
     */
    public function __clone()
    {
        $this->id = null;
        $this->slug = null;
        $this->createdAt = null;
        $this->updatedAt = null;
    }

    /**
     * {@inheritDoc}
     */
    public function getContent()
    {
        if (!is_readable($this->filename)) {
            throw new \RuntimeException(sprintf('The file "%s" is not readable.', $this->filename));
        }

        return file_get_contents($this->filename);
    }

    /**
     * {@inheritDoc}
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * {@inheritDoc}
     */
    public function setNode(DocumentNodeInterface $node)
    {
        $this->node = $node;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        $path = $this->node->getPath();
        $path->add($this->node);

        return $path;
    }

    /**
     * {@inheritDoc}
     */
    public function getFilename()
    {
        return $this->isLink()? $this->parent->getFilename() : $this->filename;
    }

    /**
     * {@inheritDoc}
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        if (null === $this->originalName) {
            $this->originalName = basename($filename);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * {@inheritDoc}
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * {@inheritDoc}
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritDoc}
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * {@inheritDoc}
     */
    public function addAlias(DocumentInterface $document)
    {
        if (!$this->aliases->contains($document)) {
            $document->setParent($this);
            $this->aliases->add($document);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function removeAlias(DocumentInterface $document)
    {
        if ($this->aliases->contains($document)) {
            $this->aliases->removeElement($document);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isLink()
    {
        return $this->parent !== null;
    }

    /**
     * {@inheritDoc}
     */
    public function getComputedFilename()
    {
        if (null === $this->id) {
            throw new \RuntimeException('You must persist the document before calling getComputedFilename().');
        }

        $reverseId = str_pad($this->id, 8, '0', STR_PAD_LEFT);
        $path = '';

        for ($i = 0; $i < 6; $i += 2) {
            $path .= substr($reverseId, $i, 2).DIRECTORY_SEPARATOR;
        }

        $extension = pathinfo($this->originalName, PATHINFO_EXTENSION);
        $extension = empty($extension)? 'noext' : $extension;

        $path .= $reverseId.'.'.$extension;

        return $path;
    }

    /**
     * {@inheritDoc}
     */
    public function getExtension()
    {
        return pathinfo($this->originalName, PATHINFO_EXTENSION);
    }

    /**
     * {@inheritDoc}
     */
    public function setFilesize($filesize)
    {
        $this->filesize = $filesize;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getFilesize()
    {
        return $this->filesize;
    }

    /**
     * {@inheritDoc}
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * {@inheritDoc}
     */
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;

        return $this;
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
            $name = 'new_document';
        }

        return $name;
    }
}
