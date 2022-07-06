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

use LogicException;
use Pagerfanta\Exception\InvalidArgumentException;
use Sidus\DataGridBundle\Form\Type\LinkType;
use Sidus\DataGridBundle\Renderer\ColumnLabelRendererInterface;
use Sidus\DataGridBundle\Renderer\ColumnValueRendererInterface;
use Sidus\FilterBundle\Query\Handler\QueryHandlerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Traversable;
use UnexpectedValueException;

/**
 * Handle a datagrid configuration
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class DataGrid
{
    /** @var string */
    protected $code;

    /** @var QueryHandlerInterface */
    protected $queryHandler;

    /** @var string|null */
    protected $formTheme;

    /** @var string */
    protected $template;

    /** @var array */
    protected $templateVars = [];

    /** @var ColumnValueRendererInterface */
    protected $columnValueRenderer;

    /** @var ColumnLabelRendererInterface */
    protected $columnLabelRenderer;

    /** @var Column[] */
    protected $columns = [];

    /** @var FormInterface */
    protected $form;

    /** @var array */
    protected $formOptions = [];

    /** @var FormView */
    protected $formView;

    /** @var array */
    protected $actions = [];

    /** @var array */
    protected $submitButton = [];

    /** @var array */
    protected $resetButton = [];

    public function __construct(string $code, array $configuration)
    {
        $this->code = $code;
        /** @var array $columns */
        $columns = $configuration['columns'];
        unset($configuration['columns']);

        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($configuration as $key => $option) {
            $accessor->setValue($this, $key, $option);
        }

        foreach ($columns as $key => $columnConfiguration) {
            $this->createColumn($key, $columnConfiguration);
        }
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getQueryHandler(): QueryHandlerInterface
    {
        return $this->queryHandler;
    }

    public function setQueryHandler(QueryHandlerInterface $queryHandler): void
    {
        $this->queryHandler = $queryHandler;
    }

    public function getFormTheme(): ?string
    {
        return $this->formTheme;
    }

    /**
     * @param string $formTheme
     */
    public function setFormTheme(string $formTheme = null): void
    {
        $this->formTheme = $formTheme;
    }

    public function getTemplate(): string
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

    public function getColumnValueRenderer(): ColumnValueRendererInterface
    {
        return $this->columnValueRenderer;
    }

    public function setColumnValueRenderer(ColumnValueRendererInterface $columnValueRenderer): void
    {
        $this->columnValueRenderer = $columnValueRenderer;
    }

    public function getColumnLabelRenderer(): ColumnLabelRendererInterface
    {
        return $this->columnLabelRenderer;
    }

    public function setColumnLabelRenderer(ColumnLabelRendererInterface $columnLabelRenderer): void
    {
        $this->columnLabelRenderer = $columnLabelRenderer;
    }

    /**
     * @return Column[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @param int $index
     */
    public function addColumn(Column $column, int $index = null): void
    {
        if (null === $index) {
            $this->columns[] = $column;
        } else {
            array_splice($this->columns, $index, 0, [$column]);
        }
    }

    /**
     * @param Column[] $columns
     */
    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @param string $action
     *
     * @throws UnexpectedValueException
     */
    public function getAction($action): array
    {
        if (!$this->hasAction($action)) {
            throw new UnexpectedValueException("No action with code: '{$action}'");
        }

        return $this->actions[$action];
    }

    public function hasAction(string $action): bool
    {
        return array_key_exists($action, $this->actions);
    }

    public function setAction(string $action, array $configuration): void
    {
        $this->actions[$action] = $configuration;
    }

    public function setActions(array $actions): void
    {
        $this->actions = $actions;
    }

    public function getSubmitButton(): array
    {
        return $this->submitButton;
    }

    public function setSubmitButton(array $submitButton): void
    {
        $this->submitButton = $submitButton;
    }

    public function getResetButton(): array
    {
        return $this->resetButton;
    }

    public function setResetButton(array $resetButton): void
    {
        $this->resetButton = $resetButton;
    }

    /**
     * @throws LogicException
     */
    public function getForm(): FormInterface
    {
        if (!$this->form) {
            throw new LogicException('You must first call buildForm()');
        }

        return $this->form;
    }

    public function getFormOptions(): array
    {
        return $this->formOptions;
    }

    public function setFormOptions(array $formOptions): void
    {
        $this->formOptions = $formOptions;
    }

    /**
     * @throws LogicException
     */
    public function getFormView(): FormView
    {
        if (!$this->formView) {
            $this->formView = $this->getForm()->createView();
        }

        return $this->formView;
    }

    public function buildForm(FormBuilderInterface $builder): FormInterface
    {
        $this->buildFilterActions($builder);
        $this->buildDataGridActions($builder);

        $this->form = $this->getQueryHandler()->buildForm($builder);

        return $this->form;
    }

    public function handleRequest(Request $request): void
    {
        $this->queryHandler->handleRequest($request);
    }

    public function handleArray(array $data): void
    {
        $this->queryHandler->handleArray($data);
    }

    /**
     * @throws InvalidArgumentException
     *
     * @return array|Traversable
     */
    public function getPager()
    {
        return $this->getQueryHandler()->getPager();
    }

    /**
     * @param string $action
     *
     * @throws UnexpectedValueException
     */
    public function setActionParameters($action, array $parameters): void
    {
        if ('submit_button' === $action) {
            $this->setSubmitButton(
                array_merge(
                    $this->getSubmitButton(),
                    [
                        'route_parameters' => $parameters,
                    ]
                )
            );

            return;
        }

        if ('reset_button' === $action) {
            $this->setResetButton(
                array_merge(
                    $this->getResetButton(),
                    [
                        'route_parameters' => $parameters,
                    ]
                )
            );

            return;
        }
        $this->setAction(
            $action,
            array_merge(
                $this->getAction($action),
                [
                    'route_parameters' => $parameters,
                ]
            )
        );
    }

    protected function buildFilterActions(FormBuilderInterface $builder): void
    {
        $visibleFilterCount = 0;

        foreach ($this->getQueryHandler()->getConfiguration()->getFilters() as $filter) {
            $filter->getOption('hidden') ?: ++$visibleFilterCount;
        }

        if ($visibleFilterCount > 0) {
            $this->buildResetAction($builder);
            $this->buildSubmitAction($builder);
        }
    }

    protected function buildResetAction(FormBuilderInterface $builder): void
    {
        $action = $builder->getOption('action');
        $defaults = [
            'form_type' => LinkType::class,
            'label' => 'sidus.datagrid.reset.label',
            'uri' => $action ?: '?',
        ];
        $options = array_merge($defaults, $this->getResetButton());
        $type = $options['form_type'];
        unset($options['form_type']);
        $builder->add('filterResetButton', $type, $options);
    }

    protected function buildSubmitAction(FormBuilderInterface $builder): void
    {
        $defaults = [
            'form_type' => SubmitType::class,
            'label' => 'sidus.datagrid.submit.label',
            'attr' => [
                'class' => 'btn-primary',
            ],
        ];
        $options = array_merge($defaults, $this->getSubmitButton());
        $type = $options['form_type'];
        unset($options['form_type']);
        $builder->add('filterSubmitButton', $type, $options);
    }

    protected function buildDataGridActions(FormBuilderInterface $builder): void
    {
        $actionsBuilder = $builder->create(
            'actions',
            FormType::class,
            [
                'label' => false,
            ]
        );

        foreach ($this->getActions() as $code => $options) {
            $type = empty($options['form_type']) ? LinkType::class : $options['form_type'];
            unset($options['form_type']);
            $actionsBuilder->add($code, $type, $options);
        }
        $builder->add($actionsBuilder);
    }

    protected function createColumn(string $key, array $columnConfiguration): void
    {
        $this->columns[] = new Column($key, $this, $columnConfiguration);
    }
}
