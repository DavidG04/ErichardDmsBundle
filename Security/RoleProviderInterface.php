<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 15:42
 */

namespace Erichard\DmsBundle\Security;

/**
 * Interface RoleProviderInterface
 *
 * @package Erichard\DmsBundle\Security
 */
interface RoleProviderInterface
{
    /**
     * getRoles
     *
     * @return mixed
     */
    public function getRoles();
}
