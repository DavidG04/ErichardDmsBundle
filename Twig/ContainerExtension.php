<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 16:07
 */

namespace Erichard\DmsBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContainerExtension
 *
 * @package Erichard\DmsBundle\Twig
 */
class ContainerExtension extends \Twig_Extension
{
    /**
     * Container
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * ContainerExtension constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * getFunctions
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'parameter' => new \Twig_Function_Method($this, 'getParameter'),
        );
    }

    /**
     * getParameter
     *
     * @param string $paramName
     *
     * @return mixed
     */
    public function getParameter($paramName)
    {
        return $this->container->getParameter($paramName);
    }

    /**
     * getName
     *
     * @return string
     */
    public function getName()
    {
        return 'container_extension';
    }
}
