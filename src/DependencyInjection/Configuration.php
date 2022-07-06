<?php
/*
 * This file is part of the Sidus/DataGridBundle package.
 *
 * Copyright (c) 2015-2018 Vincent Chalnot
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sidus\DataGridBundle\DependencyInjection;

use Closure;
use RuntimeException;
use Sidus\DataGridBundle\Renderer\ColumnLabelRendererInterface;
use Sidus\DataGridBundle\Renderer\ColumnValueRendererInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link
 * http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class Configuration implements ConfigurationInterface
{
    /** @var string */
    protected string $root;

    /** @var Closure */
    protected Closure $serviceResolver;

    /**
     * @param string $root
     */
    public function __construct(string $root = 'sidus_data_grid')
    {
        $this->root = $root;
        $this->serviceResolver = static function ($reference) {
            return new Reference(ltrim($reference, '@'));
        };
    }

    /**
     * {@inheritdoc}
     * @throws RuntimeException
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder($this->root);
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
            ->scalarNode('default_form_theme')->defaultNull()->end()
            ->scalarNode('default_datagrid_template')
            ->defaultValue('@SidusDataGrid/DataGrid/bootstrap4.html.twig')
            ->end()
            ->variableNode('default_column_value_renderer')
            ->defaultValue(new Reference(ColumnValueRendererInterface::class))
            ->beforeNormalization()->always($this->serviceResolver)->end()
            ->end()
            ->variableNode('default_column_label_renderer')
            ->defaultValue(new Reference(ColumnLabelRendererInterface::class))
            ->beforeNormalization()->always($this->serviceResolver)->end()
            ->end()
            ->variableNode('actions')->defaultValue([])->end()
            ->append($this->getDataGridConfigTreeBuilder())
            ->end();

        return $treeBuilder;
    }

    /**
     * @throws RuntimeException
     *
     * @return NodeDefinition
     */
    protected function getDataGridConfigTreeBuilder(): NodeDefinition
    {
        $builder = new TreeBuilder('configurations');
        $node = $builder->getRootNode();
        $dataGridDefinition = $node
            ->useAttributeAsKey('code')
            ->prototype('array')
            ->performNoDeepMerging()
            ->children();

        $this->appendDataGridDefinition($dataGridDefinition);

        $dataGridDefinition
            ->end()
            ->end()
            ->end();

        return $node;
    }

    /**
     * @param NodeBuilder $dataGridDefinition
     */
    protected function appendDataGridDefinition(NodeBuilder $dataGridDefinition): void
    {
        $columnDefinition = $dataGridDefinition
            ->variableNode('query_handler')->end()
            ->scalarNode('form_theme')->end()
            ->variableNode('form_options')->end()
            ->scalarNode('template')->end()
            ->variableNode('template_vars')->end()
            ->scalarNode('parent')->end()
            ->variableNode('column_value_renderer')
            ->beforeNormalization()->always($this->serviceResolver)->end()
            ->end()
            ->variableNode('column_label_renderer')
            ->beforeNormalization()->always($this->serviceResolver)->end()
            ->end()
            ->variableNode('actions')->end()
            ->variableNode('submit_button')->end()
            ->variableNode('reset_button')->end()
            ->arrayNode('columns')
            ->prototype('array')
            ->children();

        $this->appendColumnDefinition($columnDefinition);

        $columnDefinition
            ->end()
            ->end()
            ->end();
    }

    /**
     * @param NodeBuilder $columnDefinition
     */
    protected function appendColumnDefinition(NodeBuilder $columnDefinition): void
    {
        $columnDefinition
            ->scalarNode('template')->end()
            ->variableNode('template_vars')->end()
            ->scalarNode('sort_column')->end()
            ->scalarNode('property_path')->end()
            ->scalarNode('label')->end()
            ->variableNode('label_renderer')
            ->beforeNormalization()->always($this->serviceResolver)->end()
            ->end()
            ->variableNode('value_renderer')
            ->beforeNormalization()->always($this->serviceResolver)->end()
            ->end()
            ->variableNode('formatting_options')->end();
    }
}
