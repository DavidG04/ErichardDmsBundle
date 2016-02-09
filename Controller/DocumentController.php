<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 22/01/2016
 * Time: 14:48
 */

namespace Erichard\DmsBundle\Controller;

use Erichard\DmsBundle\Entity\Document;
use Erichard\DmsBundle\Entity\DocumentMetadata;
use Erichard\DmsBundle\Event\DmsDocumentEvent;
use Erichard\DmsBundle\Event\DmsEvents;
use Erichard\DmsBundle\Form\DocumentType;
use Erichard\DmsBundle\Response\FileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class DocumentController
 *
 * @package Erichard\DmsBundle\Controller
 */
class DocumentController extends Controller
{
    /**
     * addAction
     *
     * @param string      $node
     * @param null|string $document
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAction($node, $document = null)
    {
        $documentNode = $this->findNodeOrThrowError($node);
        $request      = $this->get('request');
        if (null !== $document) {
            $document = $this->findDocumentOrThrowError($document, $node);
            $firstEdition = false;
        } else {
            $document = new Document($documentNode);
            $firstEdition = true;
        }
        if ($request->isMethod('GET')) {
            $params = array(
                'node' => $documentNode,
            );
            if (null !== $document->getId()) {
                $params['document'] = $document;
            }

            return $this->render('ErichardDmsBundle:Standard/Document:add.html.twig', $params);
        } else {
            $filename = $request->request->get('filename');
            $documentNode->removeEmptyMetadatas();
            if (null === $document->getId()) {
                $document->setName($filename);
            }
            $document->setOriginalName($filename);
            $document->setFilename($request->request->get('token'));
            $document->removeEmptyMetadatas();
            foreach ($document->getNode()->getDocuments() as $sibling) {
                $sibling->removeEmptyMetadatas();
            }
            $emn = $this->get('doctrine')->getManager();
            $emn->persist($document);
            $emn->flush();
            $storageTmpPath = $this->container->getParameter('dms.storage.tmp_path');
            $storagePath    = $this->container->getParameter('dms.storage.path');
            $filesystem = $this->get('filesystem');
            $absTmpFilename = $storageTmpPath.'/'.$document->getFilename();
            $absFilename = $storagePath.'/'.$document->getComputedFilename();
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
            if ($this->get('event_dispatcher')->hasListeners(DmsEvents::DOCUMENT_ADD)) {
                $event = new DmsDocumentEvent($document);
                $this->get('event_dispatcher')->dispatch(DmsEvents::DOCUMENT_ADD, $event);
            }

            return $this->redirect(
                $this->get('router')->generate(
                    'erichard_dms_edit_document',
                    array(
                        'document' => $document->getSlug(),
                        'node' => $documentNode->getSlug(),
                        'first' => $firstEdition,
                    )
                )
            );
        }
    }

    /**
     * uploadAction
     *
     * @return JsonResponse
     */
    public function uploadAction()
    {
        $request = $this->get('request');

        return $this->container->get('dms.document_upload_manager')->uploadFiles($request);
    }

    /**
     * showAction
     *
     * @param string $node
     * @param string $document
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction($node, $document)
    {
        $document = $this->findDocumentOrThrowError($document, $node);
        $this->get('dms.manager')->getDocumentMetadatas($document);

        return $this->render('ErichardDmsBundle:Standard/Document:show.html.twig', array(
            'node'     => $document->getNode(),
            'document' => $document,
        ));
    }

    /**
     * editAction
     *
     * @param string $node
     * @param string $document
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($node, $document)
    {
        $document = $this->findDocumentOrThrowError($document, $node);

        $this->get('dms.manager')->getDocumentMetadatas($document);
        $form = $this->createForm(new DocumentType($this->get('doctrine'), $this->get('dms.node_provider')), $document);

        if ($this->get('event_dispatcher')->hasListeners(DmsEvents::DOCUMENT_EDIT)) {
            $event = new DmsDocumentEvent($document);
            $this->get('event_dispatcher')->dispatch(DmsEvents::DOCUMENT_EDIT, $event);
        }

        return $this->render('ErichardDmsBundle:Standard/Document:edit.html.twig', array(
            'node'         => $document->getNode(),
            'document'     => $document,
            'form'         => $form->createView(),
            'firstEdition' => $this->get('request')->get('first', false),
        ));
    }

    /**
     * updateAction
     *
     * @param string $node
     * @param string $document
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateAction($node, $document)
    {
        $document = $this->findDocumentOrThrowError($document, $node);
        $this->get('dms.manager')->getDocumentMetadatas($document);

        $form = $this->createForm(new DocumentType($this->get('doctrine'), $this->get('dms.node_provider')), $document);
        $form->submit($this->get('request'));

        if (!$form->isValid()) {
            $response = $this->render('ErichardDmsBundle:Standard/Node:edit.html.twig', array(
                'node'     => $document->getNode(),
                'document' => $document,
                'form'     => $form->createView(),
            ));
        } else {
            $emn = $this->get('doctrine')->getManager();

            $metadatas = $form->get('metadatas')->getData();
            foreach ($metadatas as $metaName => $metaValue) {

                if (null === $metaValue) {
                    if ($metadata = $document->getMetadata($metaName)) {
                        $document->removeMetadataByName($metaName);
                        $emn->remove($metadata);
                    }
                    continue;
                }

                if (!$document->hasMetadata($metaName)) {
                    $metadata = new DocumentMetadata(
                        $emn->getRepository('Erichard\DmsBundle\Entity\DocumentMetadata')->findOneByName($metaName)
                    );
                    $metadata->setValue($metaName);
                    $document->addMetadata($metadata);
                }

                $metadata = $document->getMetadata($metaName);
                $metadata
                    ->setValue($metaValue)
                ;

                $emn->persist($document->getMetadata($metaName));
            }

            $uploadedFile = $form->get('thumbnail')->getData();
            if (null !== $uploadedFile) {
                $dirname = dirname($document->getFilename());
                $absDirName = $this->container->getParameter('dms.storage.path').DIRECTORY_SEPARATOR.$dirname;
                $filename = 'thumb_'.basename($document->getFilename());
                $uploadedFile->move($absDirName, $filename);
                $document->setThumbnail($dirname.DIRECTORY_SEPARATOR.$filename);
                $document->setUpdatedAt(new \DateTime());
            }

            // Remove document's thumbnails
            if (is_dir($this->container->getParameter('dms.cache.path'))) {
                $filesystem = $this->get('filesystem');
                $finder = new Finder();
                $finder->files()
                    ->in($this->container->getParameter('dms.cache.path'))
                    ->name("{$document->getSlug()}.png")
                ;

                foreach ($finder as $file) {
                    $filesystem->remove($file);
                }
            }

            $emn->persist($document);
            $emn->flush();

            if ($this->get('event_dispatcher')->hasListeners(DmsEvents::DOCUMENT_UPDATE)) {
                $event = new DmsDocumentEvent($document);
                $this->get('event_dispatcher')->dispatch(DmsEvents::DOCUMENT_UPDATE, $event);
            }

            $this->get('session')->getFlashBag()->add('success', 'document.edit.successfully_updated');

            $response = $this->redirect($this->generateUrl('erichard_dms_node_list', array('node' => $document->getNode()->getSlug())));
        }

        return $response;
    }

    /**
     * previewAction
     *
     * @param string $dimension
     * @param string $document
     * @param string $node
     *
     * @return FileResponse
     */
    public function previewAction($dimension, $document, $node)
    {
        $document = $this->findDocumentOrThrowError($document, $node);

        $thumbnailFile = $this->get('dms.manager')->generateThumbnail($document, $dimension);

        $expireDate = new \DateTime();
        $expireDate->modify('+10 years');

        $response = new FileResponse();
        $response->setFilename($thumbnailFile);
        $response->headers->set('Content-Type', 'image/png');

        $response->setPublic();
        $response->setExpires($expireDate);

        return $response;
    }

    /**
     * downloadAction
     *
     * @param string $document
     * @param string $node
     *
     * @return FileResponse
     */
    public function downloadAction($document, $node)
    {
        $document = $this->findDocumentOrThrowError($document, $node);

        $absPath = $this->container->getParameter('dms.storage.path').DIRECTORY_SEPARATOR.$document->getFilename();

        $response = new FileResponse();
        $response->setFilename($absPath);

        $response->headers->set('Cache-Control', 'public');
        $response->headers->set('Content-Type', $document->getMimeType());

        $contentDisposition = "filename*=UTF-8''".rawurlencode($document->getSlug().'.'.$document->getExtension());
        $response->headers->set('Content-Disposition', 'attachment; '.$contentDisposition);

        if ($this->get('event_dispatcher')->hasListeners(DmsEvents::DOCUMENT_DOWNLOAD)) {
            $event = new DmsDocumentEvent($document);
            $this->get('event_dispatcher')->dispatch(DmsEvents::DOCUMENT_DOWNLOAD, $event);
        }

        return $response;
    }

    /**
     * deleteAction
     *
     * @param string $document
     * @param string $node
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($document, $node)
    {
        $document = $this->findDocumentOrThrowError($document, $node);

        $parentListUrl = $this->generateUrl('erichard_dms_node_list', array(
            'node' => $document->getNode()->getSlug(),
        ));

        $backUrl = $this->get('request')->request->get('back', $parentListUrl);

        $emn = $this->get('doctrine')->getManager();
        $emn->remove($document);
        $emn->flush();

        if ($this->get('event_dispatcher')->hasListeners(DmsEvents::DOCUMENT_DELETE)) {
            $event = new DmsDocumentEvent($document);
            $this->get('event_dispatcher')->dispatch(DmsEvents::DOCUMENT_DELETE, $event);
        }

        $this->get('session')->getFlashBag()->add('success', 'document.remove.successfully_removed');

        return $this->redirect($backUrl);
    }

    /**
     * removeAction
     *
     * @param string $document
     * @param string $node
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction($document, $node)
    {
        $document = $this->findDocumentOrThrowError($document, $node);

        return $this->render('ErichardDmsBundle:Standard/Document:remove.html.twig', array(
            'document' => $document,
            'node'     => $document->getNode(),
            'backUrl'  => $this->get('request')->query->get('back'),
        ));
    }

    /**
     * linkAction
     *
     * @param string $document
     * @param string $node
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function linkAction($document, $node)
    {
        $request    = $this->get('request');
        $dmsManager = $this->get('dms.manager');
        $documentSlug = $document;
        $nodeSlug = $node;
        $document = $this->findDocumentOrThrowError($documentSlug, $nodeSlug);

        if ($request->isMethod('POST')) {
            $nodeId = $request->request->getInt('linkTo');
            $targetNode = $dmsManager->getNodeById($nodeId);

            if (!$this->get('security.authorization_checker')->isGranted('DOCUMENT_ADD', $targetNode)) {
                throw new AccessDeniedHttpException("You are not allowed to access this document");
            }

            $link = clone $document;

            $emn = $this->get('doctrine')->getManager();

            $document->addAlias($link);
            $link->setNode($targetNode);
            $link->removeEmptyMetadatas();
            $link->getNode()->removeEmptyMetadatas();
            foreach ($link->getNode()->getDocuments() as $document) {
                $document->removeEmptyMetadatas();
            }

            $emn->persist($link);
            $emn->flush();

            if ($this->get('event_dispatcher')->hasListeners(DmsEvents::DOCUMENT_LINK)) {
                $this->get('event_dispatcher')->dispatch(DmsEvents::DOCUMENT_LINK, $link);
            }

            return $this->redirect($this->generateUrl('erichard_dms_link_document', array(
                'node' => $nodeSlug,
                'document' => $documentSlug,
            )));
        }

        $targetNodeSlug = $request->query->get('target');

        if (null !== $targetNodeSlug) {
            $target = $dmsManager->getNode($targetNodeSlug);
            foreach ($target->getNodes() as $targetSubNode) {
                if (!$this->get('security.authorization_checker')->isGranted('DOCUMENT_ADD', $targetSubNode)) {
                    $target->removeNode($targetSubNode);
                }
            }
        } else {
            $target = null;
        }

        return $this->render('ErichardDmsBundle:Standard/Document:link.html.twig', array(
            'document'      => $document,
            'node'          => $document->getNode(),
            'target'        => $target,
        ));
    }

    /**
     * findDocumentOrThrowError
     *
     * @param string $documentSlug
     * @param string $nodeSlug
     *
     * @return Document
     */
    public function findDocumentOrThrowError($documentSlug, $nodeSlug)
    {
        try {
            $document = $this
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
     * findNodeOrThrowError
     *
     * @param string $nodeSlug
     *
     * @return \Erichard\DmsBundle\Entity\DocumentNode
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

    /**
     * removeThumbnailAction
     *
     * @param string $document
     * @param string $node
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeThumbnailAction($document, $node)
    {
        $emn = $this->get('doctrine')->getManager();
        $documentSlug = $document;
        $nodeSlug = $node;

        $document = $this->findDocumentOrThrowError($document, $node);
        $document->setThumbnail(null);
        $emn->persist($document);
        $emn->flush();

        $filesystem = $this->get('filesystem');

        // Remove document's thumbnails
        $finder = new Finder();
        $finder->files()
            ->in($this->get('request')->server->get('DOCUMENT_ROOT'))
            ->name("{$document->getSlug()}.png");

        foreach ($finder as $file) {
            $filesystem->remove($file);
        }

        if ($this->get('event_dispatcher')->hasListeners(DmsEvents::DOCUMENT_REMOVE_THUMBNAIL)) {
            $this->get('event_dispatcher')->dispatch(DmsEvents::DOCUMENT_REMOVE_THUMBNAIL, $document);
        }

        return $this->redirect($this->generateUrl('erichard_dms_edit_document', array(
            'node' => $nodeSlug,
            'document' => $documentSlug,
        )));
    }
}
