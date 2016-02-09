<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 15:25
 */

namespace Erichard\DmsBundle\Event;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Erichard\DmsBundle\Entity\DocumentNodeInterface;
use Erichard\DmsBundle\Entity\DocumentNode;
use Erichard\DmsBundle\Service\NodeProvider;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class DmsNodeEvent
 *
 * @package Erichard\DmsBundle\Event
 */
class DmsNodeEvent extends Event
{
    /**
     * node
     *
     * @var DocumentNodeInterface
     */
    protected $node;

    /**
     * Node provider
     *
     * @var NodeProvider
     */
    protected $nodeProvider;

    /**
     * DmsNodeEvent constructor.
     *
     * @param DocumentNodeInterface|null $node
     */
    public function __construct($node = null)
    {
        $this->node = $node;
    }

    /**
     * set node provider
     *
     * @param NodeProvider $nodeProvider
     */
    public function setNodeProvider($nodeProvider)
    {
        $this->nodeProvider = $nodeProvider;
    }

    /**
     * get node
     *
     * @return DocumentNodeInterface
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * Post load
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof DocumentNode) {
            $entity->setUserNode($this->nodeProvider->isUserNode($entity));
        }
    }
}
