<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 15:25
 */

namespace Erichard\DmsBundle\Controller;

use Erichard\DmsBundle\Event\DmsEvents;
use Erichard\DmsBundle\Event\DmsNodeEvent;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SonataNodeController
 *
 * @package Erichard\DmsBundle\Controller
 */
class SonataNodeController extends CRUDController
{
    /**
     * listAction
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        $template = 'ErichardDmsBundle:Sonata/Node:list.html.twig';
        if ($this->isXmlHttpRequest()) {
            $template = 'ErichardDmsBundle:Sonata/Node:ajax_list.html.twig';
        }

        return $this->render($template, array(
            'node'        => $this->getNodeProvider()->getCurrentNode(),
            'mode'        => $this->get('request')->query->get('mode', 'table'),
            'show_nodes'  => $this->container->getParameter('dms.workspace.show_nodes'),
            'parent_node' => $this->getNodeProvider()->getRootNode(),
            'action'      => 'list',
        ));
    }

    /**
     * create action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $object = $this->admin->getNewInstance();
        $this->admin->setSubject($object);
        $form = $this->admin->getForm();
        $form->setData($object);
        if ($this->get('event_dispatcher')->hasListeners(DmsEvents::NODE_ADD)) {
            $event = new DmsNodeEvent($object);
            $this->get('event_dispatcher')->dispatch(DmsEvents::NODE_ADD, $event);
        }

        $template = 'ErichardDmsBundle:Sonata/Node:form.html.twig';
        if ($this->isXmlHttpRequest()) {
            $template = 'ErichardDmsBundle:Sonata/Node:ajax_form.html.twig';
        }
        if ($this->getRestMethod() == 'POST') {
            $form->submit($this->get('request'));

            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid) {

                $object = $this->admin->create($object);

                $this->addFlash(
                    'success',
                    $this->admin->trans(
                        'flash_create_success',
                        array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                        'SonataAdminBundle'
                    )
                );

                if ($this->isXmlHttpRequest()) {
                    return new RedirectResponse($this->admin->generateUrl('list'));
                }

                // redirect to edit mode
                return $this->redirectTo($object);
            }

            // show an error message if the form failed validation
            if (!$isFormValid && !$this->isXmlHttpRequest()) {
                $this->addFlash(
                    'error',
                    $this->admin->trans(
                        'flash_create_error',
                        array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                        'SonataAdminBundle'
                    )
                );
            }
        }

        $view = $form->createView();
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($template, array(
            'node' => $this->getNodeProvider()->getCurrentNode(),
            'form' => $view,
            'parent_node' => $this->getNodeProvider()->getRootNode(),
            'action' => 'create',
            'object' => $object,
        ));
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
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT')) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);
        $template = 'ErichardDmsBundle:Sonata/Node:form.html.twig';
        if ($this->isXmlHttpRequest()) {
            $template = 'ErichardDmsBundle:Sonata/Node:ajax_form.html.twig';
        }
        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->getRestMethod() == 'POST') {
            $form->submit($this->get('request'));

            $isFormValid = $form->isValid();
            if ($isFormValid) {
                $object = $this->admin->update($object);

                $this->addFlash(
                    'success',
                    $this->admin->trans(
                        'flash_edit_success',
                        array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                        'SonataAdminBundle'
                    )
                );

                if ($this->isXmlHttpRequest()) {
                    return new RedirectResponse($this->admin->generateUrl('list'));
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
            'action' => 'edit',
            'node' => $this->getNodeProvider()->getCurrentNode(),
            'form' => $view,
            'parent_node' => $this->getNodeProvider()->getRootNode(),
            'object' => $object,
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
}
