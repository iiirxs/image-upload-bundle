<?php

namespace IIIRxs\ImageUploadBundle\DependencyInjection;

use IIIRxs\ImageUploadBundle\Controller\ImageController;
use IIIRxs\ImageUploadBundle\Form\ImageFormService;
use IIIRxs\ImageUploadBundle\Uploader\ChainUploader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

        foreach ($taggedServices as $id => $tags) {
            $uploaderDefinition = $container->getDefinition($id);

            if ($container->hasParameter('iiirxs.max.dimension.thumbnail')) {
                $maxThumbnailDimension = $container->getParameter('iiirxs.max.dimension.thumbnail');
                $bindinds = $uploaderDefinition->getBindings();
                $bindinds['int $maxThumbnailDimension'] = $maxThumbnailDimension;

                $uploaderDefinition->setBindings($bindinds);
            }

            $definition->addMethodCall('addUploader', [ new Reference($id) ]);
        }
    }
}