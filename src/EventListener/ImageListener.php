<?php


namespace IIIRxs\ImageUploadBundle\EventListener;

use IIIRxs\ImageUploadBundle\Document\AbstractImage;
use IIIRxs\ImageUploadBundle\Event\ImagesDeleteEvent;
use IIIRxs\ImageUploadBundle\Event\ImagesUploadEvent;
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

    public function __construct(ChainUploader $uploader)
    {
        $this->uploader = $uploader;
    }

    public static function getSubscribedEvents()
    {
        return [
            ImagesUploadEvent::class => 'onImagesUpload',
            ImagesDeleteEvent::class => 'onImagesDelete'
        ];
    }

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

    public function onImagesDelete(ImagesDeleteEvent $event)
    {
        $callable = function (AbstractImage $image) {
            return !empty($image->getPath());
        };

        $images = $event->getImageCollection()->filter($callable);

        foreach ($images as $image) {
            $this->deleteImage($image, $event->getDirectories());
        }
    }

    protected function uploadImage(AbstractImage $image)
    {
        $this->uploader->selectUploader($image);
        $filename = $this->uploader->upload($image->getFile());

        $image->setPath($filename);
        $image->setFile(null);
    }

    protected function deleteImage(AbstractImage $image, array $directories)
    {
        $filesystem = new Filesystem();

        foreach ($directories as $directory) {
            $filesystem->remove($directory . '/' . $image->getPath());
        }
    }

}