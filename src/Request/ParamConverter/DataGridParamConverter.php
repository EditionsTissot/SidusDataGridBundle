<?php
/*
 * This file is part of the Sidus/DataGridBundle package.
 *
 * Copyright (c) 2015-2018 Vincent Chalnot
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sidus\DataGridBundle\Request\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sidus\BaseBundle\Request\ParamConverter\AbstractParamConverter;
use Sidus\DataGridBundle\Model\DataGrid;
use Sidus\DataGridBundle\Registry\DataGridRegistry;

/**
 * Automatically convert request parameters in datagrid
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class DataGridParamConverter extends AbstractParamConverter
{
    /** @var DataGridRegistry */
    protected DataGridRegistry $dataGridRegistry;

    /**
     * @param DataGridRegistry $dataGridRegistry
     */
    public function __construct(DataGridRegistry $dataGridRegistry)
    {
        $this->dataGridRegistry = $dataGridRegistry;
    }

    /**
     * @param string         $value
     * @param ParamConverter $configuration
     *
     * @return DataGrid
     */
    protected function convertValue($value, ParamConverter $configuration)
    {
        return $this->dataGridRegistry->getDataGrid($value);
    }

    /**
     * @return mixed
     */
    protected function getClass()
    {
        return DataGrid::class;
    }
}
