<?php


namespace IIIRxs\ImageUploadBundle\Form;


use Doctrine\ODM\MongoDB\DocumentManager;
use IIIRxs\ImageUploadBundle\Exception\InvalidMappedClassException;
use IIIRxs\ImageUploadBundle\Exception\InvalidMappedFieldException;
use IIIRxs\ImageUploadBundle\Exception\UnsetDataClassException;
use IIIRxs\ImageUploadBundle\Form\Type\ImageCollectionType;
use IIIRxs\ImageUploadBundle\Form\Type\ImageType;
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
     * @var DocumentManager
     */
    private $documentManager;

    private $mappings;

    /**
     * ImageService constructor.
     * @param FormFactoryInterface $formFactory
     * @param DocumentManager $documentManager
     * @param array|null $mappings
     * @throws \Exception
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        DocumentManager $documentManager,
        array $mappings
    )
    {
        $this->formFactory = $formFactory;
        $this->documentManager = $documentManager;

        foreach ($mappings as $class => $fields) {
            $this->addMapping($class, $fields['fields']);
        }
    }

    /**
     * @param string $class
     * @param array $fields
     * @throws InvalidMappedClassException
     * @throws InvalidMappedFieldException
     */
    private function addMapping(string $class, array $fields)
    {
        if (!class_exists($class)) {
            throw new InvalidMappedClassException('Invalid mapped class in root configuration');
        }

        $this->mappings[$class] = [];
        foreach ($fields as $field => $options) {
            if (!property_exists($class, $field)) {
                throw new InvalidMappedFieldException();
            }

            if (isset($options['class']) && !class_exists($options['class'])) {
                throw new InvalidMappedClassException('Invalid mapped class in data_class configuration');
            }

            if (isset($options['form_type']) && !class_exists($options['form_type'])) {
                throw new InvalidMappedClassException('Invalid mapped class in form_type configuration');
            }

            $this->mappings[$class][$field] = $options;
        }
    }

    /**
     * @param $object
     * @param string $fieldName
     * @return FormInterface
     * @throws UnsetDataClassException
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
     * @throws UnsetDataClassException
     */
    public function getImageCollectionTypeOptions($object, string $fieldName): array
    {
        $associationMappings = $this->documentManager->getClassMetadata(get_class($object))->associationMappings;

        $mapping = $this->mappings[get_class($object)][$fieldName] ?? [];
        $entryType = $mapping['form_type'] ?? ImageType::class;
        $imageClass = $mapping['class'] ?? $associationMappings[$fieldName]['targetDocument'] ?? null;

        if (empty($imageClass)) {
            throw new UnsetDataClassException();
        }

        return [
            'data_class' => get_class($object),
            'entry_type' => $entryType,
            'image_data_class' => $imageClass,
            'field_name' => $fieldName
        ];
    }

}