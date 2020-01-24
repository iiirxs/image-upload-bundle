<?php


namespace IIIRxs\ImageUploadBundle\Tests\Form\Type;

use IIIRxs\ImageUploadBundle\Form\Type\ImageType;
use IIIRxs\ImageUploadBundle\Tests\Util\TestConstants;
use IIIRxs\ImageUploadBundle\Tests\Util\TestImage;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageTypeTest extends TypeTestCase
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
            'rank' => 1,
            'file' => $uploadedFile,
        ];

        $objectToCompare = new TestImage();
        $form = $this->factory->create(ImageType::class, $objectToCompare);

        $object = new TestImage();
        $object
            ->setRank(1)
            ->setFile($uploadedFile)
        ;

        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());

        $this->assertEquals($object, $objectToCompare);
    }

}