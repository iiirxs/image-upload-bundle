<?php


namespace IIIRxs\ImageUploadBundle\Mapping;


interface ClassPropertyMetadataInterface
{

    public function getClassName();
    public function getPropertyName();
    public function getEntryType();
    public function getImageClass();
    public function getDirectories();

}