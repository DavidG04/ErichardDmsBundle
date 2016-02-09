<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 22/01/2016
 * Time: 14:48
 */

namespace Erichard\DmsBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Erichard\DmsBundle\Entity\Document;
use Erichard\DmsBundle\Entity\DocumentInterface;
use Erichard\DmsBundle\Entity\DocumentMetadata;
use Erichard\DmsBundle\Entity\DocumentMetadataLnk;
use Erichard\DmsBundle\Entity\DocumentNode;
use Erichard\DmsBundle\Entity\DocumentNodeInterface;
use Erichard\DmsBundle\Entity\DocumentNodeMetadataLnk;
use Imagick;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\Metadata\MetadataBag;
use Imagine\Image\Palette\RGB;
use Imagine\Imagick\Image;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class DmsManager
 *
 * @package Erichard\DmsBundle\Service
 *
 * @SuppressWarnings("PMD")
 */
class DmsManager
{
    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Security context
     *
     * @var TokenStorageInterface
     */
    protected $securityContext;

    /**
     * MimeTypeManager
     *
     * @var MimeTypeManager
     */
    protected $mimeTypeManager;

    /**
     * Router
     *
     * @var RouterInterface
     */
    protected $router;

    /**
     * options
     *
     * @var array
     */
    protected $options;

    /**
     * Container
     *
     * @var Container
     */
    protected $container;

    /**
     * DmsManager constructor.
     *
     * @param Container $container
     * @param array     $options
     */
    public function __construct($container, array $options = array())
    {
        $this->container = $container;
        $this->registry = $this->container->get('doctrine');
        $this->securityContext = $this->container->get('security.authorization_checker');
        $this->mimeTypeManager = $this->container->get('dms.mime_type_manager');
        $this->router = $this->container->get('router');
        $this->options = $options;
    }

    /**
     * get roots
     *
     * @return \Erichard\DmsBundle\Entity\DocumentNode[]
     */
    public function getRoots()
    {
        $nodes = $this
            ->registry
            ->getRepository('Erichard\DmsBundle\Entity\DocumentNode')
            ->getRoots()
        ;

        foreach ($nodes as $idx => $node) {
            try {
                $this->prepareNode($node);
            } catch (AccessDeniedException $e) {
                unset($nodes[$idx]);
            }
        }

        return $nodes;
    }

    /**
     * get node by uniqId
     *
     * @param integer $uniqRef
     *
     * @return \Erichard\DmsBundle\Entity\DocumentNode
     */
    public function getNodeByUniqRef($uniqRef)
    {
        $documentNode = $this
            ->registry
            ->getRepository('Erichard\DmsBundle\Entity\DocumentNode')
            ->findOneByIdUniqRef($uniqRef)
        ;

        if (null !== $documentNode) {
            $this->prepareNode($documentNode);
        }

        return $documentNode;
    }

    /**
     * get node by id
     *
     * @param integer $nodeId
     *
     * @return \Erichard\DmsBundle\Entity\DocumentNode
     */
    public function getNodeById($nodeId)
    {
        $documentNode = $this
            ->registry
            ->getRepository('Erichard\DmsBundle\Entity\DocumentNode')
            ->findOneByIdWithChildren($nodeId)
        ;

        if (null !== $documentNode) {
            $this->prepareNode($documentNode);
        }

        return $documentNode;
    }

    /**
     * get node by slug
     *
     * @param string $slug
     *
     * @return \Erichard\DmsBundle\Entity\DocumentNode[]
     */
    public function getNodeBySlug($slug)
    {
        return $this->registry->findOneBySlugWithChildren($slug);
    }

    /**
     * manage node tree
     *
     * @param array                                        $nodeTree
     * @param \Erichard\DmsBundle\Entity\DocumentNode|null $parentNode
     */
    public function manageNodeTree($nodeTree, $parentNode = null)
    {
        if (!$parentNode) {
            $parentNode = $this->getRoots()[0];
        }

        foreach ($nodeTree as $uniqRef => $tree) {
            $node = $this->getNodeByUniqRef($uniqRef);
            if (null === $node) {
                $node = $this->newNode($uniqRef, $tree['name'], $parentNode);
            } else {
                $this->updateNode($node, $tree['name'], $parentNode);
            }
            if (null !== $tree['children']) {
                $this->manageNodeTree($tree['children'], $node);
            }
        }
    }

    /**
     * createDocument
     *
     * @param string                                  $filename
     * @param string                                  $token
     * @param \Erichard\DmsBundle\Entity\DocumentNode $currentNode
     * @param \Erichard\DmsBundle\Entity\Document     $document
     */
    public function createDocument($filename, $token, $currentNode, $document)
    {
        $currentNode->removeEmptyMetadatas();

        if (null === $document->getId()) {
            $document->setName($filename);
        }

        $document->setOriginalName($filename);
        $document->setFilename($token);
        $document->removeEmptyMetadatas();

        $emn = $this->registry->getManager();
        $emn->persist($document);
        $emn->flush();

        foreach ($document->getNode()->getDocuments() as $sibling) {
            $sibling->removeEmptyMetadatas();
        }

        $storageTmpPath = $this->container->getParameter('dms.storage.tmp_path');
        $storagePath    = $this->container->getParameter('dms.storage.path');

        $filesystem = $this->container->get('filesystem');

        $absTmpFilename = $storageTmpPath.'/'.$document->getFilename();
        $absFilename = $storagePath.'/'.$document->getComputedFilename();

        $finder = new Finder();
        $finder->files()
            ->in($storageTmpPath)
            ->name($document->getFilename());
        foreach ($finder as $file) {
            $this->addMetadatas($file, $document, 'document');
        }

        // Delete existing thumbnails
        if (is_dir($this->container->getParameter('dms.cache.path'))) {
            $finder = new Finder();
            $finder->files()
                ->in($this->container->getParameter('dms.cache.path'))
                ->name("{$document->getSlug()}.png")
            ;
            foreach ($finder as $file) {
                $filesystem->remove($file);
            }
        }

        // overwrite file
        if (is_file($absFilename)) {
            unlink($absFilename);
        } elseif (!$filesystem->exists(dirname($absFilename))) {
            $filesystem->mkdir(dirname($absFilename));
        }

        $filesystem->rename($absTmpFilename, $absFilename);
        $document->setFilename($document->getComputedFilename());


        $emn->persist($document);
        $emn->flush();
    }

    /**
     * addMetadatas
     *
     * @param \Symfony\Component\Finder\SplFileInfo $file
     * @param mixed                                 $object
     * @param string                                $scope
     */
    public function addMetadatas($file, $object, $scope)
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $now = new \DateTime('now');
        $metadataArray = array(
            'FILESIZE' => $this->getHumanFileSize($file->getSize()),
            'AUTHOR' => $user->getLastname().' '.$user->getFirstName(),
            'VERSION' => 'v.'.$now->format('YmdHis'),
            'DESCRIPTION' => '',
        );
        $metadatas = $this->registry->getManager()->getRepository('Erichard\DmsBundle\Entity\DocumentMetadata')->findByScope(array($scope, 'both'));
        foreach ($metadatas as $metadata) {
            if (array_key_exists($metadata->getName(), $metadataArray)) {
                $metadataLnk = $scope == 'document' ? new DocumentMetadataLnk($metadata) : new DocumentNodeMetadataLnk($metadata);
                $metadataLnk->setValue($metadataArray[$metadata->getName()]);
                $this->registry->getManager()->persist($metadataLnk);
                $object->addMetadata($metadataLnk);
            }
        }
    }

    /**
     * Human filesize
     *
     * @param integer $size
     *
     * @return string
     */
    public function getHumanFileSize($size)
    {
        if ($size >= 1073741824) {
            $fileSize = round($size / 1024 / 1024 / 1024, 1).'GB';
        } elseif ($size >= 1048576) {
            $fileSize = round($size / 1024 / 1024, 1).'MB';
        } elseif ($size >= 1024) {
            $fileSize = round($size / 1024, 1).'KB';
        } else {
            $fileSize = $size.' bytes';
        }

        return $fileSize;
    }

    /**
     * new Node
     *
     * @param string                                  $uniqRef
     * @param string                                  $name
     * @param \Erichard\DmsBundle\Entity\DocumentNode $parentNode
     *
     * @return \Erichard\DmsBundle\Entity\DocumentNode
     */
    public function newNode($uniqRef, $name, $parentNode)
    {
        $node = new DocumentNode();
        $node
            ->setName($name)
            ->setUniqRef($uniqRef)
            ->setParent($parentNode)
            ->setDepth($parentNode->getDepth()+1);
        $this->registry->getManager()->persist($node);
        $this->registry->getManager()->flush();

        return $node;
    }

    /**
     * new Node
     *
     * @param \Erichard\DmsBundle\Entity\DocumentNode $node
     * @param string                                  $name
     * @param \Erichard\DmsBundle\Entity\DocumentNode $parentNode
     *
     * @return \Erichard\DmsBundle\Entity\DocumentNode
     */
    public function updateNode($node, $name, $parentNode)
    {
        $node
            ->setName($name)
            ->setParent($parentNode)
            ->setDepth($parentNode->getDepth()+1);
        $this->registry->getManager()->flush();

        return $node;
    }

    /**
     * move node
     *
     * @param \Erichard\DmsBundle\Entity\DocumentNode $node
     * @param \Erichard\DmsBundle\Entity\DocumentNode $parentNode
     *
     * @return \Erichard\DmsBundle\Entity\DocumentNode
     */
    public function moveNode($node, $parentNode)
    {
        $node->setParent($parentNode);
        $this->registry->getManager()->persist($node);
        $this->registry->getManager()->flush();

        return $node;
    }

    /**
     * get node
     *
     * @param string $nodeSlug
     *
     * @return \Erichard\DmsBundle\Entity\DocumentNode
     */
    public function getNode($nodeSlug)
    {
        $registry = $this->registry
            ->getRepository('Erichard\DmsBundle\Entity\DocumentNode')
        ;

        $sortField = $registry->findSortField($nodeSlug);

        if (null !== $sortField && is_array($sortField)) {
            list($sortByField, $sortByOrder) = explode(',', $sortField);

            $documentNode = $registry->findOneBySlugWithChildren($nodeSlug, $sortByField, $sortByOrder);
        } else {
            $documentNode = $registry->findOneBySlugWithChildren($nodeSlug);
        }

        if (null !== $documentNode) {
            $this->prepareNode($documentNode);
        }

        return $documentNode;
    }

    /**
     * get Document
     *
     * @param string $documentSlug
     * @param string $nodeSlug
     *
     * @return \Erichard\DmsBundle\Entity\Document
     */
    public function getDocument($documentSlug, $nodeSlug)
    {
        $document = $this
            ->registry
            ->getRepository('Erichard\DmsBundle\Entity\Document')
            ->findOneBySlugAndNode($documentSlug, $nodeSlug)
        ;

        if (null !== $document) {
            $this->prepareDocument($document);
        }

        return $document;
    }

    /**
     * find nodes by metadatas
     *
     * @param \Erichard\DmsBundle\Entity\DocumentNode|null $node
     * @param array                                        $metatadas
     * @param array                                        $sortBy
     *
     * @return \Erichard\DmsBundle\Entity\DocumentNode[]
     */
    public function findNodesByMetadatas($node = null, array $metatadas = array(), array $sortBy = array())
    {
        $documentNodes = $this
            ->registry
            ->getRepository('Erichard\DmsBundle\Entity\DocumentNode')
            ->findByMetadatas($node, $metatadas, $sortBy)
        ;

        return array_filter($documentNodes, function (DocumentNodeInterface $documentNode) {
            return $this->isViewable($documentNode);
        });
    }

    /**
     * find documents by metadatas
     *
     * @param \Erichard\DmsBundle\Entity\DocumentNode|null $node
     * @param array                                        $metatadas
     * @param array                                        $sortBy
     *
     * @return \Erichard\DmsBundle\Entity\Document[]
     */
    public function findDocumentsByMetadatas($node = null, array $metatadas = array(), array $sortBy = array())
    {
        $documents = $this
            ->registry
            ->getRepository('Erichard\DmsBundle\Entity\Document')
            ->findByMetadatas($node, $metatadas, $sortBy)
        ;

        return array_filter($documents, function (DocumentInterface $document) {
            return $this->isViewable($document);
        });
    }

    /**
     * prepareNode
     *
     * @param DocumentNodeInterface $documentNode
     *
     * @return DocumentNodeInterface
     */
    public function prepareNode(DocumentNodeInterface $documentNode)
    {
        foreach ($documentNode->getDocuments() as $document) {
            $this->prepareDocument($document);
        }

        return $documentNode;
    }

    /**
     * Prepare document
     *
     * @param DocumentInterface $document
     *
     * @return DocumentInterface
     */
    public function prepareDocument(DocumentInterface $document)
    {
        $mimetype = $this
            ->mimeTypeManager
            ->getMimeType($this->options['storage_path'].DIRECTORY_SEPARATOR.$document->getFilename())
        ;

        $document->setMimeType($mimetype);

        return $document;
    }

    /**
     * getNodeMetadatas
     *
     * @param DocumentNodeInterface $node
     */
    public function getNodeMetadatas(DocumentNodeInterface $node)
    {
        $metadatas = $this
            ->registry
            ->getRepository('Erichard\DmsBundle\Entity\DocumentMetadata')
            ->findByScope(array('node', 'both'))
        ;

        foreach ($metadatas as $meta) {
            if (!$node->hasMetadata($meta->getName())) {
                $metadata = new DocumentNodeMetadataLnk($meta);
                $node->addMetadata($metadata);
            }
        }
    }

    /**
     * getDocumentMetadatas
     *
     * @param DocumentInterface $document
     */
    public function getDocumentMetadatas(DocumentInterface $document)
    {
        // Set all metadata on the document
        $metadatas = $this
            ->registry
            ->getRepository('Erichard\DmsBundle\Entity\DocumentMetadata')
            ->findByScope(array('document', 'both'))
        ;

        foreach ($metadatas as $meta) {
            if (!$document->hasMetadata($meta->getName())) {
                $metadata = new DocumentMetadata($meta);
                $document->addMetadata($metadata);
            }
        }
    }

    /**
     * generateThumbnail
     *
     * @param DocumentInterface $document
     * @param array             $dimension
     *
     * @return array|null|string
     */
    public function generateThumbnail(DocumentInterface $document, $dimension)
    {
        list($width, $height) = array_map('intval', explode('x', $dimension));
        $cacheFile = sprintf(
            '%s/%s/%s/%s.png',
            $this->options['cache_path'],
            $dimension,
            $document->getNode()->getSlug(),
            $document->getSlug()
        );

        if (!is_file($cacheFile)) {
            $size = new Box($width, $height);
            $mode = ImageInterface::THUMBNAIL_INSET;
            $absPath = $this->options['storage_path'].DIRECTORY_SEPARATOR.(null !== $document->getThumbnail() ? $document->getThumbnail() : $document->getFilename());
            if (!is_file($absPath) || filesize($absPath) >= 100000000) {
                $cacheFile = $this->mimeTypeManager->getMimetypeImage($absPath, max([$width, $height]));
            } else {
                try {
                    if (pathinfo($absPath, PATHINFO_EXTENSION) === 'pdf') {
                        $absPath .= '[0]';
                    }
                    $imagick = new \Imagick($absPath);
                    $imagick->setCompression(Imagick::COMPRESSION_LZW);
                    $imagick->setResolution(72, 72);
                    $imagick->setCompressionQuality(90);
                    $image = new Image($imagick, new RGB(), new MetadataBag());

                    if (!is_dir(dirname($cacheFile))) {
                        mkdir(dirname($cacheFile), 0777, true);
                    }
                    $image
                        ->thumbnail($size, $mode)
                        ->save($cacheFile, array('quality' => 90))
                    ;
                } catch (\Exception $e) {
                    $cacheFile = $this->mimeTypeManager->getMimetypeImage(
                        $this->options['storage_path'].DIRECTORY_SEPARATOR.$document->getFilename(),
                        max([$width, $height])
                    );
                }
            }
        }

        return $cacheFile;
    }

    /**
     * deleteDocument
     *
     * @param DocumentNode $document
     */
    public function deleteDocument($document)
    {
        $parts = preg_split('~/(?=[^/]*$)~', $document->getFilename());
        $finder = new Finder();
        $finder->files()
            ->in($this->container->getParameter('dms.storage.path').'/'.$parts[0])
            ->name($parts[1]);
        foreach ($finder as $file) {
            $this->container->get('filesystem')->remove($file);
        }
    }

    /**
     * isViewable
     *
     * @param mixed $entity
     *
     * @return bool
     */
    public function isViewable($entity)
    {
        $editPermission = $entity instanceof Document ? 'DOCUMENT_EDIT' : 'NODE_EDIT';

        return $this->securityContext->isGranted('VIEW', $entity) &&
            ($this->securityContext->isGranted($editPermission, $entity) || $entity->isEnabled())
        ;
    }
}
