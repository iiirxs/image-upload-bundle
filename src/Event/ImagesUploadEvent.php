<?php


namespace IIIRxs\ImageUploadBundle\Event;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use IIIRxs\ImageUploadBundle\Document\ImageInterface;
use IIIRxs\ImageUploadBundle\Exception\InvalidImageCollectionException;
use PHPUnit\Framework\StaticAnalysis\HappyPath\AssertNotInstanceOf\A;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\EventDispatcher\Event;

class ImagesUploadEvent extends Event
{

    use ValidateImageCollectionTrait;

    public const NAME = 'images.upload';

    /** @var Collection */
    private $imageCollection;

    private $document;
    private $parent;
    private $propertyName;

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

    /**
     * @return Collection
     */
    public function getImageCollection()
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