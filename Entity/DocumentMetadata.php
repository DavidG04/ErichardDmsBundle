<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 22/01/2016
 * Time: 14:48
 */

namespace Erichard\DmsBundle\Entity;

/**
 * Class DocumentMetadata
 *
 * @package Erichard\DmsBundle\Entity
 */
class DocumentMetadata implements DocumentMetadaInterface
{
    /**
     * id
     *
     * @var integer
     */
    protected $id;

    /**
     * name
     *
     * @var string
     */
    protected $name;

    /**
     * label
     *
     * @var string
     */
    protected $label;

    /**
     * type
     *
     * @var string
     */
    protected $type;

    /**
     * default Value
     *
     * @var mixed
     */
    protected $defaultValue;

    /**
     * attribute
     *
     * @var mixed
     */
    protected $attributes;

    /**
     * scope
     *
     * @var string
     */
    protected $scope;

    /**
     * required
     *
     * @var bool
     */
    protected $required;

    /**
     * visible
     *
     * @var bool
     */
    protected $visible;

    /**
     * scope values
     *
     * @var array
     */
    public static $scopeValues = array(
        'document' => 'metadata.scope.document',
        'node'     => 'metadata.scope.node',
        'both'     => 'metadata.scope.both',
    );

    /**
     * DocumentMetadata constructor.
     */
    public function __construct()
    {
        $this->required = false;
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function setScope($scope)
    {
        if (null !== $scope && !isset(self::$scopeValues[$scope])) {
            throw new \InvalidArgumentException(sprintf(
                'The value "%s" is not allowed for the scope property.',
                $scope
            ));
        }

        $this->scope = $scope;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * {@inheritDoc}
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * {@inheritDoc}
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributes()
    {
        return (null === $this->attributes)? array() : $this->attributes;
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }
}
