<?php

namespace Erichard\DmsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'required' => true,
                'constraints' => array(
                    new Constraints\NotBlank()
                )
            ))
            ->add('enabled')
            ->add('metadatas', 'document_metadata', array(
                'label' => false,
                'mapped' => false
            ))
        ;

        $factory = $builder->getFormFactory();

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($factory) {
            $document = $event->getData();
            $form = $event->getForm();

            if (null === $document) {
                return;
            }

            if (null === $document->getId()) {
                $form->add($factory->createNamed('filename', 'hidden'));
                $form->add($factory->createNamed('originalName', 'hidden'));
                $form->add($factory->createNamed('token', 'hidden', array(
                    'mapped' => false
                )));
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Erichard\DmsBundle\Entity\Document'
        ));
    }

    public function getName()
    {
        return 'document';
    }
}
