<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 22/01/2016
 * Time: 14:48
 */

namespace Erichard\DmsBundle\Form;

use Erichard\DmsBundle\Form\Transformer\NodeToIdTransformer;
use Erichard\DmsBundle\Iterator\GedmoTreeIterator;
use Erichard\DmsBundle\Service\NodeProvider;
use Florajet\CoreBundle\Override\Doctrine\Registry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

/**
 * Class DocumentType
 *
 * @package Erichard\DmsBundle\Form
 */
class DocumentType extends AbstractType
{
    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Node provider
     *
     * @var NodeProvider
     */
    protected $nodeProvider;

    /**
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * NodeType constructor.
     *
     * @param Registry     $registry
     * @param NodeProvider $nodeProvider
     */
    public function __construct(Registry $registry, NodeProvider $nodeProvider)
    {
        $this->registry = $registry;
        $this->nodeProvider = $nodeProvider;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @SuppressWarnings("unused")
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'required' => true,
                'constraints' => array(
                new Constraints\NotBlank(),
                ),
            ))
            ->add('metadatas', 'document_metadata', array(
                'label' => false,
                'mapped' => false,
                'data' => $builder->getData()->getMetadatas(),
            ));
        $this->factory = $builder->getFormFactory();
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'))
            ->addEventListener(FormEvents::POST_SUBMIT, array($this, 'onPostSubmit'));
    }

    /**
     * onPostSubmit
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $document = $event->getData();
        if (null === $document) {

            return;
        }
    }

    /**
     * onPreSetData
     *
     * @param FormEvent $event
     *
     * @SuppressWarnings("PMD")
     */
    public function onPreSetData(FormEvent $event)
    {
        $data = $event->getData();

        $form = $event->getForm();

        if (null === $data) {
            return;
        }

        if (null === $data->getId()) {
            $form->add($this->factory->createNamed('filename', 'hidden', null, array('auto_initialize' => false)));
            $form->add($this->factory->createNamed('originalName', 'hidden', null, array('auto_initialize' => false)));
            $form->add($this->factory->createNamed('token', 'hidden', null, array(
                'mapped' => false,
                'auto_initialize' => false,
            )));
        }

        if (null === $data || null === $data->getId()) {
            return;
        }
        $rootNode = $this->nodeProvider->getRootNode();

        $repository = $this->registry
            ->getRepository('Erichard\DmsBundle\Entity\DocumentNode');

        $allTree = $this->registry
            ->getRepository('Erichard\DmsBundle\Entity\DocumentNodeClosure')
            ->createQueryBuilder('c')
            ->select('d.id')
            ->innerJoin('c.descendant', 'd')
            ->where('c.ancestor = :id')
            ->setParameter('id', $rootNode->getId())
            ->getQuery()
            ->getResult();

        $treeArray = array();
        foreach ($allTree as $tree) {
            $treeArray[] = $tree['id'];
        }

        $descendants = $this->registry
            ->getRepository('Erichard\DmsBundle\Entity\DocumentNodeClosure')
            ->createQueryBuilder('c')
            ->select('d.id')
            ->innerJoin('c.descendant', 'd')
            ->where('c.ancestor = :id')
            ->setParameter('id', $data->getId())
            ->getQuery()
            ->getArrayResult();
        $descendants = array_map(
            function ($descendant) {
                return $descendant['id'];
            },
            $descendants
        );

        $tree = $repository
            ->getNodesHierarchyQueryBuilder(
                null,
                false,
                array(
                    'childSort' => array(
                        'field' => 'name',
                        'dir' => 'asc',
                    ),
                ),
                true
            );

        if (count($descendants) > 0) {
            $tree
                ->andWhere('node.id NOT IN (:node_id)')
                ->setParameter('node_id', $descendants);
        }

        $realTreeArray = array();

        foreach ($tree->getQuery()->getArrayResult() as $node) {
            if (in_array($node[0]['descendant']['id'], $treeArray)) {
                $realTreeArray[] = $node;
            }
        }

        $tree = $repository->buildTree($realTreeArray);
        // On met en place notre itérator en vue de retirer la profondeur de nos éléments
        $iterator = new \RecursiveIteratorIterator(
            new GedmoTreeIterator($tree),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $choices = array();
        foreach ($iterator as $node) {
            $nodeName = $node['name'];
            if ($node['id'] == $rootNode->getId()) {
                $nodeName = $this->registry->getContainer()->get('translator')->trans('home', array(), 'ErichardDmsBundle');
            }
            $depth = $iterator->getDepth();
            $choices[$node['id']] = str_repeat("&nbsp;&nbsp;&nbsp;", $depth).$nodeName;
        }

        $form->add(
            $this->factory->createNamedBuilder('node', 'choice', $data->getParent(), array(
                'label' => 'move_document',
                'required'      => true,
                'choices'       => $choices,
                'auto_initialize' => false,
            ))->addModelTransformer(new NodeToIdTransformer($this->registry))
                ->getForm()
        );
    }

    /**
     * Option par defaut
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Erichard\DmsBundle\Entity\Document',
        ));
    }

    /**
     * getName
     *
     * @return string
     */
    public function getName()
    {
        return 'dms_document';
    }
}
