<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 16:07
 */

namespace Erichard\DmsBundle\Service;

use Erichard\DmsBundle\Entity\DocumentNode;
use Erichard\DmsBundle\Event\DmsNodeEvent;
use Symfony\Component\DependencyInjection\Container;
use Erichard\DmsBundle\Event\DmsEvents;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class NodeProvider
 *
 * @package Erichard\DmsBundle\Service
 */
class NodeProvider
{
    /**
     * Container
     *
     * @var Container
     */
    protected $container;

    /**
     * constructor
     *
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * getDocumentNode
     *
     * @param string  $node
     * @param boolean $uniqRef
     *
     * @return DocumentNode
     *
     * @throws AccessDeniedHttpException
     * @throws NotFoundHttpException
     */
    public function getDocumentNode($node, $uniqRef = false)
    {
        $documentNode = null;

        if ($uniqRef) {
            $documentNode = $this->container->get('dms.manager')->getNodeByUniqRef($node);
        } else {
            $documentNode = $this->findNodeOrThrowError($node);
        }

        $this->container->get('dms.manager')->getNodeMetadatas($documentNode);

        foreach ($documentNode->getDocuments() as $document) {
            $filename = $this->container->getParameter('dms.storage.path').'/'.$document->getFilename();

            if (is_file($filename) && is_readable($filename)) {
                $document->setFilesize(filesize($filename));
            }
        }

        if ($this->container->get('event_dispatcher')->hasListeners(DmsEvents::NODE_ACCESS)) {
            $event = new DmsNodeEvent($documentNode);
            $this->container->get('event_dispatcher')->dispatch(DmsEvents::NODE_ACCESS, $event);
        }

        return $documentNode;
    }

    /**
     * findNodeOrThrowError
     *
     * @param string $nodeSlug
     *
     * @return mixed
     *
     * @throws AccessDeniedHttpException
     */
    public function findNodeOrThrowError($nodeSlug)
    {
        try {
            $node = $this->container
                ->get('dms.manager')
                ->getNode($nodeSlug)
            ;
        } catch (AccessDeniedException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        }

        if (null === $node) {
            $node = $this->getRootNode($nodeSlug);
        }

        return $node;
    }

    /**
     * findDocumentOrThrowError
     *
     * @param string $documentSlug
     * @param string $nodeSlug
     *
     * @return mixed
     *
     * @throws AccessDeniedHttpException
     */
    public function findDocumentOrThrowError($documentSlug, $nodeSlug)
    {
        try {
            $document = $this->container
                ->get('dms.manager')
                ->getDocument($documentSlug, $nodeSlug)
            ;
        } catch (AccessDeniedException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        }

        if (null === $document) {
            throw new NotFoundHttpException(sprintf('The document "%s" was not found', $documentSlug));
        }

        return $document;
    }

    /**
     * getChroot
     *
     * @return string
     */
    public function getChroot()
    {
        $chroot = $this->container->get('security.token_storage')->getToken()->getUser()->getGedUniqRef();
        $pid = $this->container->get('request_stack')->getCurrentRequest()->get('pid');
        if ($pid) {
            try {
                $pcode = $this->container->get('request_stack')->getCurrentRequest()->get('pcode');
                $padmin = $this->container->get($pcode);
                $object = $padmin->getModelManager()->find($padmin->getClass(), $pid);
                $chroot = $object->getGedUniqRef();
            } catch (ServiceNotFoundException $e) {
                throw new AccessDeniedHttpException('Acces denied without valid pcode parameter');
            }
        }

        return $chroot;
    }

    /**
     * get root node
     *
     * @return \Erichard\DmsBundle\Entity\DocumentNode
     */
    public function getRootNode()
    {
        $dmsManager = $this->container->get('dms.manager');
        $root = $dmsManager->getRoots()[0];
        $rootNode = $this->getDocumentNode($this->getChroot(), true);
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if ($user->hasRole($this->container->getParameter('dms.permission.super_admin_role'))) {
            $rootNode = $root;
        }

        return $rootNode;
    }

    /**
     * get current node
     *
     * @return \Erichard\DmsBundle\Entity\DocumentNode
     */
    public function getCurrentNode()
    {
        $currentNodeS = $this->container->get('request_stack')->getCurrentRequest()->get('node');
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $currentNode = $this->getRootNode();
        if ($currentNodeS) {
            $pathNode = $this->getDocumentNode($currentNodeS);
            if ($user->hasRole($this->container->getParameter('dms.permission.super_admin_role')) || $pathNode->getPath()->contains($currentNode)) {
                $currentNode = $pathNode;
            }
        }

        return $currentNode;
    }

    /**
     * isuserNode
     *
     * @param \Erichard\DmsBundle\Entity\DocumentNode $node
     *
     * @return boolean
     */
    public function isUserNode($node)
    {
        $return = false;
        if (preg_match('/'.$this->container->getParameter('dms.workspace.user_pattern').'/', $node->getUniqRef())) {
            $return = true;
        }

        return $return;
    }
}
