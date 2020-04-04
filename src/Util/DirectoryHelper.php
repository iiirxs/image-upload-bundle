<?php


namespace IIIRxs\ImageUploadBundle\Util;


use IIIRxs\ImageUploadBundle\DependencyInjection\Configuration;

class DirectoryHelper
{

    static function getDirectoriesFromConfiguration(array $config, string $key)
    {
        if (isset($config[$key])) {
            $directories = $config[$key];
            if (!isset($directories[Configuration::THUMBNAILS_KEY])) {
                return $directories[Configuration::OPTIMIZED_KEY];
            }
            return $directories;
        }
        return null;
    }

}