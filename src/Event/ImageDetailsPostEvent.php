<?php

namespace IIIRxs\ImageUploadBundle\Event;

use Doctrine\Common\Collections\Collection;
use IIIRxs\ImageUploadBundle\Document\AbstractImage;
use Symfony\Contracts\EventDispatcher\Event;

class ImageDetailsPostEvent extends Event
{

    public const NAME = 'image.details.post';
    private $imageContainer;

    /**
     * ImageDetailsPostEvent constructor.
     * @param $imageContainer
     */
    public function __construct($imageContainer)
    {
        $this->imageContainer = $imageContainer;
    }

    /**
     * @return mixed
     */
    public function getImageContainer()
    {
        return $this->imageContainer;
    }

}