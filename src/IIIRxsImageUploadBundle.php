<?php

namespace IIIRxs\ImageUploadBundle;

use IIIRxs\ImageUploadBundle\DependencyInjection\IIIRxsImageUploadExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IIIRxsImageUploadBundle extends Bundle
{

    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new IIIRxsImageUploadExtension();
        }
        return $this->extension;
    }
}