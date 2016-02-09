<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 15:42
 */

namespace Erichard\DmsBundle\Security;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RoleProvider
 *
 * @package Erichard\DmsBundle\Security
 */
class RoleProvider implements RoleProviderInterface
{
    /**
     * Container
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * RoleProvider constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoles()
    {
        if ($this->container->hasParameter('dms.permission.role_provider')) {
            $customRoleProvider = $this->container->get($this->container->getParameter('dms.permission.role_provider'));
            $roles = $customRoleProvider->getRoles();
        } else {
            $roles = $this->container->getParameter('dms.permission.roles');
        }

        return $roles;
    }
}
