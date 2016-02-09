<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 22/01/2016
 * Time: 14:48
 */

namespace Erichard\DmsBundle\Form;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class DocumentMetadataType
 *
 * @package Erichard\DmsBundle\Form
 */
class DocumentMetadataType extends AbstractType
{
    /**
     * EntityManager
     *
     * @var EntityManager
     */
    protected $emn;

    /**
     * Datas
     *
     * @var array
     */
    protected $datas;

    /**
     * DocumentMetadataType constructor.
     *
     * @param EntityManager $emn
     */
    public function __construct($emn)
    {
        $this->emn = $emn;
    }

    /**
     * buildForm
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @SuppressWarnings("unused")
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $metadatas = $this->emn->getRepository('Erichard\DmsBundle\Entity\DocumentMetadata')->findByScope(array('document', 'both'));
        foreach ($metadatas as $meta) {
            $data = null;
            if ($meta->isVisible()) {
                foreach ($builder->getData() as $metadataLnk) {
                    if ($metadataLnk->getMetadata() == $meta) {
                        $data = $metadataLnk->getValue();
                    }
                }
                $builder->add($meta->getName(), $meta->getType(), array_merge(array(
                    'label' => $meta->getLabel(),
                    'required' => $meta->isRequired(),
                    'data' => $data,
                    'mapped' => false,
                ), $meta->getAttributes()));
            }
        }
        $builder
            ->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'))
            ->addEventListener(FormEvents::SUBMIT, array($this, 'onSubmit'));
    }

    /**
     * onPreSubmit
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $this->datas = $event->getData();
    }

    /**
     * onSubmit
     *
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        foreach ($event->getData() as $metadataLnk) {
            if ($metadataLnk->getMetadata()->isVisible() && array_key_exists($metadataLnk->getMetadata()->getName(), $this->datas)) {
                $metadataLnk->setValue($this->datas[$metadataLnk->getMetadata()->getName()]);
            }
        }
    }

    /**
     * getName
     *
     * @return string
     */
    public function getName()
    {
        return 'document_metadata';
    }
}
