<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 27/01/2016
 * Time: 09:22
 */

namespace Erichard\DmsBundle\Event;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Erichard\DmsBundle\Gedable\GedableInterface;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class GedableListener
 *
 * @package Erichard\DmsBundle\Event
 */
class GedableListener
{
    /**
     * Container
     *
     * @var Container
     */
    protected $container;

    /**
     * GedableListener constructor.
     *
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * init object dms
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof GedableInterface) {
            $this->manageNodeTree($entity->getGedTree());
        }
    }

    /**
     * Control object dms
     *
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof GedableInterface) {
            $this->manageNodeTree($entity->getGedTree());
        }
    }

    /**
     * manage node tree
     *
     * @param array $tree
     */
    protected function manageNodeTree($tree)
    {
        $this->container->get('dms.manager')->manageNodeTree($tree);
    }
}
