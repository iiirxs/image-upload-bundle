<?php


namespace IIIRxs\ImageUploadBundle\Uploader;


use IIIRxs\ImageUploadBundle\Mapping\Factory\CacheClassPropertyMetadataFactory;

class DefaultUploader extends AbstractUploader
{

    /**
     * @var CacheClassPropertyMetadataFactory
     */
    private $metadataFactory;

    function __construct(
        CacheClassPropertyMetadataFactory $metadataFactory,
        int $maxThumbnailDimension
    )
    {
        parent::__construct($maxThumbnailDimension);
        $this->metadataFactory = $metadataFactory;
    }

    public function select($document, $parent, $propertyName)
    {
        $this->setTargetDir($this->metadataFactory->getMetadataFor($parent, $propertyName)->getDirectories());
    }

    public function supports($document, $parent, $propertyName): bool
    {
        return $this->metadataFactory->hasMetadataFor($parent, $propertyName);
    }
}