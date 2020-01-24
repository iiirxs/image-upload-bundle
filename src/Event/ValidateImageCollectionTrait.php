<?php


namespace IIIRxs\ImageUploadBundle\Event;


use Doctrine\Common\Collections\Collection;
use IIIRxs\ImageUploadBundle\Document\AbstractImage;
use IIIRxs\ImageUploadBundle\Document\ImageInterface;

trait ValidateImageCollectionTrait
{

    /**
     * @param Collection $collection
     * @return bool
     */
    private function validateCollection($collection): bool
    {
        return $collection instanceof Collection && $collection->forAll(function ($index, $element) {
            return $element instanceof ImageInterface;
        });
    }

}