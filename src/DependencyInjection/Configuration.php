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
                ->variableNode('default_image_upload_dir')->end()
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