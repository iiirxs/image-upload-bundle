<?php


namespace IIIRxs\ImageUploadBundle\Tests\Mapping;


use IIIRxs\ImageUploadBundle\Exception\InvalidClassException;
use IIIRxs\ImageUploadBundle\Mapping\ClassPropertyMetadata;
use PHPUnit\Framework\TestCase;

class ClassPropertyMetadataTest extends TestCase
{

    public function testSetInvalidClass()
    {
        $this->expectException(InvalidClassException::class);

        new ClassPropertyMetadata('class', 'field', ['class' => 'invalid_class']);
    }

    public function testSetInvalidEntryType()
    {
        $this->expectException(InvalidClassException::class);

        new ClassPropertyMetadata('class', 'field', ['entry_type' => 'invalid_class']);
    }
}