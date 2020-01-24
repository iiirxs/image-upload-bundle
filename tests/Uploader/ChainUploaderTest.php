<?php


namespace IIIRxs\ImageUploadBundle\Tests\Uploader;


use IIIRxs\ImageUploadBundle\Exception\InvalidSelectedImageUploaderException;
use IIIRxs\ImageUploadBundle\Uploader\ChainUploader;
use IIIRxs\ImageUploadBundle\Uploader\ImageUploaderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ChainUploaderTest extends TestCase
{

    public function testSupports()
    {
        $chainUploader = new ChainUploader();

        $uploader = $this->createMock(ImageUploaderInterface::class);
        $uploader
            ->expects($this->once())
            ->method('supports')
            ->willReturn(true);

        $chainUploader->addUploader($uploader);

        $this->assertTrue($chainUploader->supports(new \stdClass()));
    }

    public function testSelectUploader()
    {
        $chainUploader = new ChainUploader();

        $uploader = $this->createMock(ImageUploaderInterface::class);
        $uploader
            ->expects($this->once())
            ->method('supports')
            ->with($this->equalTo(new \stdClass()))
            ->willReturn(true)
        ;

        $uploader
            ->expects($this->once())
            ->method('upload')
            ->willReturn('success')
        ;

        $chainUploader->addUploader($uploader);

        $chainUploader->selectUploader(new \stdClass());

        $this->assertEquals('success', $chainUploader->upload($this->createMock(UploadedFile::class)));

    }

    public function testUploadWithoutSelectedUploader()
    {
        $this->expectException(InvalidSelectedImageUploaderException::class);

        $chainUploader = new ChainUploader();
        $chainUploader->upload($this->createMock(UploadedFile::class));
    }
}