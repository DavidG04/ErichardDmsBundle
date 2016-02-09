<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 26/01/2016
 * Time: 12:25
 */

namespace Erichard\DmsBundle\Gedable;

/**
 * Interface GedableInterface
 *
 * @package Erichard\DmsBundle\Gedable
 */
interface GedableInterface
{
    /**
     * Return ged ref of folder
     * 'uniqId' => array(
     *      'name' => 'name of folder of depth 2'
     *      'children' => array(
     *          'uniqId' => array(
     *              'name' => 'name of folder of depth 3'
     *              'children' => etc ....
     *
     * @return array
     */
    public function getGedTree();

    /**
     * return current ged uniqRef folder for object
     *
     * @return string
     */
    public function getGedUniqRef();
}
