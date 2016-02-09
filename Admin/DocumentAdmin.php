<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 15:42
 */

namespace Erichard\DmsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Class DocumentAdmin
 *
 * @package Erichard\DmsBundle\Admin
 */
class DocumentAdmin extends Admin
{
    /**
     * persistent parameters
     *
     * @var null|string
     */
    protected $parameters = null;

    /**
     * getFormTheme
     *
     * @return array
     */
    public function getFormTheme()
    {
        return array_merge(
            parent::getFormTheme(),
            array(
                'ErichardDmsBundle:Sonata/Document:upload.html.twig',
            )
        );
    }

    /**
     * getPersistentParameters
     *
     * @return array
     */
    public function getPersistentParameters()
    {
        $array = array();
        if ($this->getRequest()) {
            $node = $this->parameters;
            if (!$node) {
                $node = $this->getRequest()->get('node');
            }
            if (!is_null($node)) {
                $array = array('node' => $node);
            }
            $pcode = $this->getRequest()->get('pcode');
            if (!is_null($pcode)) {
                $array['pcode'] = $pcode;
            }
            $pid = $this->getRequest()->get('pid');
            if (!is_null($pid)) {
                $array['pid'] = $pid;
            }
        }

        return $array;
    }

    /**
     * configureFormFields
     *
     * @param FormMapper $formMapper
     */
    public function configureFormFields(FormMapper $formMapper)
    {
        $label = 'add_documents';
        $type = 'dms_document_upload';
        if ($this->getSubject()->getId()) {
            $label = 'update_document';
            $type = 'dms_document';
        }

        $formMapper
            ->with($label, array('class' => 'col-md-12'))
                ->add('document', $type, array(
                    'label' => false,
                    'mapped' => false,
                    'data' => $this->getSubject(),
                ))
            ->end();
    }

    /**
     * configure Route
     *
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('upload');
        $collection->add('preview');
        $collection->add('download');
    }
}
