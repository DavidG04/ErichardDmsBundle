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

/**
 * Class NodeMetadataType
 *
 * @package Erichard\DmsBundle\Form
 */
class NodeMetadataType extends AbstractType
{
    /**
     * EntityManager
     *
     * @var EntityManager
     */
    protected $emn;

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
        $metadatas = $this->emn->getRepository('Erichard\DmsBundle\Entity\DocumentMetadata')->findByScope(array('node', 'both'));

        foreach ($metadatas as $meta) {
            $builder->add($meta->getName(), $meta->getType(), array_merge(array(
                'label' => $meta->getLabel(),
                'required' => $meta->isRequired(),
            ), $meta->getAttributes()));
        }
    }

    /**
     * getName
     *
     * @return string
     */
    public function getName()
    {
        return 'node_metadata';
    }
}
