<?php
/**
 * Created by PhpStorm.
 * User: d.galaup
 * Date: 19/01/2016
 * Time: 15:25
 */

namespace Erichard\DmsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @package Erichard\DmsBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD)
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('erichard_dms');

        $viewModes = array('gallery', 'showcase', 'table', 'content');
        $rootNode
            ->children()
                ->arrayNode('view_modes')
                    ->defaultValue($viewModes)
                    ->prototype('scalar')
                        ->validate()
                            ->ifNotInArray($viewModes)
                            ->thenInvalid('%s is not a valid view mode.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('storage')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('max_file_size')
                            ->defaultValue('1gb')
                        ->end()
                        ->scalarNode('chunk_size')
                            ->defaultValue('5mb')
                        ->end()
                        ->scalarNode('path')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->validate()
                                ->ifTrue(
                                    function ($v) {
                                        return !is_dir($v);
                                    }
                                )
                                ->thenInvalid('The given path does not exist : %s')
                            ->end()
                        ->end()
                        ->scalarNode('tmp_path')
                            ->cannotBeEmpty()
                            ->defaultValue(sys_get_temp_dir())
                            ->validate()
                                ->ifTrue(
                                    function ($v) {
                                        return !is_dir($v);
                                    }
                                )
                                ->thenInvalid('The given path does not exist : %s')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('path')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->validate()
                                ->ifTrue(
                                    function ($v) {
                                        return !is_dir($v);
                                    }
                                )
                                ->thenInvalid('The given path does not exist : %s')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('gallery')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('items_per_row')->defaultValue(4)->end()
                        ->scalarNode('image_size')
                            ->defaultValue('260x180')
                            ->validate()
                                ->ifTrue(
                                    function ($v) {
                                        return !preg_match('/\d+x\d+/', $v);
                                    }
                                )
                                ->thenInvalid('The given size "%s" is not valid. Please use the {width}x{height} format.')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('content')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('image_size')
                            ->defaultValue('190x80')
                            ->validate()
                                ->ifTrue(
                                    function ($v) {
                                        return !preg_match('/\d+x\d+/', $v);
                                    }
                                )
                                ->thenInvalid('The given size "%s" is not valid. Please use the {width}x{height} format.')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('showcase')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('image_size')
                            ->defaultValue('1200x900')
                            ->validate()
                                ->ifTrue(
                                    function ($v) {
                                        return !preg_match('/\d+x\d+/', $v);
                                    }
                                )
                                ->thenInvalid('The given size "%s" is not valid. Please use the {width}x{height} format.')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('show')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('image_size')
                            ->defaultValue('440x600')
                            ->validate()
                                ->ifTrue(
                                    function ($v) {
                                        return !preg_match('/\d+x\d+/', $v);
                                    }
                                )
                                ->thenInvalid('The given size "%s" is not valid. Please use the {width}x{height} format.')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('table')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('image_size')
                            ->defaultValue('32x32')
                            ->validate()
                                ->ifTrue(
                                    function ($v) {
                                        return !preg_match('/\d+x\d+/', $v);
                                    }
                                )
                                ->thenInvalid('The given size "%s" is not valid. Please use the {width}x{height} format.')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('permission')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultValue(false)->end()
                        ->scalarNode('super_admin_role')
                            ->defaultValue('ROLE_ADMIN_DMS')
                        ->end()
                        ->scalarNode('sonata_role_slug')
                            ->defaultValue('DMS')
                        ->end()
                        ->scalarNode('role_provider')
                            ->cannotBeEmpty()
                        ->end()
                        ->arrayNode('roles_node')
                            ->defaultValue(array())
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('roles_document')
                            ->defaultValue(array())
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('workspace')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('show_nodes')->defaultValue(false)->end()
                        ->scalarNode('user_pattern')
                            ->defaultValue('^(user|USER)-([0-9]*)$')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    /**
     * addImageSizeNode
     *
     * @param mixed $defaultSize
     *
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    public function addImageSizeNode($defaultSize)
    {
        $builder = new TreeBuilder();
        $node = $builder->root('image_size');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('image_size')
                    ->defaultValue($defaultSize)
                    ->validate()
                        ->ifTrue(
                            function ($v) {
                                return !preg_match('/\d+x\d+/', $v);
                            }
                        )
                        ->thenInvalid('The given size "%s" is not valid. Please use the {width}x{height} format.')
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
