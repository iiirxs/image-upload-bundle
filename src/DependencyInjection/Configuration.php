<?php


namespace IIIRxs\ImageUploadBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('iiirxs_image_upload');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->integerNode('max_thumbnail_dimension')
                    ->defaultValue(600)
                ->end()
                ->scalarNode('cache_provider')
                    ->defaultValue('cache.app')
                ->end()
                ->arrayNode('default_image_upload_dir')
                    ->beforeNormalization()->castToArray()->end()
                    ->children()
                        ->scalarNode('optimized')->end()
                        ->scalarNode('thumbnails')->end()
                    ->end()
                ->end()
                ->arrayNode('mappings')
                    ->fixXmlConfig('mapping')
                    ->useAttributeAsKey('class')
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('fields')
                                ->useAttributeAsKey('name')
                                ->arrayPrototype()
                                    ->children()
                                        ->scalarNode('class')->end()
                                        ->scalarNode('form_type')->end()
                                        ->arrayNode('directories')
                                            ->beforeNormalization()->castToArray()->end()
                                            ->children()
                                                ->scalarNode('optimized')->end()
                                                ->scalarNode('thumbnails')->end()
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