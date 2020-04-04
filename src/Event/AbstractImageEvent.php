<?php


namespace IIIRxs\ImageUploadBundle\Event;


use Doctrine\Common\Collections\Collection;
use IIIRxs\ImageUploadBundle\Document\ImageInterface;
use IIIRxs\ImageUploadBundle\Exception\InvalidImageCollectionException;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractImageEvent extends Event
{

    protected $document;
    protected $parent;

    /** @var string|null */
    protected $propertyName;

    /**
     * ImagesUploadEvent constructor.
     * @param $document
     * @param $parent
     * @param $propertyName
     * @throws InvalidImageCollectionException
     */
    public function __construct($document, $parent, $propertyName)
    {
        if (!$parent instanceof ImageInterface && !$this->validateCollection($document)) {
            throw new InvalidImageCollectionException();
        }
        $this->document = $document;
        $this->parent = $parent;
        $this->propertyName = $propertyName;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getPropertyName()
    {
        return $this->propertyName;
    }

    abstract function getImageCollection(): Collection;

    /**
     * @param Collection $collection
     * @return bool
     */
    protected function validateCollection($collection): bool
    {
        return $collection instanceof Collection && $collection->forAll(function ($index, $element) {
            return $element instanceof ImageInterface;
        });
    }

}