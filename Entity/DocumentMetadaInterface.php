<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 04/02/2016
 * Time: 11:01
 */

namespace Erichard\DmsBundle\Entity;

/**
 * Interface DocumentMetadaIntarface
 *
 * @package Erichard\DmsBundle\Entity
 */
interface DocumentMetadaInterface
{
    /**
     * getId
     *
     * @return int
     */
    public function getId();

    /**
     * setScope
     *
     * @param string $scope
     *
     * @return $this
     */
    public function setScope($scope);

    /**
     * setType
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType($type);

    /**
     * getName
     *
     * @return string
     */
    public function getName();

    /**
     * setName
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * getType
     *
     * @return string
     */
    public function getType();

    /**
     * getLabel
     *
     * @return string
     */
    public function getLabel();

    /**
     * setLabel
     *
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label);

    /**
     * isRequired
     *
     * @return bool
     */
    public function isRequired();

    /**
     * setRequired
     *
     * @param bool $required
     *
     * @return $this
     */
    public function setRequired($required);

    /**
     * setVisible
     *
     * @param bool $visible
     *
     * @return $this
     */
    public function setVisible($visible);

    /**
     * isVisible
     *
     * @return bool
     */
    public function isVisible();

    /**
     * getAttributes
     *
     * @return array|mixed
     */
    public function getAttributes();

    /**
     * getDefaultValue
     *
     * @return mixed
     */
    public function getDefaultValue();
}
