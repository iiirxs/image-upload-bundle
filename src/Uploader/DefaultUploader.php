<?php


namespace IIIRxs\ImageUploadBundle\Uploader;


class DefaultUploader extends AbstractUploader
{

    function __construct(string $imagesDir, int $maxThumbnailDimension)
    {
        parent::__construct($imagesDir, $maxThumbnailDimension);
    }
}