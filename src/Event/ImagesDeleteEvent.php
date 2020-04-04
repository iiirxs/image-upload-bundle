<?php

namespace IIIRxs\ImageUploadBundle\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use IIIRxs\ImageUploadBundle\Document\ImageInterface;

class ImagesDeleteEvent extends AbstractImageEvent
{
    public const NAME = 'images.delete';

    /**
     * @return Collection
     */
    public function getImageCollection(): Collection
    {
        if ($this->parent instanceof ImageInterface) {
            return new ArrayCollection([$this->parent]);
        }

        return $this->document;
    }
}