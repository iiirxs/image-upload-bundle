<?php

namespace IIIRxs\ImageUploadBundle\Event;

use IIIRxs\ImageUploadBundle\Document\AbstractImage;
use Symfony\Contracts\EventDispatcher\Event;

class ImagesDeleteEvent extends Event
{

    public const NAME = 'images.delete';

    /**
     * @var AbstractImage
     */
    private $image;

    /**
     * ImagesDeleteEvent constructor.
     * @param AbstractImage $image
     */
    public function __construct(AbstractImage $image)
    {
        $this->image = $image;
    }

    /**
     * @return AbstractImage
     */
    public function getImage(): AbstractImage
    {
        return $this->image;
    }

}