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

/**
 * Allows an object to be rendered in a templating engine
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
interface ColumnLabelRendererInterface
{
    public function renderColumnLabel(Column $column): string;
}
