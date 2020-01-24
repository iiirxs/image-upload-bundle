<?php

namespace IIIRxs\ImageUploadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($options['field_name'], CollectionType::class, [
                'entry_type' => $options['entry_type'],
                'entry_options' => [ 'data_class' => $options['image_data_class'] ],
                'allow_add' => true,
                'allow_delete' => true,
            ])
        ;
    }

    public function getBlockPrefix(){
        return 'image_collection';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'entry_type' => ImageType::class
        ]);

        $resolver->setRequired(['field_name', 'data_class', 'image_data_class']);
    }
}
