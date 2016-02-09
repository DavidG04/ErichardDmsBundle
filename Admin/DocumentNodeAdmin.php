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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class DocumentNodeAdmin
 *
 * @package Erichard\DmsBundle\Admin
 */
class DocumentNodeAdmin extends Admin
{
    /**
     * persistent parameters
     *
     * @var null|string
     */
    protected $parameters = null;

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
        $label = 'add_node';
        if ($this->getSubject()->getId()) {
            $label = 'update_node';
        }

        $formMapper
            ->with($label, array('class' => 'col-md-12'))
                ->add('node', 'dms_node', array(
                    'label' => false,
                    'mapped' => false,
                    'data' => $this->getSubject(),
                ))
            ->end()
            ->getFormBuilder()->addEventListener(FormEvents::SUBMIT, array($this, 'onSubmit'));
    }

    /**
     * on submit
     *
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        $this->parameters = $event->getData()->getParent()->getSlug();
    }
}
