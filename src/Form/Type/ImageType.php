<?php

namespace IIIRxs\ImageUploadBundle\Form\Type;

use IIIRxs\ImageUploadBundle\Document\AbstractImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rank', HiddenType::class, [])
            ->add('file', FileType::class, ['required' => true])
        ;
    }

    public function getBlockPrefix()
    {
        return 'image';
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('data_class');
        $resolver->setAllowedValues('data_class', function ($dataClass) {
            return is_subclass_of($dataClass, AbstractImage::class);
        });
    }
}