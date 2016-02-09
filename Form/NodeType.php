<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 22/01/2016
 * Time: 14:48
 */

namespace Erichard\DmsBundle\Form;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Erichard\DmsBundle\Form\Transformer\NodeToIdTransformer;
use Erichard\DmsBundle\Iterator\GedmoTreeIterator;
use Erichard\DmsBundle\Service\NodeProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints;

/**
 * Class NodeType
 *
 * @package Erichard\DmsBundle\Form
 */
class NodeType extends AbstractType
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
     * request stack
     *
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var FormFactoryInterface
     */
    protected $factory;

    /**
     * NodeType constructor.
     *
     * @param Registry     $registry
     * @param NodeProvider $nodeProvider
     * @param RequestStack $requestStack
     */
    public function __construct(Registry $registry, NodeProvider $nodeProvider, RequestStack $requestStack)
    {
        $this->registry = $registry;
        $this->nodeProvider = $nodeProvider;
        $this->requestStack = $requestStack;
    }

    /**
     * build Form
     *
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
                'label' => 'node_name',
                'constraints' => array(new Constraints\NotBlank()),
            ))
            ->add('parent', 'entity', array(
                'label' => false,
                'label_attr' => array(
                'class' => 'hidden',
                ),
                'attr' => array(
                'class' => 'hidden',
                ),
                'class' => 'Erichard\DmsBundle\Entity\DocumentNode',
                'data' => $this->nodeProvider->getCurrentNode(),
            ))
            ->add('metadatas', 'node_metadata', array(
                'label' => false,
                'mapped' => false,
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
        $node = $event->getData();
        if (null === $node) {

            return;
        }
    }

    /**
     * onPreSetData
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();
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
            $this->factory->createNamedBuilder('parent', 'choice', $data->getParent(), array(
                'label' => 'move_node',
                'required'      => true,
                'choices'       => $choices,
                'auto_initialize' => false,
            ))->addModelTransformer(new NodeToIdTransformer($this->registry))
                ->getForm()
        );
    }

    /**
     * get name
     *
     * @return string
     */
    public function getName()
    {
        return 'dms_node';
    }
}
