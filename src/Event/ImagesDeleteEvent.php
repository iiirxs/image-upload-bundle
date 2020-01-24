<?php

namespace IIIRxs\ImageUploadBundle\Event;

use Doctrine\Common\Collections\Collection;
use IIIRxs\ImageUploadBundle\Exception\InvalidImageCollectionException;
use Symfony\Contracts\EventDispatcher\Event;

class ImagesDeleteEvent extends Event
{

    use ValidateImageCollectionTrait;

    public const NAME = 'images.delete';

    /** @var array */
    private $directories;

    /** @var Collection */
    private $imageCollection;

    /**
     * ImagesDeleteEvent constructor.
     * @param Collection $imageCollection
     * @param mixed ...$directories
     * @throws InvalidImageCollectionException
     */
    public function __construct(Collection $imageCollection, ...$directories)
    {
        if (!$this->validateCollection($imageCollection)) {
            throw new InvalidImageCollectionException();
        }
        $this->imageCollection = $imageCollection;
        $this->directories = [];

        array_walk_recursive($directories, function ($directory) { $this->directories[] = $directory; });
    }

    /**
     * @return Collection
     */
    public function getImageCollection(): Collection
    {
        return $this->imageCollection;
    }

    /**
     * @return array
     * @return array|mixed[]
     */
    public function getDirectories()
    {
        return $this->directories;
    }

}