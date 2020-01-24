<?php


namespace IIIRxs\ImageUploadBundle\Tests\Form;


use Doctrine\ODM\MongoDB\DocumentManager;
use IIIRxs\ImageUploadBundle\Exception\InvalidMappedClassException;
use IIIRxs\ImageUploadBundle\Exception\InvalidMappedFieldException;
use IIIRxs\ImageUploadBundle\Exception\UnsetDataClassException;
use IIIRxs\ImageUploadBundle\Form\ImageFormService;
use IIIRxs\ImageUploadBundle\Form\Type\ImageCollectionType;
use IIIRxs\ImageUploadBundle\Form\Type\ImageType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;

class ImageFormServiceTest extends TestCase
{

    /**
     * @param $mappings
     * @param $exceptionClass
     * @throws \Exception
     * @dataProvider mappingExceptionProvider
     */
    public function testMappingExceptionOnCreation($mappings, $exceptionClass)
    {
        $this->expectException($exceptionClass);

        $documentManager = $this->createMock(DocumentManager::class);
        $formFactory = $this->createMock(FormFactory::class);

        new ImageFormService($formFactory, $documentManager, $mappings);
    }

    public function testGetImageCollectionTypeOptions()
    {
        $documentManager = $this->createMock(DocumentManager::class);
        $formFactory = $this->createMock(FormFactory::class);

        $mappings = $this->getValidMappings();
        $imageFormService = new ImageFormService($formFactory, $documentManager, $mappings);

        $imageTypeOptions = [
            'data_class' => DummyDocument::class,
            'entry_type' => ImageType::class,
            'image_data_class' => \stdClass::class,
            'field_name' => 'dummyField'
        ];

        $this->assertEquals($imageTypeOptions, $imageFormService->getImageCollectionTypeOptions(new DummyDocument(), 'dummyField'));
    }

    public function testGetImageCollectionTypeOptionsException()
    {
        $this->expectException(UnsetDataClassException::class);
        $documentManager = $this->createMock(DocumentManager::class);
        $formFactory = $this->createMock(FormFactory::class);

        $imageFormService = new ImageFormService($formFactory, $documentManager, []);

        $imageFormService->getImageCollectionTypeOptions(new \stdClass(), '');
    }

    public function testCreateForm()
    {
        $documentManager = $this->createMock(DocumentManager::class);
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

        $mappings = $this->getValidMappings();

        $imageFormService = new ImageFormService($formFactory, $documentManager, $mappings);

        $imageFormService->createForm(new DummyDocument(), 'dummyField');

    }

    public function mappingExceptionProvider()
    {
        return [
            [
                'mappings' => [ 'Invalid_Class' => [ 'fields' => [] ] ],
                'exceptionClass' => InvalidMappedClassException::class
            ],
            [
                'mappings' => [ DummyDocument::class => [ 'fields' => [ 'invalid_field' ] ] ],
                'exceptionClass' => InvalidMappedFieldException::class
            ],
            [
                'mappings' => [ DummyDocument::class => [ 'fields' => [ 'dummyField' => [ 'class' => 'Invalid_Class' ] ] ] ],
                'exceptionClass' => InvalidMappedClassException::class
            ],
            [
                'mappings' => [
                    DummyDocument::class => [ 'fields' => [
                        'dummyField' => [ 'class' => DummyDocument::class, 'form_type' => 'Invalid_Class' ] ]
                    ]
                ],
                'exceptionClass' => InvalidMappedClassException::class
            ],

        ];
    }

    private function getValidMappings(): array
    {
        return [
            DummyDocument::class => [
                'fields' => [
                    'dummyField' => [
                        'class' => 'stdClass'
                    ]
                ]
            ]
        ];
    }
}

class DummyDocument
{
    private $dummyField;
}