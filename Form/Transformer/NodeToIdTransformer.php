<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 15:25
 */

namespace Erichard\DmsBundle\Form\Transformer;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class NodeToIdTransformer
 *
 * @package Erichard\DmsBundle\Form\Transformer
 */
class NodeToIdTransformer implements DataTransformerInterface
{
    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * NodeToIdTransformer constructor.
     *
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * transform
     *
     * @param \Erichard\DmsBundle\Entity\DocumentNode $node
     *
     * @return string
     */
    public function transform($node)
    {
        if (null === $node) {
            return '';
        }

        return $node->getId();
    }

    /**
     * reverseTransform
     *
     * @param integer $idx
     *
     * @return \Erichard\DmsBundle\Entity\DocumentNode|null|object
     */
    public function reverseTransform($idx)
    {
        if (null === $idx) {
            return null;
        }

        return $this
            ->registry
            ->getRepository('Erichard\DmsBundle\Entity\DocumentNode')
            ->find($idx)
        ;
    }
}
