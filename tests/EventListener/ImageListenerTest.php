<?php


namespace IIIRxs\ImageUploadBundle\Tests\EventListener;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\PropertyChangedListener;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\UnitOfWork;
use IIIRxs\ImageUploadBundle\Event\ImagesDeleteEvent;
use IIIRxs\ImageUploadBundle\Event\ImagesUploadEvent;
use IIIRxs\ImageUploadBundle\EventListener\ImageListener;
use IIIRxs\ImageUploadBundle\Mapping\ClassPropertyMetadata;
use IIIRxs\ImageUploadBundle\Mapping\Factory\CacheClassPropertyMetadataFactory;
use IIIRxs\ImageUploadBundle\Tests\Util\TestConstants;
use IIIRxs\ImageUploadBundle\Tests\Util\TestImage;
use IIIRxs\ImageUploadBundle\Uploader\ChainUploader;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageListenerTest extends WebTestCase
{
    private $documentManager;

    public function setUp(): void
    {
        self::bootKernel();
        $this->documentManager = self::$container->get('doctrine_mongodb.odm.document_manager');
    }

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

        $chainUploader = $this->getMockUploader();

        $chainUploader
            ->expects($this->once())
            ->method('upload')
            ->willReturn('image_listener_success')
        ;

        $listener = new ImageListener($chainUploader, $this->getMockMetadataFactory(), $this->getMockUoW());
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

        $image = new TestImage();
        $image->setPath('test.jpg');

        $config = [
            'directories' => [
                'optimized' => $targetDirs['optimized'],
                'thumbnails' => $targetDirs['thumbnails'],
            ]
        ];

        $metadata = new ClassPropertyMetadata('', '', $config);

        $metadataFactory = $this->getMockMetadataFactory();
        $metadataFactory
            ->expects($this->once())
            ->method('getMetadataFor')
            ->willReturn($metadata);

        $listener = new ImageListener($this->getMockUploader(), $metadataFactory, $this->getMockUoW());

        $event = new ImagesDeleteEvent($image);

        $listener->onImagesDelete($event);

        $this->assertFileNotExists($optimizedPath);
        $this->assertFileNotExists($thumbnailPath);
    }

    private function getMockUploader()
    {
        return $this->createMock(ChainUploader::class);
    }

    private function getMockMetadataFactory()
    {
        return $this->createMock(CacheClassPropertyMetadataFactory::class);
    }

    private function getMockUoW()
    {
        return $this->documentManager->getUnitOfWork();
    }

}