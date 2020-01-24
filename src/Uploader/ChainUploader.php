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
     * @throws InvalidImageUploaderClassException
     */
    public function selectUploader($document) {

        foreach ($this->uploaders as $uploader) {
            if (!$uploader instanceof ImageUploaderInterface) {
                throw new InvalidImageUploaderClassException();
            }

            if ($uploader->supports($document)) {
                $this->selectedUploader = $uploader;
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
     * @return bool
     */
    public function supports($document): bool
    {
        $supports = false;

        foreach ($this->uploaders as $uploader) {
            $supports = $supports || $uploader->supports($document);
        }

        return $supports;
    }
}