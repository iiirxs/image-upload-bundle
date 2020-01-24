<?php

namespace IIIRxs\ImageUploadBundle\DependencyInjection;

use IIIRxs\ImageUploadBundle\Controller\ImageController;
use IIIRxs\ImageUploadBundle\Uploader\ChainUploader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class IIIRxsImageUploadExtension extends Extension implements CompilerPassInterface
{

    const MAX_THUMBNAIL_PARAMETER = 'iiirxs.max.dimension.thumbnail';
    const IMAGE_DIR_PARAMETER = 'iiirxs.image.upload.dir';
    const CACHE_PROVIDER_PARAMETER = 'iiirxs.cache.provider';

    const CACHE_CLASS_PROPERTY_METADATA_FACTORY_ID = 'iiirxs_image_upload.mapping.factory.cache_class_property_metadata_factory';
    const CLASS_PROPERTY_METADATA_FACTORY_ID = 'iiirxs_image_upload.mapping.factory.class_property_metadata_factory';
    const PARAM_CONVERTER_ID = 'iiirxs_image_upload.param_converter';
    /**
     * Loads a specific configuration.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load('services.xml');

        $this->addAnnotatedClassesToCompile([ ImageController::class ]);

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $metadataFactoryDefinition = $container->findDefinition(self::CLASS_PROPERTY_METADATA_FACTORY_ID);
        $paramConverterDefinition = $container->findDefinition(self::PARAM_CONVERTER_ID);

        $metadataFactoryDefinition->setArgument(1, $config['mappings']);
        $paramConverterDefinition->setArgument(1, $config['mappings']);

        $container->setParameter(self::MAX_THUMBNAIL_PARAMETER, $config['max_thumbnail_dimension']);
        $container->setParameter(self::CACHE_PROVIDER_PARAMETER, $config['cache_provider']);

        $defaultImageUploadDir = $config['default_image_upload_dir'] ?? null;
        if (!empty($defaultImageUploadDir)) {
            $container->setParameter(self::IMAGE_DIR_PARAMETER, $config['default_image_upload_dir']);
        }
        $metadataFactoryDefinition->setArgument(2, $defaultImageUploadDir);

    }

    public function getAlias()
    {
        return 'iiirxs_image_upload';
    }

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(ChainUploader::class)) {
            return;
        }

        $cachePoolId = $container->getParameter(self::CACHE_PROVIDER_PARAMETER);
        $cachePoolDefinition = $container->getDefinition($cachePoolId);

        $cacheMetadataFactoryDefinition = $container->findDefinition(self::CACHE_CLASS_PROPERTY_METADATA_FACTORY_ID);
        $cacheMetadataFactoryDefinition->setArgument(1, $cachePoolDefinition);

        $chainUploaderDefinition = $container->findDefinition(ChainUploader::class);
        $taggedServices = $container->findTaggedServiceIds('image.uploader');

        foreach ($taggedServices as $id => $tags) {
            $chainUploaderDefinition->addMethodCall('addUploader', [ new Reference($id) ]);
        }
    }

}