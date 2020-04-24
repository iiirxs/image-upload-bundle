<?php


namespace IIIRxs\ImageUploadBundle\Mapping;

use IIIRxs\ImageUploadBundle\DependencyInjection\Configuration;
use IIIRxs\ImageUploadBundle\Exception\InvalidClassException;
use IIIRxs\ImageUploadBundle\Form\Type\BaseParentType;
use IIIRxs\ImageUploadBundle\Util\DirectoryHelper;

class ClassPropertyMetadata implements ClassPropertyMetadataInterface
{

    /** @var string */
    private $className;

    /** @var string */
    private $propertyName;

    /** @var string */
    private $formType;

    /** @var string */
    private $entryType;

    /** @var string|null */
    private $imageClass;

    /** @var array|string|null */
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
        $this->setEntryType($config[Configuration::ENTRY_TYPE_KEY]);
        $this->setFormType($config[Configuration::FORM_TYPE_KEY]);
        $this->directories = DirectoryHelper::getDirectoriesFromConfiguration($config, Configuration::DIRECTORIES_KEY);
    }

    /**
     * @return string|null
     */
    public function getImageClass():? string
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

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * @return string
     */
    public function getEntryType(): string
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
            throw new InvalidClassException('Invalid mapped class in entry_type configuration');
        }

        $this->entryType = $entryType;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormType(): string
    {
        return $this->formType;
    }

    /**
     * @param string $formType
     * @return ClassPropertyMetadata
     * @throws InvalidClassException
     */
    public function setFormType(string $formType): ClassPropertyMetadata
    {
        if (!class_exists($formType)) {
            throw new InvalidClassException('Invalid mapped class in form_type configuration. Class does not exist.');
        }

        if (!is_subclass_of($formType, BaseParentType::class)) {
            throw new InvalidClassException('Invalid mapped class in form_type configuration. Class should not extend BaseParentType class.');
        }

        $this->formType = $formType;
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