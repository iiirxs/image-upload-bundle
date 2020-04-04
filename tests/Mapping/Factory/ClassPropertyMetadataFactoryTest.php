<?php


namespace IIIRxs\ImageUploadBundle\Tests\Mapping\Factory;


use Doctrine\ODM\MongoDB\DocumentManager;
use IIIRxs\ImageUploadBundle\Document\AbstractImage;
use IIIRxs\ImageUploadBundle\Exception\InvalidClassException;
use IIIRxs\ImageUploadBundle\Exception\InvalidPropertyException;
use IIIRxs\ImageUploadBundle\Exception\InvalidUploadTargetDirException;
use IIIRxs\ImageUploadBundle\Form\Type\ImageType;
use IIIRxs\ImageUploadBundle\Mapping\Factory\ClassPropertyMetadataFactory;
use IIIRxs\ImageUploadBundle\Tests\Util\TestImageContainer;
use PHPUnit\Framework\TestCase;

class ClassPropertyMetadataFactoryTest extends TestCase
{

    public function testGetMetadataForInvalidClass()
    {
        $this->expectException(InvalidClassException::class);

        $documentManager = $this->createMock(DocumentManager::class);
        $factory = new ClassPropertyMetadataFactory($documentManager, [], '');

        $factory->getMetadataFor('invalid_class', '');
    }

    public function testGetMetadataForInvalidProperty()
    {
        $this->expectException(InvalidPropertyException::class);

        $documentManager = $this->createMock(DocumentManager::class);
        $factory = new ClassPropertyMetadataFactory($documentManager, [], '');

        $factory->getMetadataFor(new \stdClass(), 'invalid_property');
    }

    public function testGetMetadataWithoutDirectories()
    {
        $this->expectException(InvalidUploadTargetDirException::class);

        $config = [
            TestImageContainer::class => [
                'fields' => [
                    'images' => [
                        'class' => AbstractImage::class,
                        'entry_type' => ImageType::class
                    ]
                ]
            ]
        ];

        $documentManager = $this->createMock(DocumentManager::class);
        $factory = new ClassPropertyMetadataFactory($documentManager, $config, '');

        $factory->getMetadataFor(TestImageContainer::class, 'images');
    }

}