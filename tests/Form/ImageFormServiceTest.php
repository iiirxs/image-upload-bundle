<?php


namespace IIIRxs\ImageUploadBundle\Tests\Form;

use IIIRxs\ImageUploadBundle\Form\ImageFormService;
use IIIRxs\ImageUploadBundle\Form\Type\ImageCollectionType;
use IIIRxs\ImageUploadBundle\Form\Type\ImageType;
use IIIRxs\ImageUploadBundle\Mapping\ClassPropertyMetadata;
use IIIRxs\ImageUploadBundle\Mapping\Factory\CacheClassPropertyMetadataFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;

class ImageFormServiceTest extends TestCase
{

    public function testGetImageCollectionTypeOptions()
    {
        $formFactory = $this->createMock(FormFactory::class);
        $metadataFactory = $this->createMock(CacheClassPropertyMetadataFactory::class);

        $imageFormService = new ImageFormService($formFactory, $metadataFactory);

        $imageTypeOptions = [
            'data_class' => DummyDocument::class,
            'entry_type' => ImageType::class,
            'image_data_class' => null,
            'field_name' => 'dummyField'
        ];

        $this->assertEquals($imageTypeOptions, $imageFormService->getImageCollectionTypeOptions($this->getMetadata()));
    }

    public function testCreateForm()
    {

        $formFactory = $this->createMock(FormFactory::class);

        $formFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->createMock(FormInterface::class))
            ->with(
                $this->equalTo(ImageCollectionType::class),
                $this->equalTo(new DummyDocument())
            )
        ;

        $imageFormService = new ImageFormService($formFactory, $this->getMockMetadataFactoryWithMetadata());

        $imageFormService->createForm(new DummyDocument(), 'dummyField');

    }

    private function getMockMetadataFactoryWithMetadata()
    {
        $metadataFactory = $this->createMock(CacheClassPropertyMetadataFactory::class);

        $metadataFactory
            ->expects($this->once())
            ->method('getMetadataFor')
            ->with($this->equalTo(new DummyDocument()), 'dummyField')
            ->willReturn($this->getMetadata());

        return $metadataFactory;
    }



    private function getMetadata()
    {
        return new ClassPropertyMetadata(DummyDocument::class, 'dummyField', []);
    }

//    public function mappingExceptionProvider()
//    {
//        return [
//            [
//                'mappings' => [ 'Invalid_Class' => [ 'fields' => [] ] ],
//                'exceptionClass' => InvalidClassException::class
//            ],
//            [
//                'mappings' => [ DummyDocument::class => [ 'fields' => [ 'invalid_field' ] ] ],
//                'exceptionClass' => InvalidPropertyException::class
//            ],
//            [
//                'mappings' => [ DummyDocument::class => [ 'fields' => [ 'dummyField' => [ 'class' => 'Invalid_Class' ] ] ] ],
//                'exceptionClass' => InvalidClassException::class
//            ],
//            [
//                'mappings' => [
//                    DummyDocument::class => [ 'fields' => [
//                        'dummyField' => [ 'class' => DummyDocument::class, 'entry_type' => 'Invalid_Class' ] ]
//                    ]
//                ],
//                'exceptionClass' => InvalidClassException::class
//            ],
//
//        ];
//    }
//
//    private function getValidMappings(): array
//    {
//        return [
//            DummyDocument::class => [
//                'fields' => [
//                    'dummyField' => [
//                        'class' => 'stdClass'
//                    ]
//                ]
//            ]
//        ];
//    }
}

class DummyDocument
{
    private $dummyField;
}