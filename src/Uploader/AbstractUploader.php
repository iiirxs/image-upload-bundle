<?php

namespace IIIRxs\ImageUploadBundle\Uploader;

use IIIRxs\ImageUploadBundle\Exception\InvalidUploadTargetDirException;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractUploader implements ImageUploaderInterface
{
    /**
     * @var string|string[]
     */
    protected $targetDir;

    /** @var int */
    private $maxThumbnailDimension;

   	public function __construct($targetDir, int $maxThumbnailDimension = null)
   	{
        $this->targetDir = $targetDir;
        $this->maxThumbnailDimension = $maxThumbnailDimension;
    }

    /**
     * @param UploadedFile $file
     * @return string
     * @throws InvalidUploadTargetDirException
     */
    public function upload(UploadedFile $file): string
    {
        $filename = static::createFilename($file);

        if (is_array($this->targetDir)) {
            $this->uploadOptimized($file, $filename);
            $this->uploadThumbnail($file, $filename);
        } else {
            $file->move($this->targetDir, $filename);
        }

        return $filename;
    }

    /**
     * @return mixed
     * @throws InvalidUploadTargetDirException
     */
    protected function getOptimizedDir()
    {
        if (!isset($this->targetDir['optimized'])) {
            throw new InvalidUploadTargetDirException();
        }
        return rtrim($this->targetDir['optimized'], '/\\');
    }

    /**
     * @return mixed
     * @throws InvalidUploadTargetDirException
     */
    protected function getThumbnailsDir()
    {
        if (!isset($this->targetDir['thumbnails'])) {
            throw new InvalidUploadTargetDirException();
        }
        return rtrim($this->targetDir['thumbnails'], '/\\');
    }

    /**
     * @param UploadedFile $imageFile
     * @param string $filename
     * @throws InvalidUploadTargetDirException
     */
    protected function uploadOptimized(UploadedFile $imageFile, string $filename)
    {
        $optimizerChain = OptimizerChainFactory::create();
        $optimizerChain->optimize($imageFile->getPathname(), $this->getOptimizedDir() . '/' . $filename);
    }

    /**
     * @param UploadedFile $imageFile
     * @param string $filename
     * @throws InvalidUploadTargetDirException
     */
    protected function uploadThumbnail(UploadedFile $imageFile, string $filename): void
    {
        $originalImage = $this->loadImageFile($imageFile);

        $thumbnail = $this->resizeThumbnail($originalImage);

        $this->saveThumbnail($imageFile, $thumbnail, $filename);
    }

    /**
     * @param UploadedFile $imageFile
     * @return mixed
     */
    protected function loadImageFile(UploadedFile $imageFile)
    {
        $mimeType = $imageFile->getClientMimeType();
        $callback = 'imagecreatefrom' . explode('/', $mimeType)[1];
        return call_user_func($callback, $imageFile);
    }

    /**
     * @param UploadedFile $file
     * @param $thumbnail
     * @param $filename
     * @throws InvalidUploadTargetDirException
     */
    protected function saveThumbnail(UploadedFile $file, $thumbnail, $filename)
    {
        $mimeType = $file->getClientMimeType();
        $callback = 'image' . explode('/', $mimeType)[1];

        call_user_func($callback, $thumbnail, $this->getThumbnailsDir() . '/' . $filename);
    }

    /**
     * @param $originalImage
     * @return false|resource
     */
    protected function resizeThumbnail($originalImage)
    {
        $originalWidth = imagesx($originalImage);
        $originalHeight = imagesy($originalImage);

        $percent = $this->maxThumbnailDimension / max($originalHeight, $originalWidth);

        $newWidth = $originalWidth * $percent;
        $newHeight = $originalHeight * $percent;

        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        imagecopyresampled(
            $newImage, $originalImage, 0, 0, 0, 0,
            $newWidth, $newHeight, $originalWidth, $originalHeight
        );

        return $newImage;
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    public static function createFilename(UploadedFile $file): string
    {
        return md5(uniqid()) . '.' . $file->guessExtension();
    }

}