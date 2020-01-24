<?php

namespace IIIRxs\ImageUploadBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractImage implements ImageInterface
{

    protected $file;
    
    /**
     * @MongoDB\Field(type="string")
     */
    protected $path;
    
    /**
     * @MongoDB\Field(type="int")
     */
    protected $rank;

    /**
     * @param string $filename
     */
    public function completeUpload(string $filename)
    {
        $this->setPath($filename);
        $this->setFile(null);
    }

    /**
     * @param UploadedFile|null $file
     * @return AbstractImage
     */
    public function setFile(?UploadedFile $file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return UploadedFile|null
     */
    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * @param string|null $path
     * @return AbstractImage
     */
    public function setPath(?string $path): self 
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param $rank
     * @return AbstractImage
     */
    public function setRank($rank): self
    {
        if (!is_null($rank)) {
            $this->rank = (int) $rank;
        }

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRank(): ?int
    {
        return $this->rank;
    }

}
