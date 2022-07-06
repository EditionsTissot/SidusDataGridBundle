<?php
/*
 * This file is part of the Sidus/DataGridBundle package.
 *
 * Copyright (c) 2015-2018 Vincent Chalnot
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sidus\DataGridBundle\Renderer;

use Sidus\DataGridBundle\Model\Column;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Render values inside the Twig engine
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class DefaultColumnLabelRenderer implements ColumnLabelRendererInterface
{
    /** @var TranslatorInterface */
    protected TranslatorInterface $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param Column $column
     *
     * @return string
     */
    public function renderColumnLabel(Column $column): string
    {
        $label = $column->getLabel();
        if (!$label) {
            $key = "datagrid.{$column->getDataGrid()->getCode()}.{$column->getCode()}";
            if ($this->translator instanceof TranslatorBagInterface
                && $this->translator->getCatalogue()
                && $this->translator->getCatalogue()->has($key)
            ) {
                $label = $key;
            } else {
                $label = ucfirst(
                    strtolower(trim(preg_replace(['/([A-Z])/', '/[_\s]+/'], ['_$1', ' '], $column->getCode())))
                );
            }
        }

        return $this->translator->trans($label);
    }
}
