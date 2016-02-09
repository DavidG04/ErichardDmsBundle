<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 22/01/2016
 * Time: 14:48
 */

namespace Erichard\DmsBundle\Event;

use Erichard\DmsBundle\Entity\DocumentInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class DmsDocumentEvent
 *
 * @package Erichard\DmsBundle\Event
 */
class DmsDocumentEvent extends Event
{
    /**
     * document
     *
     * @var DocumentInterface
     */
    protected $document;

    /**
     * DmsDocumentEvent constructor.
     *
     * @param DocumentInterface $document
     */
    public function __construct(DocumentInterface $document)
    {
        $this->document = $document;
    }

    /**
     * get document
     *
     * @return DocumentInterface
     */
    public function getDocument()
    {
        return $this->document;
    }
}
