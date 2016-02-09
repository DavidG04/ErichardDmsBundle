<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 15:25
 */

namespace Erichard\DmsBundle\Controller;

use Erichard\DmsBundle\Entity\Document;
use Erichard\DmsBundle\Event\DmsDocumentEvent;
use Erichard\DmsBundle\Event\DmsEvents;
use Erichard\DmsBundle\Response\FileResponse;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SonataDocumentController
 *
 * @package Erichard\DmsBundle\Controller
 */
class SonataDocumentController extends CRUDController
{
    /**
     * index action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        return $this->redirect(
            $this->get('router')->generate('admin_erichard_dms_documentnode_list')
        );
    }

    /**
     * create action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        $request = $this->get('request');
        $currentNode = $this->getNodeProvider()->getCurrentNode();
        $document = new Document($currentNode);
        $this->admin->setSubject($document);
        $form = $this->admin->getForm();
        $form->setData($document);

        $template = 'ErichardDmsBundle:Sonata/Document:form.html.twig';
        if ($this->isXmlHttpRequest()) {
            $template = 'ErichardDmsBundle:Sonata/Document:ajax_form.html.twig';
        }

        if ($request->isMethod('GET')) {
            $view = $form->createView();
            $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());
            $response = $this->render($template, array(
                'node' => $currentNode,
                'form' => $view,
                'parent_node' => $this->getNodeProvider()->getRootNode(),
                'action' => 'create',
                'object' => $document,
            ));
        } else {
            $filenames = array_filter(explode(';', $request->request->get($this->admin->getUniqid())['document']['filename']));
            $tokens = array_filter(explode(';', $request->request->get($this->admin->getUniqid())['document']['token']));
            foreach ($filenames as $key => $filename) {
                $document = new Document($currentNode);
                $document->removeEmptyMetadatas();
                $this->get('dms.manager')->createDocument($filename, $tokens[$key], $currentNode, $document);
                if ($this->get('event_dispatcher')->hasListeners(DmsEvents::DOCUMENT_ADD)) {
                    $event = new DmsDocumentEvent($document);
                    $this->get('event_dispatcher')->dispatch(DmsEvents::DOCUMENT_ADD, $event);
                }
            }

            $this->addFlash(
                'success',
                $this->admin->trans(
                    'flash_create_success',
                    array('%name%' => $this->escapeHtml($this->admin->toString($document))),
                    'SonataAdminBundle'
                )
            );

            $response = $this->redirect(
                $this->get('router')->generate('admin_erichard_dms_documentnode_list', $request->query->all())
            );

        }

        return $response;
    }

    /**
     * Edit action.
     *
     * @param int|string|null $id
     *
     * @return Response|RedirectResponse
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     *
     * @SuppressWarnings("PMD")
     */
    public function editAction($id = null)
    {
        $request = $this->get('request');
        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        $template = 'ErichardDmsBundle:Sonata/Document:form.html.twig';
        if ($this->isXmlHttpRequest()) {
            $template = 'ErichardDmsBundle:Sonata/Document:ajax_form.html.twig';
        }

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->getRestMethod() == 'POST') {
            $form->submit($this->get('request'));

            $isFormValid = $form->isValid();
            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid) {
                try {
                    $object = $this->admin->update($object);
                    $this->addFlash(
                        'success',
                        $this->admin->trans(
                            'flash_edit_success',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );

                    return $this->redirect(
                        $this->get('router')->generate('admin_erichard_dms_documentnode_list', $request->query->all())
                    );

                } catch (ModelManagerException $e) {
                    $this->logModelManagerException($e);
                    $isFormValid = false;
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid && !$this->isXmlHttpRequest()) {
                $this->addFlash(
                    'error',
                    $this->admin->trans(
                        'flash_edit_error',
                        array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                        'SonataAdminBundle'
                    )
                );
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($template, array(
            'node' => $this->getNodeProvider()->getCurrentNode(),
            'form' => $view,
            'parent_node' => $this->getNodeProvider()->getRootNode(),
            'action' => 'edit',
            'object' => $object,
        ));
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
     * previewAction
     *
     * @return FileResponse
     */
    public function previewAction()
    {
        $request = $this->get('request');
        $dimension = $request->query->get('dimension');
        $document = $request->query->get('document');
        $node = $request->query->get('node');

        $document = $this->getNodeProvider()->findDocumentOrThrowError($document, $node);
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
     * showAction
     *
     * @param string $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @SuppressWarnings("PMD")
     */
    public function showAction($id = null)
    {
        $currentNode = $this->getNodeProvider()->getCurrentNode();
        $document = $this->getNodeProvider()->findDocumentOrThrowError($id, $currentNode->getSlug());
        $this->admin->setSubject($document);
        $form = $this->admin->getForm();
        $form->setData($document);
        $view = $form->createView();
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        $template = 'ErichardDmsBundle:Sonata/Document:show.html.twig';
        if ($this->isXmlHttpRequest()) {
            $template = 'ErichardDmsBundle:Sonata/Document:ajax_show.html.twig';
        }

        return $this->render($template, array(
            'node' => $currentNode,
            'form' => $view,
            'parent_node' => $this->getNodeProvider()->getRootNode(),
            'action' => 'create',
            'object' => $document,
        ));
    }

    /**
     * downloadAction
     *
     * @return FileResponse
     */
    public function downloadAction()
    {
        $request = $this->get('request');
        $document = $request->query->get('document');
        $node = $request->query->get('node');
        $document = $this->getNodeProvider()->findDocumentOrThrowError($document, $node);

        $absPath = $this->container->getParameter('dms.storage.path').DIRECTORY_SEPARATOR.$document->getFilename();

        $response = new FileResponse();
        $response->setFilename($absPath);

        $response->headers->set('Cache-Control', 'public');
        $response->headers->set('Content-Type', $document->getMimeType());
        $response->headers->set('Content-Transfer-Encoding', 'application/octet-stream');

        $contentDisposition = "filename*=UTF-8''".rawurlencode($document->getSlug().'.'.$document->getExtension());
        $response->headers->set('Content-Disposition', 'attachment; '.$contentDisposition);
        $response->sendHeaders();
        if ($this->get('event_dispatcher')->hasListeners(DmsEvents::DOCUMENT_DOWNLOAD)) {
            $event = new DmsDocumentEvent($document);
            $this->get('event_dispatcher')->dispatch(DmsEvents::DOCUMENT_DOWNLOAD, $event);
        }

        return $response;
    }

    /**
     * Delete action.
     *
     * @param int|string|null $id
     *
     * @return Response|RedirectResponse
     *
     * @throws NotFoundHttpException If the object does not exist
     * @throws AccessDeniedException If access is not granted
     *
     * @SuppressWarnings("PMD")
     */
    public function deleteAction($id)
    {
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('DELETE', $object)) {
            throw new AccessDeniedException();
        }

        $response = $this->redirect(
            $this->get('router')->generate('admin_erichard_dms_documentnode_list', $this->get('request')->query->all())
        );

        if ($this->getRestMethod() == 'DELETE') {
            // check the csrf token
            $this->validateCsrfToken('sonata.delete');

            try {
                $this->get('dms.manager')->deleteDocument($object);
                $this->admin->delete($object);

                $this->addFlash(
                    'success',
                    $this->admin->trans(
                        'flash_delete_success',
                        array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                        'SonataAdminBundle'
                    )
                );

                if ($this->isXmlHttpRequest()) {

                    return $response;
                }

                if ($this->get('event_dispatcher')->hasListeners(DmsEvents::DOCUMENT_DELETE)) {
                    $event = new DmsDocumentEvent($object);
                    $this->get('event_dispatcher')->dispatch(DmsEvents::DOCUMENT_DELETE, $event);
                }

            } catch (ModelManagerException $e) {
                $this->logModelManagerException($e);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(array('result' => 'error'));
                }

                $this->addFlash(
                    'error',
                    $this->admin->trans(
                        'flash_delete_error',
                        array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                        'SonataAdminBundle'
                    )
                );
            }

            return $response;
        }

        return $this->render($this->admin->getTemplate('delete'), array(
            'object'     => $object,
            'action'     => 'delete',
            'csrf_token' => $this->getCsrfToken('sonata.delete'),
        ));
    }

    /**
     * node provider
     *
     * @return \Erichard\DmsBundle\Service\NodeProvider
     */
    protected function getNodeProvider()
    {
        return $this->get('dms.node_provider');
    }

    /**
     * logModelManagerException
     *
     * @param mixed $exception
     */
    private function logModelManagerException($exception)
    {
        $context = array('exception' => $exception);
        if ($exception->getPrevious()) {
            $context['previous_exception_message'] = $exception->getPrevious()->getMessage();
        }
        $this->getLogger()->error($exception->getMessage(), $context);
    }
}
