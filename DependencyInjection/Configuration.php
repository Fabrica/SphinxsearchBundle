<?php

namespace Delocker\SphinxsearchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     *
     * @var ArrayNodeDefinition
     */
    private $node;
    
    /**
    * Generates the configuration tree.
    *
    * @return TreeBuilder
    */
    public function getConfigTreeBuilder()
    {
            $treeBuilder = new TreeBuilder();
            $this->setNode($treeBuilder->root('sphinxsearch'));
            
            $this->addIndexerSection();
            $this->addIndexesSection();
            $this->addSearchdSection();
            $this->addBundlePath();
            
            return $treeBuilder;
    }

    private function addIndexerSection()
    {
        $this->getNode()
            ->children()
                ->arrayNode('indexer')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('bin')->defaultValue('/usr/bin/indexer')->end()
                        ->scalarNode('conf')->defaultValue('/usr/bin/indexer')->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addIndexesSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('indexes')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('key')
                    ->prototype("array")
                        ->children()
                            ->scalarNode("index_name")->isRequired()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function addSearchdSection()
    {
        $this->getNode()
            ->children()
                ->arrayNode('searchd')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('host')->defaultValue('localhost')->end()
                        ->scalarNode('port')->defaultValue('9312')->end()
                        ->scalarNode('socket')->defaultNull()->end()
                    ->end()
                ->end()
            ->end();
    }
    
    private function addBundlePath(){
        $this->getNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('bundlePath')->defaultValue(realpath(__DIR__ . '/..'))->end()
            ->end();
    }
    /**
     *
     * @return ArrayNodeDefinition 
     */
    public function getNode() 
    {
        return $this->node;
    }
    /**
     *
     * @param ArrayNodeDefinition $node
     * @return \Delocker\SphinxsearchBundle\DependencyInjection\Configuration
     */
    public function setNode(ArrayNodeDefinition $node) 
    {
        $this->node = $node;
        return $this;
    }


}
