<?php


namespace IIIRxs\ImageUploadBundle\Event;


use Doctrine\Common\Collections\Collection;
use IIIRxs\ImageUploadBundle\Document\AbstractImage;

trait ValidateImageCollectionTrait
{

    /**
     * @param Collection $collection
     * @return bool
     */
    private function validateCollection(Collection $collection): bool
    {
        return $collection->forAll(function ($index, $element) {
            return $element instanceof AbstractImage;
        });
    }

}