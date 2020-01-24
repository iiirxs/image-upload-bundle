<?php


namespace IIIRxs\ImageUploadBundle\Tests\Uploader;


use IIIRxs\ImageUploadBundle\Mapping\ClassPropertyMetadata;
use IIIRxs\ImageUploadBundle\Mapping\Factory\CacheClassPropertyMetadataFactory;
use IIIRxs\ImageUploadBundle\Uploader\DefaultUploader;
use PHPUnit\Framework\TestCase;

class DefaultUploaderTest extends TestCase
{

    public function testSelect()
    {
        $parent = new \stdClass();
        $propertyName = 'dummyField';
        $metadataFactory = $this->createMock(CacheClassPropertyMetadataFactory::class);
        $metadataFactory
            ->expects($this->once())
            ->method('getMetadataFor')
            ->with($parent, $propertyName)
            ->willReturn($this->createMock(ClassPropertyMetadata::class));

        $defaultUploader = new DefaultUploader($metadataFactory, 0);

        $defaultUploader->select(null, $parent, $propertyName);
    }

    public function testSupports()
    {
        $parent = new \stdClass();
        $propertyName = 'dummyField';
        $metadataFactory = $this->createMock(CacheClassPropertyMetadataFactory::class);
        $metadataFactory
            ->expects($this->once())
            ->method('hasMetadataFor')
            ->with($parent, $propertyName)
            ->willReturn(true);

        $defaultUploader = new DefaultUploader($metadataFactory, 0);

        $this->assertTrue($defaultUploader->supports(null, $parent, $propertyName));
    }

}