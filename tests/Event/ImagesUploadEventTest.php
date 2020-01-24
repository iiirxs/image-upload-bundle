<?php


namespace IIIRxs\ImageUploadBundle\Tests\Event;


use Doctrine\Common\Collections\ArrayCollection;
use IIIRxs\ImageUploadBundle\Event\ImagesUploadEvent;
use IIIRxs\ImageUploadBundle\Exception\InvalidImageCollectionException;
use PHPUnit\Framework\TestCase;

class ImagesUploadEventTest extends TestCase
{

    public function testExceptionOnInvalidCollection()
    {
        $this->expectException(InvalidImageCollectionException::class);

        $invalidCollection = new ArrayCollection(['test']);

        new ImagesUploadEvent($invalidCollection, null, null);
    }

}