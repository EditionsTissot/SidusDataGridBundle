<?php
/*
 * This file is part of the Sidus/DataGridBundle package.
 *
 * Copyright (c) 2015-2018 Vincent Chalnot
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sidus\DataGridBundle\Model;

use Sidus\DataGridBundle\Renderer\ColumnLabelRendererInterface;
use Sidus\DataGridBundle\Renderer\ColumnValueRendererInterface;
use Symfony\Component\PropertyAccess\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Represents a column configuration for a datagrid
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class Column
{
    /** @var string */
    protected $code;

    /** @var DataGrid */
    protected $dataGrid;

    /** @var string */
    protected $template;

    /** @var array */
    protected $templateVars = [];

    /** @var string */
    protected $sortColumn;

    /** @var string */
    protected $propertyPath;

    /** @var ColumnValueRendererInterface */
    protected $valueRenderer;

    /** @var ColumnLabelRendererInterface */
    protected $labelRenderer;

    /** @var array */
    protected $formattingOptions = [];

    /** @var string */
    protected $label;

    /**
     * @param string $code
     */
    public function __construct($code, DataGrid $dataGrid, array $options = [])
    {
        $this->code = $code;
        $this->dataGrid = $dataGrid;
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($options as $key => $option) {
            $accessor->setValue($this, $key, $option);
        }
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getDataGrid(): DataGrid
    {
        return $this->dataGrid;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    public function getTemplateVars(): array
    {
        return $this->templateVars;
    }

    public function setTemplateVars(array $templateVars): void
    {
        $this->templateVars = $templateVars;
    }

    public function getSortColumn(): string
    {
        if (!$this->sortColumn) {
            return $this->getCode();
        }

        return $this->sortColumn;
    }

    public function setSortColumn(string $sortColumn): void
    {
        $this->sortColumn = $sortColumn;
    }

    public function getPropertyPath(): string
    {
        if (!$this->propertyPath) {
            return $this->getCode();
        }

        return $this->propertyPath;
    }

    public function setPropertyPath(string $propertyPath): void
    {
        $this->propertyPath = $propertyPath;
    }

    public function getValueRenderer(): ColumnValueRendererInterface
    {
        if (!$this->valueRenderer) {
            return $this->getDataGrid()->getColumnValueRenderer();
        }

        return $this->valueRenderer;
    }

    public function setValueRenderer(ColumnValueRendererInterface $valueRenderer): void
    {
        $this->valueRenderer = $valueRenderer;
    }

    public function getLabelRenderer(): ColumnLabelRendererInterface
    {
        if (null === $this->labelRenderer) {
            return $this->getDataGrid()->getColumnLabelRenderer();
        }

        return $this->labelRenderer;
    }

    public function setLabelRenderer(ColumnLabelRendererInterface $labelRenderer): void
    {
        $this->labelRenderer = $labelRenderer;
    }

    public function getFormattingOptions(): array
    {
        return $this->formattingOptions;
    }

    public function setFormattingOptions(array $formattingOptions): void
    {
        $this->formattingOptions = $formattingOptions;
    }

    /**
     * @return string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * Get column value for a given object
     */
    public function getValue($object)
    {
        $accessor = PropertyAccess::createPropertyAccessor();

        return $accessor->getValue($object, $this->getPropertyPath());
    }

    /**
     * Render column for a given result
     */
    public function renderValue($object, array $options = []): string
    {
        try {
            $value = $this->getValue($object);
        } catch (UnexpectedTypeException $e) {
            return '';
        }

        return $this->getValueRenderer()->renderValue(
            $value,
            array_merge(
                ['column' => $this, 'object' => $object],
                $this->getFormattingOptions(),
                $options
            )
        );
    }

    public function renderLabel(): string
    {
        return ucfirst($this->getLabelRenderer()->renderColumnLabel($this));
    }
}
