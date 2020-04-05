<?php


namespace IIIRxs\ImageUploadBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseParentType extends AbstractType
{

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'field_name' => null,
            'data_class' => null,
            'image_data_class' => null
        ]);
    }
}
{

}