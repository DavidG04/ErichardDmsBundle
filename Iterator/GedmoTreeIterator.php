<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 22/01/2016
 * Time: 14:48
 */

namespace Erichard\DmsBundle\Iterator;

/**
 * Class GedmoTreeIterator
 *
 * @package Erichard\DmsBundle\Iterator
 */
class GedmoTreeIterator extends \RecursiveArrayIterator
{
    /**
     * getChildren
     *
     * @return mixed
     */
    public function getChildren()
    {
        $current = $this->current();
        $class = get_class($this);

        return new $class($current['__children']);
    }

    /**
     * has children
     *
     * @return bool
     */
    public function hasChildren()
    {
        $current = $this->current();

        return count($current['__children']) > 0;
    }
}
