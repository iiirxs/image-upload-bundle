<?php


namespace IIIRxs\ImageUploadBundle\EventListener;

use IIIRxs\ImageUploadBundle\Document\ImageInterface;
use IIIRxs\ImageUploadBundle\Event\ImagesDeleteEvent;
use IIIRxs\ImageUploadBundle\Event\ImagesUploadEvent;
use IIIRxs\ImageUploadBundle\Exception\InvalidSelectedImageUploaderException;
use IIIRxs\ImageUploadBundle\Mapping\Factory\CacheClassPropertyMetadataFactory;
use IIIRxs\ImageUploadBundle\Uploader\ChainUploader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;

class ImageListener implements EventSubscriberInterface
{
    /** @var ChainUploader */
    private $uploader;

    /** @var CacheClassPropertyMetadataFactory */
    private $metadataFactory;

    /**
     * ImageListener constructor.
     * @param ChainUploader $uploader
     * @param CacheClassPropertyMetadataFactory $metadataFactory
     */
    public function __construct(
        ChainUploader $uploader,
        CacheClassPropertyMetadataFactory $metadataFactory
    )
    {
        $this->uploader = $uploader;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ImagesUploadEvent::class => 'onImagesUpload',
            ImagesDeleteEvent::class => 'onImagesDelete'
        ];
    }

    /**
     * @param ImagesUploadEvent $event
     * @throws InvalidSelectedImageUploaderException
     */
    public function onImagesUpload(ImagesUploadEvent $event)
    {
        $images = $event->getImageCollection();
        foreach ($images as $image) {
            $this->uploadImage($image, $event->getParent(), $event->getPropertyName());
        }
    }

    /**
     * @param ImagesDeleteEvent $event
     */
    public function onImagesDelete(ImagesDeleteEvent $event)
    {
        $images = $event->getImageCollection();
        foreach ($images as $image) {
            $this->deleteImage($image, $event->getParent(), $event->getPropertyName());
        }
    }

    /**
     * @param ImageInterface $image
     * @param $parent
     * @param string $propertyPath
     * @throws InvalidSelectedImageUploaderException
     */
    protected function uploadImage(ImageInterface $image, $parent, ?string $propertyPath)
    {
        $this->uploader->select($image, $parent, $propertyPath);
        $filename = $this->uploader->upload($image->getFile());

        $image->completeUpload($filename);
    }

    protected function deleteImage(ImageInterface $image, $parent, ?string $propertyPath)
    {
        $filesystem = new Filesystem();

        $metadata = $this->metadataFactory->getMetadataFor($parent, $propertyPath);

        $directories = $metadata->getDirectories();
        $directories = is_array($directories) ? $directories : [$directories];
        foreach ($directories as $directory) {
            if (!empty($image->getPath())) {
                $filesystem->remove($directory . '/' . $image->getPath());
            }
        }
    }

}