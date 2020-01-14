<?php

namespace IIIRxs\ImageUploadBundle\DependencyInjection;

use IIIRxs\ImageUploadBundle\Controller\ImageController;
use IIIRxs\ImageUploadBundle\Form\ImageFormService;
use IIIRxs\ImageUploadBundle\Uploader\ChainUploader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class IIIRxsImageUploadExtension extends Extension implements CompilerPassInterface
{

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

        $definition = $container->findDefinition(ImageFormService::class);
        $definition->setArgument(2, $config['mappings']);

        $definition = $container->findDefinition('iiirxs_image_upload.param_converter');
        $definition->setArgument(1, $config['mappings']);

        if (isset($config['max_thumbnail_dimension'])) {
            $container->setParameter('iiirxs.max.dimension.thumbnail', $config['max_thumbnail_dimension']);
        }

        if (isset($config['default_image_upload_dir'])) {
            $container->setParameter('iiirxs.image.upload.dir', $config['default_image_upload_dir']);
        }

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

        $definition = $container->findDefinition(ChainUploader::class);
        $taggedServices = $container->findTaggedServiceIds('image.uploader');

        $thumbnailBinding = 'int $maxThumbnailDimension';
        $thumbnailParameter = 'iiirxs.max.dimension.thumbnail';

        $imageDirParameter = 'iiirxs.image.upload.dir';

        if (
            empty($taggedServices) &&
            $container->hasParameter($imageDirParameter) &&
            !empty($container->getParameter($imageDirParameter))
        ) {
            $defaultId = 'iiirxs_image_upload.uploader.default_uploader';
            $uploaderDefinition = $container->getDefinition($defaultId);
            $this->addUploaderBinding($container, $uploaderDefinition, $thumbnailParameter, $thumbnailBinding);
            $this->addUploaderBinding($container, $uploaderDefinition, $imageDirParameter, 'string $imagesDir');

            $definition->addMethodCall('addUploader', [ new Reference($defaultId) ]);
        }

        foreach ($taggedServices as $id => $tags) {
            $this->addUploaderBinding($container, $container->getDefinition($id), $thumbnailParameter, $thumbnailBinding);
            $definition->addMethodCall('addUploader', [ new Reference($id) ]);
        }
    }

    private function addUploaderBinding(
        ContainerBuilder $containerBuilder,
        Definition $definition,
        string $parameter,
        string $binding
    )
    {
        if ($containerBuilder->hasParameter($parameter)) {
            $parameterValue = $containerBuilder->getParameter($parameter);
            $bindinds = $definition->getBindings();
            $bindinds[$binding] = $parameterValue;

            $definition->setBindings($bindinds);
        }
    }
}