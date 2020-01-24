<?php


namespace IIIRxs\ImageUploadBundle\Tests\EventListener;


use Doctrine\Common\Collections\ArrayCollection;
use IIIRxs\ImageUploadBundle\Event\ImagesDeleteEvent;
use IIIRxs\ImageUploadBundle\Event\ImagesUploadEvent;
use IIIRxs\ImageUploadBundle\EventListener\ImageListener;
use IIIRxs\ImageUploadBundle\Tests\Util\TestConstants;
use IIIRxs\ImageUploadBundle\Tests\Util\TestImage;
use IIIRxs\ImageUploadBundle\Uploader\ChainUploader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageListenerTest extends TestCase
{

    public function testGetSubscribedEvents()
    {
        $expectedEvents = [
            ImagesUploadEvent::class => 'onImagesUpload',
            ImagesDeleteEvent::class => 'onImagesDelete'
        ];
        $this->assertEquals($expectedEvents, ImageListener::getSubscribedEvents());
    }

    public function testOnImagesUpload()
    {
        $imageCollection = new ArrayCollection();
        $image = new TestImage();
        $uploadedFile = new UploadedFile(
            TestConstants::ORIGINAL_FILE_PATH,
            'photo.jpg',
            'image/jpeg',
            null,
            true
        );
        $image->setFile($uploadedFile);
        $imageCollection->add($image);

        $event = new ImagesUploadEvent($imageCollection);

        $chainUploader = $this->createMock(ChainUploader::class);

        $chainUploader
            ->expects($this->once())
            ->method('upload')
            ->willReturn('image_listener_success')
        ;

        $listener = new ImageListener($chainUploader);

        $listener->onImagesUpload($event);

        $this->assertEquals('image_listener_success', $image->getPath());
    }

    public function testOnImagesDelete()
    {
        $targetDirs = [
            'optimized' => TestConstants::OPTIMIZED_DIRECTORY_PATH,
            'thumbnails' => TestConstants::THUMBNAIL_DIRECTORY_PATH
        ];
        $path = 'test.jpg';
        $filesystem = new Filesystem();
        $filesystem->copy(TestConstants::ORIGINAL_FILE_PATH, $targetDirs['optimized'] . $path);
        $filesystem->copy(TestConstants::ORIGINAL_FILE_PATH, $targetDirs['thumbnails'] . $path);

        $optimizedPath = $targetDirs['optimized'] . $path;
        $thumbnailPath = $targetDirs['thumbnails'] . $path;
        $this->assertFileExists($optimizedPath);
        $this->assertFileExists($thumbnailPath);

        $imageCollection = new ArrayCollection();
        $image = new TestImage();
        $image->setPath('test.jpg');
        $imageCollection->add($image);


        $event = new ImagesDeleteEvent($imageCollection, $targetDirs);

        $listener = new ImageListener($this->createMock(ChainUploader::class));

        $listener->onImagesDelete($event);

        $this->assertFileNotExists($optimizedPath);
        $this->assertFileNotExists($thumbnailPath);
    }

}