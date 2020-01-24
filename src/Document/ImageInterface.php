<?php


namespace IIIRxs\ImageUploadBundle\Document;


use Symfony\Component\HttpFoundation\File\UploadedFile;

interface ImageInterface
{

    public function setFile(?UploadedFile $file);
    public function getFile(): ?UploadedFile;

    public function completeUpload(string $filename);

}