<?php


namespace IIIRxs\ImageUploadBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    const MAX_THUMBNAIL_DIMENSION = 'max_thumbnail_dimension';
    const CACHE_PROVIDER = 'cache_provider';
    const DEFAULT_IMAGE_UPLOAD_DIR = 'default_image_upload_dir';
    const MAPPINGS = 'mappings';

    const DIRECTORIES_KEY = 'directories';
    const OPTIMIZED_KEY = 'optimized';
    const THUMBNAILS_KEY = 'thumbnails';

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
                            ->arrayNode('fields')
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('class')->end()
                                        ->scalarNode('entry_type')->end()
                                        ->scalarNode('form_type')->end()
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