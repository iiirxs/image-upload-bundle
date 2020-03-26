<?php


namespace IIIRxs\ImageUploadBundle\Mapping;


use IIIRxs\ImageUploadBundle\Exception\InvalidClassException;
use IIIRxs\ImageUploadBundle\Form\Type\ImageType;

class ClassPropertyMetadata implements ClassPropertyMetadataInterface
{

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $propertyName;

    private $entryType;
    private $imageClass;
    private $directories;

    /**
     * ClassPropertyMetadata constructor.
     * @param string $className
     * @param string $propertyName
     * @param array $config
     * @throws InvalidClassException
     */
    public function __construct(string $className, string $propertyName, array $config)
    {
        $this->className = $className;
        $this->propertyName = $propertyName;

        $this->setImageClass($config['class'] ?? null);
        $this->setEntryType($config['form_type'] ?? ImageType::class);
        $this->directories = $config['directories'] ?? null;
    }

    public function getImageClass()
    {
        return $this->imageClass;
    }

    /**
     * @param string|null $imageClass
     * @return ClassPropertyMetadata
     * @throws InvalidClassException
     */
    public function setImageClass(?string $imageClass): ClassPropertyMetadata
    {
        if (!is_null($imageClass) && !class_exists($imageClass)) {
            throw new InvalidClassException('Invalid mapped class in data_class configuration');
        }
        $this->imageClass = $imageClass;
        return $this;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getPropertyName()
    {
        return $this->propertyName;
    }

    public function getEntryType()
    {
        return $this->entryType;
    }

    /**
     * @param string $entryType
     * @return ClassPropertyMetadata
     * @throws InvalidClassException
     */
    public function setEntryType(string $entryType): ClassPropertyMetadata
    {
        if (!class_exists($entryType)) {
            throw new InvalidClassException('Invalid mapped class in form_type configuration');
        }

        $this->entryType = $entryType;
        return $this;
    }

    public function getDirectories()
    {
        return $this->directories;
    }

    public function setDirectories($directories)
    {
        $this->directories = $directories;
    }
}