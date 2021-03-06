<?php

namespace IIIRxs\ImageUploadBundle\Tests\Util;

use Doctrine\Common\Collections\ArrayCollection;
use IIIRxs\ImageUploadBundle\Document\AbstractImage;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Filesystem\Filesystem;

class TestConstants {
    const UPLOAD_DIR = '/Users/iiirxs/developing/ImageUploadBundle/tests/files/';
    const ORIGINAL_FILE_PATH = self::UPLOAD_DIR . 'high-resolution.jpg';
    const OPTIMIZED_DIRECTORY_PATH = self::UPLOAD_DIR . 'optimized/';
    const THUMBNAIL_DIRECTORY_PATH = self::UPLOAD_DIR . 'thumbnails/';

    static function getUploadableFilePath()
    {
        $filesystem = new Filesystem();
        $uid = self::UPLOAD_DIR . uniqid() . '.jpg';
        $filesystem->copy(self::ORIGINAL_FILE_PATH, $uid);
        return $uid;
    }
}

/**
 * @MongoDB\EmbeddedDocument
 */
class TestImage extends AbstractImage
{
}

/**
 * @MongoDB\Document(collection="TestImageContainer")
 */
class TestImageContainer
{
    protected $id;

    /**
     * @MongoDB\EmbedMany(targetDocument=TestImage::class, strategy="atomicSetArray")
     */
    protected $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getImages()
    {
        return $this->images;
    }

    public function addImage($image)
    {
        $this->images[] = $image;
    }

    public function setImages($images)
    {
        $this->images = $images;
    }
}