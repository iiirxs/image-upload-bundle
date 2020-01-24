<?php


namespace IIIRxs\ImageUploadBundle\Event;


use Doctrine\Common\Collections\Collection;
use IIIRxs\ImageUploadBundle\Exception\InvalidImageCollectionException;
use Symfony\Contracts\EventDispatcher\Event;

class ImagesUploadEvent extends Event
{

    use ValidateImageCollectionTrait;

    public const NAME = 'images.upload';

    /** @var Collection */
    private $imageCollection;

    /**
     * ImagesUploadEvent constructor.
     * @param Collection $imageCollection
     * @throws InvalidImageCollectionException
     */
    public function __construct(Collection $imageCollection)
    {
        if (!$this->validateCollection($imageCollection)) {
            throw new InvalidImageCollectionException();
        }
        $this->imageCollection = $imageCollection;
    }

    /**
     * @return Collection
     */
    public function getImageCollection()
    {
        return $this->imageCollection;
    }
}