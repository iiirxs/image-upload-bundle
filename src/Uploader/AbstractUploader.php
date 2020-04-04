<?php

namespace IIIRxs\ImageUploadBundle\Uploader;

use IIIRxs\ImageUploadBundle\DependencyInjection\Configuration;
use IIIRxs\ImageUploadBundle\Exception\InvalidUploadTargetDirException;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractUploader implements ImageUploaderInterface
{

    const THUMBNAIL_KEY = 'thumbnails';

    /** @var string|string[] */
    protected $targetDir;

    /** @var int */
    private $maxThumbnailDimension;

    public function __construct(int $maxThumbnailDimension = null)
   	{
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

        if (!$this->validateTargetDir()) {
            throw new InvalidUploadTargetDirException('Target directory not set');
        }
        if (is_array($this->targetDir)) {
            $this->uploadOptimized($file, $filename);
            $this->uploadThumbnail($file, $filename);
        } else {
            $file->move($this->targetDir, $filename);
        }

        return $filename;
    }

    /**
     * @param $targetDir
     */
    public function setTargetDir($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    /**
     * @param $document
     * @param $parent
     * @param $propertyName
     */
    abstract public function select($document, $parent, $propertyName);

    /**
     * By default supports no class
     * @param $document
     * @param $parent
     * @param $propertyName
     * @return bool
     */
    abstract public function supports($document, $parent, $propertyName): bool;

    /**
     * @return bool
     */
    protected function validateTargetDir(): bool
    {
        $validKeys = [ Configuration::OPTIMIZED_KEY, Configuration::THUMBNAILS_KEY ];
        return !empty($this->targetDir)
            && !(is_array($this->targetDir)
            && !empty(array_diff(array_keys($this->targetDir), $validKeys)));
    }

    /**
     * @return string
     */
    protected function getOptimizedDir(): string
    {
        return rtrim($this->targetDir[Configuration::OPTIMIZED_KEY], '/\\');
    }

    /**
     * @return string
     */
    protected function getThumbnailsDir(): string
    {
        return rtrim($this->targetDir[static::THUMBNAIL_KEY], '/\\');
    }

    /**
     * @param UploadedFile $imageFile
     * @param string $filename
     */
    protected function uploadOptimized(UploadedFile $imageFile, string $filename)
    {
        $optimizerChain = OptimizerChainFactory::create();
        $optimizerChain->optimize($imageFile->getPathname(), $this->getOptimizedDir() . '/' . $filename);
    }

    /**
     * @param UploadedFile $imageFile
     * @param string $filename
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