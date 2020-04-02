<?php


namespace IIIRxs\ImageUploadBundle\EventListener;

use Doctrine\ODM\MongoDB\UnitOfWork;
use IIIRxs\ImageUploadBundle\Document\AbstractImage;
use IIIRxs\ImageUploadBundle\Document\ImageInterface;
use IIIRxs\ImageUploadBundle\Event\ImagesDeleteEvent;
use IIIRxs\ImageUploadBundle\Event\ImagesUploadEvent;
use IIIRxs\ImageUploadBundle\Exception\InvalidImageUploaderClassException;
use IIIRxs\ImageUploadBundle\Exception\InvalidSelectedImageUploaderException;
use IIIRxs\ImageUploadBundle\Mapping\Factory\CacheClassPropertyMetadataFactory;
use IIIRxs\ImageUploadBundle\Uploader\ChainUploader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageListener implements EventSubscriberInterface
{
    /**
     * @var ChainUploader
     */
    private $uploader;

    /**
     * @var CacheClassPropertyMetadataFactory
     */
    private $metadataFactory;

    /**
     * @var UnitOfWork
     */
    private $unitOfWork;

    /**
     * ImageListener constructor.
     * @param ChainUploader $uploader
     * @param CacheClassPropertyMetadataFactory $metadataFactory
     * @param UnitOfWork $unitOfWork
     */
    public function __construct(
        ChainUploader $uploader,
        CacheClassPropertyMetadataFactory $metadataFactory,
        UnitOfWork $unitOfWork
    )
    {
        $this->uploader = $uploader;
        $this->metadataFactory = $metadataFactory;
        $this->unitOfWork = $unitOfWork;
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
        $images = $event->getUploadableCollection();
        foreach ($images as $image) {
            $this->uploadImage($image, $event->getParent(), $event->getPropertyName());
        }

    }

    /**
     * @param ImagesDeleteEvent $event
     */
    public function onImagesDelete(ImagesDeleteEvent $event)
    {
        $this->deleteImage($event->getImage());
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

    protected function deleteImage(AbstractImage $image)
    {
        $filesystem = new Filesystem();

        list($mapping, $parent, $propertyPath) = $this->unitOfWork->getParentAssociation($image);

        $metadata = $this->metadataFactory->getMetadataFor($parent, $mapping['fieldName']);

        foreach ($metadata->getDirectories() as $directory) {
            $filesystem->remove($directory . '/' . $image->getPath());
        }
    }

}