<?php


namespace IIIRxs\ImageUploadBundle\EventListener;

use Doctrine\ODM\MongoDB\UnitOfWork;
use IIIRxs\ImageUploadBundle\Document\AbstractImage;
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
     * @throws InvalidImageUploaderClassException
     * @throws InvalidSelectedImageUploaderException
     */
    public function onImagesUpload(ImagesUploadEvent $event)
    {
        $callable = function (AbstractImage $image) {
            return $image->getFile() instanceof UploadedFile;
        };

        $images = $event->getImageCollection()->filter($callable);
        foreach ($images as $image) {
            $this->uploadImage($image);
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
     * @param AbstractImage $image
     * @throws InvalidImageUploaderClassException
     * @throws InvalidSelectedImageUploaderException
     */
    protected function uploadImage(AbstractImage $image)
    {
        list($mapping, $parent, $propertyPath) = $this->unitOfWork->getParentAssociation($image);

        $this->uploader->select($image, $parent, $propertyPath);
        $filename = $this->uploader->upload($image->getFile());

        $image->setPath($filename);
        $image->setFile(null);
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