<?php
/*
 * This file is part of the Sidus/DataGridBundle package.
 *
 * Copyright (c) 2015-2018 Vincent Chalnot
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sidus\DataGridBundle\Registry;

use Sidus\DataGridBundle\Model\DataGrid;
use Sidus\FilterBundle\Exception\MissingFilterException;
use Sidus\FilterBundle\Exception\MissingQueryHandlerException;
use Sidus\FilterBundle\Exception\MissingQueryHandlerFactoryException;
use Sidus\FilterBundle\Registry\QueryHandlerRegistry;
use UnexpectedValueException;

/**
 * Handles datagrids configurations
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class DataGridRegistry
{
    protected QueryHandlerRegistry $queryHandlerRegistry;

    /** @var DataGrid[] */
    protected array $dataGrids = [];

    /** @var array[] */
    protected array $dataGridConfigurations = [];

    public function __construct(QueryHandlerRegistry $queryHandlerRegistry)
    {
        $this->queryHandlerRegistry = $queryHandlerRegistry;
    }

    public function addRawDataGridConfiguration(string $code, array $configuration): void
    {
        $this->dataGridConfigurations[$code] = $configuration;
    }

    public function addDataGrid(DataGrid $dataGrid): void
    {
        $this->dataGrids[$dataGrid->getCode()] = $dataGrid;
    }

    /**
     * @throws MissingQueryHandlerFactoryException
     * @throws MissingQueryHandlerException
     * @throws MissingFilterException
     */
    public function getDataGrid(string $code): DataGrid
    {
        if (!array_key_exists($code, $this->dataGrids)) {
            return $this->buildDataGrid($code);
        }

        return $this->dataGrids[$code];
    }

    public function hasDataGrid(string $code): bool
    {
        return array_key_exists($code, $this->dataGrids) || array_key_exists($code, $this->dataGridConfigurations);
    }

    /**
     * @throws MissingQueryHandlerFactoryException
     * @throws MissingQueryHandlerException
     * @throws MissingFilterException
     */
    protected function buildDataGrid(string $code): DataGrid
    {
        if (!array_key_exists($code, $this->dataGridConfigurations)) {
            throw new UnexpectedValueException("No data-grid with code : {$code}");
        }

        $configuration = $this->dataGridConfigurations[$code];
        $this->queryHandlerRegistry->addRawQueryHandlerConfiguration(
            '__sidus_datagrid.' . $code,
            $configuration['query_handler']
        );
        $configuration['query_handler'] = $this->queryHandlerRegistry->getQueryHandler('__sidus_datagrid.' . $code);

        $dataGrid = new DataGrid($code, $configuration);
        $this->addDataGrid($dataGrid);
        unset($this->dataGridConfigurations[$code]);

        return $dataGrid;
    }
}
