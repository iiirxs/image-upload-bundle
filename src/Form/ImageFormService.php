<?php

namespace IIIRxs\ImageUploadBundle\Form;

use IIIRxs\ImageUploadBundle\Form\Type\ImageCollectionType;
use IIIRxs\ImageUploadBundle\Mapping\Factory\CacheClassPropertyMetadataFactory;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class ImageFormService
{

    /**
     * @var FormFactory
     */
    private $formFactory;

    /**
     * @var CacheClassPropertyMetadataFactory
     */
    private $metadataFactory;

    /**
     * ImageService constructor.
     * @param FormFactoryInterface $formFactory
     * @param CacheClassPropertyMetadataFactory $metadataFactory
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        CacheClassPropertyMetadataFactory $metadataFactory
    )
    {
        $this->formFactory = $formFactory;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @param $object
     * @param string $fieldName
     * @return FormInterface
     */
    public function createForm($object, string $fieldName): FormInterface
    {
        return $this->formFactory->create(
            ImageCollectionType::class,
            $object,
            $this->getImageCollectionTypeOptions($object, $fieldName)
        );
    }

    /**
     * @param $object
     * @param string $fieldName
     * @return array
     */
    public function getImageCollectionTypeOptions($object, string $fieldName): array
    {
        $metadata = $this->metadataFactory->getMetadataFor($object, $fieldName);

        return [
            'data_class' => $metadata->getClassName(),
            'entry_type' => $metadata->getEntryType(),
            'image_data_class' => $metadata->getImageClass(),
            'field_name' => $metadata->getPropertyName()
        ];
    }

}