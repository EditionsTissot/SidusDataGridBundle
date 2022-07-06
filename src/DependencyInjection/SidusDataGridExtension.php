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

use Sidus\BaseBundle\DependencyInjection\SidusBaseExtension;
use Sidus\DataGridBundle\Registry\DataGridRegistry;
use Sidus\FilterBundle\DependencyInjection\Configuration as FilterConfiguration;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use UnexpectedValueException;
use function is_array;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class SidusDataGridExtension extends SidusBaseExtension
{
    /** @var array */
    protected array $globalConfiguration;

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        parent::load($configs, $container);

        $configuration = $this->createConfigurationParser();
        $this->globalConfiguration = $this->processConfiguration($configuration, $configs);

        $dataGridRegistry = $container->getDefinition(DataGridRegistry::class);
        foreach ((array) $this->globalConfiguration['configurations'] as $code => $dataGridConfiguration) {
            $dataGridConfiguration = $this->finalizeConfiguration($code, $dataGridConfiguration);
            $dataGridRegistry->addMethodCall('addRawDataGridConfiguration', [$code, $dataGridConfiguration]);
        }
    }

    /**
     * Handle configuration parsing logic not handled by the semantic configuration definition
     *
     * @param string $code
     * @param array  $dataGridConfiguration
     *
     * @return array
     */
    protected function finalizeConfiguration(
        string $code,
        array $dataGridConfiguration
    ): array {
        // Handle possible parent configuration @todo find a better way to do this
        if (isset($dataGridConfiguration['parent'])) {
            $parent = $dataGridConfiguration['parent'];
            if (empty($this->globalConfiguration['configurations'][$parent])) {
                throw new UnexpectedValueException("Unknown configuration {$parent}");
            }
            $parentConfig = $this->globalConfiguration['configurations'][$parent];
            $dataGridConfiguration = array_merge($parentConfig, $dataGridConfiguration);
        }
        unset($dataGridConfiguration['parent']);

        // Set default values from global configuration
        if (empty($dataGridConfiguration['form_theme'])) {
            $dataGridConfiguration['form_theme'] = $this->globalConfiguration['default_form_theme'];
        }
        if (empty($dataGridConfiguration['template'])) {
            $dataGridConfiguration['template'] = $this->globalConfiguration['default_datagrid_template'];
        }
        if (empty($dataGridConfiguration['column_value_renderer'])) {
            $dataGridConfiguration['column_value_renderer'] = $this->globalConfiguration['default_column_value_renderer'];
        }
        if (empty($dataGridConfiguration['column_label_renderer'])) {
            $dataGridConfiguration['column_label_renderer'] = $this->globalConfiguration['default_column_label_renderer'];
        }

        if (isset($dataGridConfiguration['query_handler'])) {
            // Allow either a service or a direct configuration for filters
            if (is_array($dataGridConfiguration['query_handler'])) {
                $dataGridConfiguration['query_handler'] = $this->finalizeFilterConfiguration(
                    $code,
                    $dataGridConfiguration['query_handler']
                );
            } elseif (0 === strpos($dataGridConfiguration['query_handler'], '@')) {
                $dataGridConfiguration['query_handler'] = new Reference(
                    ltrim($dataGridConfiguration['query_handler'], '@')
                );
            } else {
                throw new UnexpectedValueException(
                    'query_handler option must be either a service or a valid filter configuration'
                );
            }
        }

        return $dataGridConfiguration;
    }

    /**
     * @param string $code
     * @param array  $queryHandlerConfig
     *
     * @return array
     */
    protected function finalizeFilterConfiguration(string $code, array $queryHandlerConfig): array
    {
        // Parse configuration using Configuration parser from FilterBundle
        $configuration = $this->createFilterConfigurationParser();
        $parsedFilterConfig = $this->processConfiguration(
            $configuration,
            [
                [
                    'configurations' => [
                        $code => $queryHandlerConfig,
                    ],
                ],
            ]
        );

        return $parsedFilterConfig['configurations'][$code];
    }

    /**
     * Allows the configuration class to be different in inherited classes
     *
     * @return ConfigurationInterface
     */
    protected function createConfigurationParser(): ConfigurationInterface
    {
        return new Configuration();
    }

    /**
     * Allows the configuration class to be different in inherited classes
     *
     * @return ConfigurationInterface
     */
    protected function createFilterConfigurationParser(): ConfigurationInterface
    {
        return new FilterConfiguration();
    }
}
