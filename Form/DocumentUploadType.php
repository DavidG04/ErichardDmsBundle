<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 01/02/2016
 * Time: 09:23
 */

namespace Erichard\DmsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class DocumentUploadType
 *
 * @package Erichard\DmsBundle\Form
 */
class DocumentUploadType extends AbstractType
{
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
            ->add('filename', 'hidden')
            ->add('token', 'hidden', array('mapped' => false));
    }

    /**
     * get name
     *
     * @return string
     */
    public function getName()
    {
        return 'dms_document_upload';
    }
}
