<?php


namespace IIIRxs\ImageUploadBundle\Tests\Uploader;


use IIIRxs\ImageUploadBundle\Exception\InvalidUploadTargetDirException;
use IIIRxs\ImageUploadBundle\Tests\Util\TestConstants;
use IIIRxs\ImageUploadBundle\Uploader\AbstractUploader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AbstractUploaderTest extends TestCase
{

    const MAX_THUMBNAIL_DIMENSION = 600;

    private $uploadedFile;
    private $filesToDelete = [];

    public function setUp(): void
    {
        $filesystem = new Filesystem();
        $filesystem->copy(TestConstants::ORIGINAL_FILE_PATH, TestConstants::UPLOADED_FILE_PATH);

        $this->uploadedFile = new UploadedFile(
            TestConstants::UPLOADED_FILE_PATH,
            'photo.jpg',
            'image/jpeg',
            null,
            true
        );

        $this->filesToDelete[] = $this->uploadedFile->getRealPath();
    }

    public function tearDown(): void
    {
        $filesystem = new Filesystem();
        foreach ($this->filesToDelete as $file) {
            if ($filesystem->exists($file)) {
                $filesystem->remove($file);
            }
        }

        $this->filesToDelete = [];
    }
//
//    public function testCreateFilename()
//    {
//        $filename = AbstractUploader::createFilename($this->uploadedFile);
//
//        $this->assertCount(2, explode('.', $filename));
//
//        list($name, $extension) = explode('.', $filename);
//
//        $this->assertEquals('jpeg', $extension);
//
//        /** Asserts name is valid md5 hash */
//        $this->assertRegExp('/^[a-f0-9]{32}$/', $name);
//    }
//
//    public function testUpload()
//    {
//        $uploader = new TestUploader();
//        $uploader->setTargetDir(TestConstants::UPLOAD_DIR);
//        $filename = $uploader->upload($this->uploadedFile);
//
//        $this->assertFileExists(TestConstants::UPLOAD_DIR . $filename);
//
//        $this->filesToDelete[] = TestConstants::UPLOAD_DIR . $filename;
//    }
//
//    public function testUploadWithThumbnail()
//    {
//        $targetDirs = [
//            'optimized' => TestConstants::OPTIMIZED_DIRECTORY_PATH,
//            'thumbnails' => TestConstants::THUMBNAIL_DIRECTORY_PATH
//        ];
//
//        $uploader = new TestUploader(self::MAX_THUMBNAIL_DIMENSION);
//        $uploader->setTargetDir($targetDirs);
//
//        $filename = $uploader->upload($this->uploadedFile);
//
//        foreach ($targetDirs as $targetDir) {
//            $this->assertFileExists($targetDir . $filename);
//            $this->filesToDelete[] = $targetDir . $filename;
//        }
//
//        list($width, $height) = getimagesize($targetDirs['thumbnails'] . $filename);
//
//        $this->assertEquals(self::MAX_THUMBNAIL_DIMENSION, max($width, $height));
//    }

    /**
     * @param $targetDir
     * @throws InvalidUploadTargetDirException
     * @dataProvider invalidTargetDirProvider
     */
    public function testUploadWithInvalidTargetDirs($targetDir)
    {
        $this->expectException(InvalidUploadTargetDirException::class);

        $uploader = new TestUploader(self::MAX_THUMBNAIL_DIMENSION);
        $uploader->setTargetDir($targetDir);
        $uploader->upload($this->uploadedFile);
    }

    public function invalidTargetDirProvider()
    {
        return [
            [ [ 'random_key' => TestConstants::OPTIMIZED_DIRECTORY_PATH, 'thumbnails' => TestConstants::THUMBNAIL_DIRECTORY_PATH ] ],
            [ [ 'optimized' => TestConstants::OPTIMIZED_DIRECTORY_PATH, 'random_key' => TestConstants::THUMBNAIL_DIRECTORY_PATH ] ],
            [ null ]
        ];
    }

}

class TestUploader extends AbstractUploader
{

    public function supports($document, $parent, $propertyName): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function select($document, $parent, $propertyName)
    {
        // TODO: Implement select() method.
    }
}