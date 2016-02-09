<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 16:07
 */

namespace Erichard\DmsBundle\Twig;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Erichard\DmsBundle\Entity\DocumentInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class DmsExtension
 *
 * @package Erichard\DmsBundle\Twig
 */
class DmsExtension extends \Twig_Extension
{
    /**
     * Router
     *
     * @var RouterInterface
     */
    protected $router;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * DmsExtension constructor.
     *
     * @param RouterInterface $router
     * @param Registry        $registry
     */
    public function __construct(RouterInterface $router, Registry $registry)
    {
        $this->router = $router;
        $this->registry = $registry;
    }

    /**
     * getFilters
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            'filesize' => new \Twig_Filter_Method($this, 'getFileSize'),
            'shorten'  => new \Twig_Filter_Method($this, 'shorten', array(
                'is_safe' => array('html'),
            )),
        );
    }

    /**
     * getFunctions
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'sonataThumbUrl' => new \Twig_Function_Method($this, 'getSonataThumbUrl'),
            'thumbUrl' => new \Twig_Function_Method($this, 'getThumbUrl'),
            'roots'    => new \Twig_Function_Method($this, 'getRoots'),
        );
    }

    /**
     * getFileSize
     *
     * @param int $sizeInBytes
     *
     * @return string
     */
    public function getFileSize($sizeInBytes)
    {
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');

        if (null === $sizeInBytes) {
            return '';
        }

        return @round($sizeInBytes/pow(1024, ($iii = (int) floor(log($sizeInBytes, 1024)))), 2).' '.$unit[$iii];
    }

    /**
     * getThumbUrl
     *
     * @param DocumentInterface $document
     * @param mixed             $dimension
     * @param bool              $absolute
     *
     * @return string
     */
    public function getThumbUrl(DocumentInterface $document, $dimension, $absolute = false)
    {
        return $this->router->generate('erichard_dms_document_preview', array(
            'document'    => $document->getSlug(),
            'node'        => $document->getNode()->getSlug(),
            'dimension'   => $dimension,
        ), $absolute).'?'.$document->getUpdatedAt()->getTimestamp();
    }

    /**
     * getSonataThumbUrl
     *
     * @param DocumentInterface $document
     * @param mixed             $dimension
     * @param bool              $absolute
     *
     * @return string
     */
    public function getSonataThumbUrl(DocumentInterface $document, $dimension, $absolute = false)
    {
        return $this->router->generate('admin_erichard_dms_document_preview', array(
            'document'    => $document->getSlug(),
            'node'        => $document->getNode()->getSlug(),
            'dimension'   => $dimension,
        ), $absolute);
    }

    /**
     * getRoots
     *
     * @return \Erichard\DmsBundle\Entity\DocumentNode[]
     */
    public function getRoots()
    {
        return $this
            ->registry
            ->getRepository('Erichard\DmsBundle\Entity\DocumentNode')
            ->getRoots()
        ;
    }

    /**
     * getName
     *
     * @return string
     */
    public function getName()
    {
        return "dms_extension";
    }
}
