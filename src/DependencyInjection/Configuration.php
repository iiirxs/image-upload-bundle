<?php


namespace IIIRxs\ImageUploadBundle\DependencyInjection;


use IIIRxs\ImageUploadBundle\Form\Type\ImageCollectionType;
use IIIRxs\ImageUploadBundle\Form\Type\ImageType;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const MAX_THUMBNAIL_DIMENSION = 'max_thumbnail_dimension';
    const CACHE_PROVIDER = 'cache_provider';
    const DEFAULT_IMAGE_UPLOAD_DIR = 'default_image_upload_dir';
    const MAPPINGS = 'mappings';

    const DIRECTORIES_KEY = 'directories';
    const FIELDS_KEY = 'fields';
    const OPTIMIZED_KEY = 'optimized';
    const THUMBNAILS_KEY = 'thumbnails';
    const ENTRY_TYPE_KEY = 'entry_type';
    const FORM_TYPE_KEY = 'form_type';

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('iiirxs_image_upload');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->integerNode(self::MAX_THUMBNAIL_DIMENSION)
                    ->defaultValue(600)
                ->end()
                ->scalarNode(self::CACHE_PROVIDER)
                    ->defaultValue('cache.app')
                ->end()
                ->arrayNode(self::DEFAULT_IMAGE_UPLOAD_DIR)
                    ->beforeNormalization()
                    ->ifString()
                        ->then(function($value) { return [self::OPTIMIZED_KEY => $value]; })
                    ->end()
                    ->children()
                        ->scalarNode(self::OPTIMIZED_KEY)->end()
                        ->scalarNode(self::THUMBNAILS_KEY)->end()
                    ->end()
                ->end()
                ->arrayNode(self::MAPPINGS)
                    ->fixXmlConfig('mapping')
                    ->useAttributeAsKey('class')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode(self::FIELDS_KEY)
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('class')->end()
                                        ->scalarNode(self::ENTRY_TYPE_KEY)
                                            ->defaultValue(ImageType::class)
                                        ->end()
                                        ->scalarNode(self::FORM_TYPE_KEY)
                                            ->defaultValue(ImageCollectionType::class)
                                        ->end()
                                        ->arrayNode(self::DIRECTORIES_KEY)
                                            ->beforeNormalization()
                                            ->ifString()
                                                ->then(function($value) { return [self::OPTIMIZED_KEY => $value]; })
                                            ->end()
                                            ->children()
                                                ->scalarNode(self::OPTIMIZED_KEY)->end()
                                                ->scalarNode(self::THUMBNAILS_KEY)->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}