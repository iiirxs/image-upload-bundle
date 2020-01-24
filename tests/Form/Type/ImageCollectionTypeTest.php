<?php


namespace IIIRxs\ImageUploadBundle\Tests\Form\Type;


use IIIRxs\ImageUploadBundle\Form\Type\ImageCollectionType;
use IIIRxs\ImageUploadBundle\Tests\Util\TestConstants;
use IIIRxs\ImageUploadBundle\Tests\Util\TestImage;
use IIIRxs\ImageUploadBundle\Tests\Util\TestImageContainer;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageCollectionTypeTest extends TypeTestCase
{

    protected function getExtensions()
    {
        return [
            new HttpFoundationExtension(),
        ];
    }

    public function testSubmitValidData()
    {
        $uploadedFile = new UploadedFile(TestConstants::ORIGINAL_FILE_PATH, 'photo.jpg', 'image/jpeg', null, true);

        $formData = [
            'images' => [
                [ 'rank' => 2, 'file' => $uploadedFile ],
                [ 'rank' => 1, 'file' => $uploadedFile ],
            ]
        ];

        $objectToCompare = new TestImageContainer();

        $form = $this->factory->create(ImageCollectionType::class, $objectToCompare, [
            'data_class' => TestImageContainer::class,
            'image_data_class' => TestImage::class,
            'field_name' => 'images'
        ]);

        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());

        $container = new TestImageContainer();
        $container->addImage($this->createTestImage(2, $uploadedFile));
        $container->addImage($this->createTestImage(1, $uploadedFile));

        $this->assertEquals($container, $objectToCompare);
    }

    private function createTestImage(int $rank, UploadedFile $uploadedFile)
    {
        $image = new TestImage();
        return $image->setRank($rank)->setFile($uploadedFile);
    }

}

