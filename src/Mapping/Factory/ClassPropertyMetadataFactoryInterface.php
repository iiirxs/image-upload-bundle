<?php


namespace IIIRxs\ImageUploadBundle\Mapping\Factory;


use IIIRxs\ImageUploadBundle\Mapping\ClassPropertyMetadataInterface;

interface ClassPropertyMetadataFactoryInterface
{

    public function getMetadataFor($class, $property): ClassPropertyMetadataInterface;

    public function hasMetadataFor($class, $property): bool;

}