<?php


namespace HelloSebastian\ReactTableBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('react_table');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('default_table_props')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('showPagination')->defaultTrue()->end()
                        ->booleanNode('showPaginationTop')->defaultFalse()->end()
                        ->booleanNode('showPaginationBottom')->defaultTrue()->end()
                        ->booleanNode('showPageSizeOptions')->defaultTrue()->end()
                        ->arrayNode('pageSizeOptions')
                            ->scalarPrototype()->end()
                                ->defaultValue(array(5, 10, 20, 25, 50, 100))
                        ->end()
                        ->integerNode('defaultPageSize')->defaultValue(20)->end()
                        ->booleanNode('showPageJump')->defaultTrue()->end()
                        ->booleanNode('collapseOnSortingChange')->defaultTrue()->end()
                        ->booleanNode('collapseOnPageChange')->defaultTrue()->end()
                        ->booleanNode('freezeWhenExpanded')->defaultFalse()->end()
                        ->booleanNode('sortable')->defaultTrue()->end()
                        ->booleanNode('multiSort')->defaultTrue()->end()
                        ->booleanNode('resizable')->defaultTrue()->end()
                        ->booleanNode('filterable')->defaultTrue()->end()
                        ->booleanNode('defaultSortDesc')->defaultFalse()->end()
                        ->scalarNode('className')->defaultValue('')->end()
                        ->scalarNode('previousText')->defaultValue('Previous')->end()
                        ->scalarNode('nextText')->defaultValue('Next')->end()
                        ->scalarNode('loadingText')->defaultValue('Loading...')->end()
                        ->scalarNode('noDataText')->defaultValue('No rows found')->end()
                        ->scalarNode('pageText')->defaultValue('Page')->end()
                        ->scalarNode('ofText')->defaultValue('of')->end()
                        ->scalarNode('rowsText')->defaultValue('Rows')->end()
                        ->scalarNode('pageJumpText')->defaultValue('jump to page')->end()
                        ->scalarNode('rowsSelectorText')->defaultValue('rows per page')->end()
                ->end()
            ->end()
            ->arrayNode('default_persistence_options')
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode('resized')->defaultTrue()->end()
                    ->booleanNode('filtered')->defaultTrue()->end()
                    ->booleanNode('sorted')->defaultTrue()->end()
                    ->booleanNode('page')->defaultTrue()->end()
                    ->booleanNode('page_size')->defaultTrue()->end()
                ->end()
            ->end();


        return $treeBuilder;
    }
}