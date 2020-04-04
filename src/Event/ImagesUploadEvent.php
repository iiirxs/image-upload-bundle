<?php


namespace IIIRxs\ImageUploadBundle\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use IIIRxs\ImageUploadBundle\Document\ImageInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImagesUploadEvent extends AbstractImageEvent
{
    public const NAME = 'images.upload';

    /**
     * @return Collection
     */
    public function getImageCollection(): Collection
    {
        if ($this->parent instanceof ImageInterface) {
            return new ArrayCollection([$this->parent]);
        }

        $callable = function (ImageInterface $image) {
            return $image->getFile() instanceof UploadedFile;
        };

        return $this->document->filter($callable);
    }
}