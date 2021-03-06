<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 22/01/2016
 * Time: 14:48
 */

namespace Erichard\DmsBundle\Import;

use Doctrine\ORM\EntityManager;
use Erichard\DmsBundle\Entity\DocumentNodeInterface;
use Erichard\DmsBundle\Entity\Document;
use Erichard\DmsBundle\Entity\DocumentNode;
use Symfony\Component\Finder\Finder;

/**
 * Class FilesystemImporter
 *
 * @package Erichard\DmsBundle\Import
 */
class FilesystemImporter
{
    /**
     * Entity Manager
     *
     * @var EntityManager
     */
    protected $emn;

    /**
     * Options
     *
     * @var array
     */
    protected $options;

    /**
     * FilesystemImporter constructor.
     *
     * @param EntityManager $emn
     * @param array         $options
     */
    public function __construct(EntityManager $emn, array $options = array())
    {
        $this->emn = $emn;
        $this->options = $options;
    }

    /**
     * import
     *
     * @param string                $sourceDir
     * @param DocumentNodeInterface $targetNode
     * @param array                 $excludes
     */
    public function import($sourceDir, DocumentNodeInterface $targetNode, array $excludes = array())
    {
        if (!is_dir($sourceDir)) {
            throw new \InvalidArgumentException(sprintf('The directory %s does not exist', $sourceDir));
        }

        $this->emn->persist($targetNode);
        $currentNode = array( 0 => $targetNode );

        $finder = new Finder();
        $finder->in($sourceDir);

        $files = $finder->getIterator();

        foreach ($files as $file) {
            foreach ($excludes as $exclude) {
                if (strpos($file->getRelativePathname(), $exclude) !== false) {
                    continue 2;
                }
            }

            $depth = $files->getDepth();
            if ($file->isDir()) {
                $currentNode[$depth+1] = $this->importDir($currentNode, $depth, $file, $targetNode);
            } elseif ($file->isFile()) {
                $this->importFile($currentNode, $depth, $file);
            }
        }

        $this->emn->flush();
    }

    /**
     * importFile
     *
     * @param array   $currentNode
     * @param integer $depth
     * @param mixed   $file
     */
    public function importFile($currentNode, $depth, $file)
    {
        $document = new Document($currentNode[$depth]);
        $document
            ->setName($file->getBaseName())
            ->setOriginalName($file->getBaseName())
            ->setFilename($file->getBaseName())
        ;

        if (isset($this->options['document_callback']) && is_callable($this->options['document_callback'])) {
            call_user_func_array($this->options['document_callback'], array($document));
        }

        $this->emn->persist($document);
        $this->emn->flush();

        $document->setFilename($document->getComputedFilename());

        $destFile = $this->options['storage_path'].'/'.$document->getFilename();

        if (!is_dir(dirname($destFile))) {
            mkdir(dirname($destFile), 0755, true);
        }

        if ($this->options['copy']) {
            copy($file->getRealPath(), $destFile);
        } else {
            rename($file->getRealPath(), $destFile);
        }

        $this->emn->persist($document);
        $this->emn->flush();
        $this->emn->detach($document);
    }
    /**
     * importDir
     *
     * @param array                 $currentNode
     * @param integer               $depth
     * @param mixed                 $file
     * @param DocumentNodeInterface $targetNode
     *
     * @return DocumentNode
     */
    public function importDir($currentNode, $depth, $file, $targetNode)
    {
        $node = new DocumentNode();
        $node
            ->setParent($currentNode[$depth])
            ->setName($file->getBaseName())
            ->setDepth($targetNode->getDepth() + $depth + 1)
        ;

        if (isset($this->options['node_callback']) && is_callable($this->options['node_callback'])) {
            call_user_func_array($this->options['node_callback'], array($node));
        }

        $this->emn->persist($node);

        return $node;
    }
}
