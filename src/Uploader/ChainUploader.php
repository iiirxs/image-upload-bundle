<?php

namespace IIIRxs\ImageUploadBundle\Uploader;


use IIIRxs\ImageUploadBundle\Exception\InvalidSelectedImageUploaderException;
use IIIRxs\ImageUploadBundle\Exception\InvalidImageUploaderClassException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ChainUploader implements ImageUploaderInterface
{

    /**
     * @var ImageUploaderInterface[]
     */
    private $uploaders;

    /**
     * @var ImageUploaderInterface
     */
    private $selectedUploader = null;

    public function __construct()
    {
        $this->uploaders = [];
    }

    /**
     * @param ImageUploaderInterface $uploader
     */
    public function addUploader(ImageUploaderInterface $uploader)
    {
        $this->uploaders[] = $uploader;
    }

    /**
     * @param $document
     * @param $parent
     * @param $propertyName
     */
    public function select($document, $parent, $propertyName) {

        foreach ($this->uploaders as $uploader) {
            if ($uploader->supports($document, $parent, $propertyName)) {
                $this->selectedUploader = $uploader;
                $uploader->select($document, $parent, $propertyName);
                break;
            }
        }
    }

    /**
     * @param UploadedFile $file
     * @return string
     * @throws InvalidSelectedImageUploaderException
     */
    public function upload(UploadedFile $file): string
    {
        if (!$this->selectedUploader instanceof ImageUploaderInterface) {
            throw new InvalidSelectedImageUploaderException();
        }

        return $this->selectedUploader->upload($file);
    }

    /**
     * @param $document
     * @param $parent
     * @param $propertyName
     * @return bool
     */
    public function supports($document, $parent, $propertyName): bool
    {
        foreach ($this->uploaders as $uploader) {
            if ($uploader->supports($document, $parent, $propertyName)) {
                return true;
            }
        }
        return false;
    }
}