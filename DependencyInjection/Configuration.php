<?php

namespace RedBullet\PersistentObjectsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use RedBullet\PersistentObjectsBundle\PersistentObject\Manager;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('red_bullet_persistent_objects');

        $rootNode
            ->children()
                ->arrayNode('mapping')
                    ->defaultValue(array())
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')
                    ->end()
                    ->validate()
                        ->ifTrue(function($value) {
                            // check for valid keys
                            foreach ($value as $k => $v) {
                                if (!preg_match(Manager::NAME_REG_EXP, $k)) {
                                    return true;
                                }
                            }

                            return false;
                        })
                        ->thenInvalid(
                            'The redbullet_persistent_objects.mapping keys must'
                            . ' match ' . Manager::NAME_REG_EXP
                        )
                    ->end()
                    ->validate()
                        ->ifTrue(function($value) {
                            $keys = array_map('strtolower', array_keys($value));
                            return count($keys) != count(array_unique($keys));
                        })
                        ->thenInvalid(
                            'Each redbullet_persistent_objects.mapping key must'
                            . ' be unique (case-insentive)'
                        )
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
