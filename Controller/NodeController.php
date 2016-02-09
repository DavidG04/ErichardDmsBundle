<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 22/01/2016
 * Time: 14:48
 */

namespace Erichard\DmsBundle\Controller;

use Erichard\DmsBundle\Entity\DocumentNode;
use Erichard\DmsBundle\Entity\DocumentNodeMetadataLnk;
use Erichard\DmsBundle\Event\DmsEvents;
use Erichard\DmsBundle\Event\DmsNodeEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class NodeController
 *
 * @package Erichard\DmsBundle\Controller
 */
class NodeController extends Controller
{
    /**
     * listAction
     *
     * @param string $node
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($node)
    {
        $documentNode = $this->findNodeOrThrowError($node);
        $this->get('dms.manager')->getNodeMetadatas($documentNode);

        foreach ($documentNode->getDocuments() as $document) {
            $filename = $this->container->getParameter('dms.storage.path').'/'.$document->getFilename();

            if (is_file($filename) && is_readable($filename)) {
                $document->setFilesize(filesize($filename));
            }
        }

        if ($this->get('event_dispatcher')->hasListeners(DmsEvents::NODE_ACCESS)) {
            $event = new DmsNodeEvent($documentNode);
            $this->get('event_dispatcher')->dispatch(DmsEvents::NODE_ACCESS, $event);
        }

        return $this->render('ErichardDmsBundle:Standard/Node:list.html.twig', array(
            'node'       => $documentNode,
            'mode'       => $this->get('request')->query->get('mode', 'table'),
            'show_nodes' => $this->container->getParameter('dms.workspace.show_nodes'),
        ));
    }

    /**
     * indexAction
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $dmsManager = $this->get('dms.manager');

        $response = $this->render('ErichardDmsBundle:Standard/Node:index.html.twig', array(
            'nodes' => $dmsManager->getRoots(),
        ));

        return $response;
    }

    /**
     * addAction
     *
     * @param string $node
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addAction($node)
    {
        $documentNode = $this->findNodeOrThrowError($node);

        $form = $this->createForm('dms_node');

        if ($this->get('event_dispatcher')->hasListeners(DmsEvents::NODE_ADD)) {
            $event = new DmsNodeEvent($documentNode);
            $this->get('event_dispatcher')->dispatch(DmsEvents::NODE_ADD, $event);
        }

        return $this->render('ErichardDmsBundle:Standard/Node:add.html.twig', array(
            'node' => $documentNode,
            'form' => $form->createView(),
        ));
    }

    /**
     * editAction
     *
     * @param string $node
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($node)
    {
        $documentNode = $this->findNodeOrThrowError($node);

        $this->get('dms.manager')->getNodeMetadatas($documentNode);

        $form = $this->createForm('dms_node', $documentNode);

        if ($this->get('event_dispatcher')->hasListeners(DmsEvents::NODE_EDIT)) {
            $event = new DmsNodeEvent($documentNode);
            $this->get('event_dispatcher')->dispatch(DmsEvents::NODE_EDIT, $event);
        }

        return $this->render('ErichardDmsBundle:Standard/Node:edit.html.twig', array(
            'node' => $documentNode,
            'form' => $form->createView(),
        ));
    }

    /**
     * createAction
     *
     * @param string $node
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction($node)
    {
        $emn = $this->get('doctrine')->getManager();
        $parentNode = $this->findNodeOrThrowError($node);

        $newNode    = new DocumentNode();
        $newNode->setParent($parentNode);
        $form       = $this->createForm('dms_node', $newNode);

        $form->bind($this->get('request'));

        if (!$form->isValid()) {
            $response = $this->render('ErichardDmsBundle:Standard/Node:add.html.twig', array(
                'node' => $parentNode,
                'form' => $form->createView(),
            ));
        } else {

            $metadatas = $form->get('metadatas')->getData();
            foreach ($metadatas as $metaName => $metaValue) {
                if (null !== $metaValue) {
                    $metadata = new DocumentNodeMetadataLnk(
                        $emn->getRepository('Erichard\DmsBundle\Entity\Metadata')->findOneByName($metaName)
                    );
                    $metadata->setValue($metaValue);
                    $newNode->addMetadata($metadata);
                    $emn->persist($metadata);
                }
            }

            $emn->persist($newNode);
            $emn->flush();

            if ($this->get('event_dispatcher')->hasListeners(DmsEvents::NODE_CREATE)) {
                $event = new DmsNodeEvent($newNode);
                $this->get('event_dispatcher')->dispatch(DmsEvents::NODE_CREATE, $event);
            }

            $this->get('session')->getFlashBag()->add('success', 'documentNode.add.successfully_created');

            $response = $this->redirect($this->generateUrl('erichard_dms_node_list', array('node' => $node)));
        }

        return $response;
    }

    /**
     * updateAction
     *
     * @param string $node
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateAction($node)
    {

        $documentNode = $this->findNodeOrThrowError($node);

        $form = $this->createForm('dms_node', $documentNode);
        $form->submit($this->get('request'));

        if (!$form->isValid()) {
            $response = $this->render('ErichardDmsBundle:Standard/Node:edit.html.twig', array(
                'node' => $documentNode,
                'form' => $form->createView(),
            ));
        } else {

            $emn = $this->get('doctrine')->getManager();

            $metadatas = $form->get('metadatas')->getData();
            foreach ($metadatas as $metaName => $metaValue) {

                if (empty($metaValue)) {
                    if ($metadata = $documentNode->getMetadata($metaName)) {
                        $documentNode->removeMetadataByName($metaName);
                        $emn->remove($metadata);
                    }
                    continue;
                }

                if (!$documentNode->hasMetadata($metaName)) {
                    $metadata = new DocumentNodeMetadataLnk(
                        $emn->getRepository('Erichard\DmsBundle\Entity\Metadata')->findOneByName($metaName)
                    );
                    $metadata->setValue($metaValue);
                    $documentNode->addMetadata($metadata);
                }

                $metadata = $documentNode->getMetadata($metaName);
                $metadata
                    ->setLocale($documentNode->getLocale())
                    ->setValue($metaValue)
                ;

                $emn->persist($documentNode->getMetadata($metaName));
            }

            $emn->persist($documentNode);
            $emn->flush();

            if ($this->get('event_dispatcher')->hasListeners(DmsEvents::NODE_UPDATE)) {
                $event = new DmsNodeEvent($documentNode);
                $this->get('event_dispatcher')->dispatch(DmsEvents::NODE_UPDATE, $event);
            }

            $this->get('session')->getFlashBag()->add('success', 'documentNode.edit.successfully_updated');

            $response = $this->redirect($this->generateUrl('erichard_dms_node_list', array('node' => $documentNode->getSlug())));
        }

        return $response;
    }

    /**
     * deleteAction
     *
     * @param string $node
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($node)
    {
        $documentNode = $this->findNodeOrThrowError($node);

        $redirectUrl = $this->generateUrl('erichard_dms_node_list', array('node' => $documentNode->getParent()->getSlug()));

        $emn = $this->get('doctrine')->getManager();
        $emn->refresh($documentNode);
        $emn->remove($documentNode);
        $emn->flush();

        if ($this->get('event_dispatcher')->hasListeners(DmsEvents::NODE_DELETE)) {
            $event = new DmsNodeEvent($documentNode);
            $this->get('event_dispatcher')->dispatch(DmsEvents::NODE_DELETE, $event);
        }

        $this->get('session')->getFlashBag()->add('success', 'documentNode.remove.successfully_removed');

        return $this->redirect($redirectUrl);
    }

    /**
     * removeAction
     *
     * @param string $node
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction($node)
    {
        $documentNode = $this->findNodeOrThrowError($node);

        return $this->render('ErichardDmsBundle:Standard/Node:remove.html.twig', array(
            'node' => $documentNode,
        ));
    }

    /**
     * findNodeOrThrowError
     *
     * @param string $nodeSlug
     *
     * @return DocumentNode
     */
    public function findNodeOrThrowError($nodeSlug)
    {
        try {
            $node = $this
                ->get('dms.manager')
                ->getNode($nodeSlug)
            ;
        } catch (AccessDeniedException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        }

        if (null === $node) {
            throw new NotFoundHttpException(sprintf('The node "%s" was not found', $nodeSlug));
        }

        return $node;
    }
}
