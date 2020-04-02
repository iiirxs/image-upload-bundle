<?php


namespace IIIRxs\ImageUploadBundle\Mapping\Factory;


use Doctrine\ODM\MongoDB\DocumentManager;
use IIIRxs\ImageUploadBundle\Document\AbstractImage;
use IIIRxs\ImageUploadBundle\Exception\InvalidClassException;
use IIIRxs\ImageUploadBundle\Exception\InvalidPropertyException;
use IIIRxs\ImageUploadBundle\Exception\InvalidUploadTargetDirException;
use IIIRxs\ImageUploadBundle\Exception\UnsetDataClassException;
use IIIRxs\ImageUploadBundle\Mapping\ClassPropertyMetadata;
use IIIRxs\ImageUploadBundle\Mapping\ClassPropertyMetadataInterface;

class ClassPropertyMetadataFactory implements ClassPropertyMetadataFactoryInterface
{

    /**
     * @var array
     */
    private $mappings;

    /**
     * @var DocumentManager
     */
    private $documentManager;
    private $defaultImageDir;

    public function __construct(DocumentManager $documentManager, array $mappings, $defaultImageDir)
    {
        $this->mappings = $mappings;
        $this->documentManager = $documentManager;
        $this->defaultImageDir = $defaultImageDir;
    }

    /**
     * @param $class
     * @param $property
     * @return ClassPropertyMetadataInterface
     * @throws InvalidClassException
     * @throws InvalidPropertyException
     * @throws InvalidUploadTargetDirException
     */
    public function getMetadataFor($class, $property): ClassPropertyMetadataInterface
    {
        $class = \is_string($class) ? $class : get_class($class);

        if (!class_exists($class)) {
            throw new InvalidClassException('Invalid class');
        }

        if (!property_exists($class, $property)) {
            throw new InvalidPropertyException();
        }

        $config = $this->mappings[$class]['fields'][$property] ?? [];
        $metadata = new ClassPropertyMetadata($class, $property, $config);

        if (is_null($metadata->getImageClass())) {
            $associationMappings = $this->documentManager->getClassMetadata($class)->associationMappings;
            $metadata->setImageClass($associationMappings[$property]['targetDocument'] ?? AbstractImage::class);
        }

        if (empty($metadata->getDirectories())) {
            if (empty($this->defaultImageDir)) {
                throw new InvalidUploadTargetDirException(sprintf('Target directories not set for %s, %s', $class, $property));
            }
            $metadata->setDirectories($this->defaultImageDir);
        }

        return $metadata;
    }

    public function hasMetadataFor($class, $property): bool
    {
        if (empty($class) || empty($property)) {
            return false;
        }

        $class = \is_string($class) ? $class : get_class($class);
        $mappings = $this->mappings[$class]['fields'][$property] ?? [];

        return
            class_exists($class)
            && property_exists($class, $property)
            && (
                (isset($mappings['directories']) && !empty($mappings['directories']))
                || !empty($this->defaultImageDir)
            )
        ;
    }
}