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

use DateTimeInterface;
use IntlDateFormatter;
use NumberFormatter;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function is_bool;
use function is_float;
use function is_int;
use function is_iterable;

/**
 * Render values inside the Twig engine
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class DefaultColumnValueRenderer implements ColumnValueRendererInterface
{
    /** @var TranslatorInterface */
    protected TranslatorInterface $translator;

    /** @var PropertyAccessorInterface */
    protected PropertyAccessorInterface $accessor;

    /**
     * @param TranslatorInterface       $translator
     * @param PropertyAccessorInterface $accessor
     */
    public function __construct(
        TranslatorInterface $translator,
        PropertyAccessorInterface $accessor
    )
    {
        $this->translator = $translator;
        $this->accessor = $accessor;
    }

    /**
     * @param mixed $value
     * @param array $options
     *
     * @return string
     */
    public function renderValue($value, array $options = []): string
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $options = $resolver->resolve($options);

        if ($value instanceof DateTimeInterface) {
            if (null !== $options['date_format']) {
                return (string) $value->format($options['date_format']);
            }
            if ($value->format('H:i') === '00:00') {
                $options['time_type'] = IntlDateFormatter::NONE;
            }
            $dateFormatter = new IntlDateFormatter(
                $this->translator->getLocale(),
                $options['date_type'],
                $options['time_type']
            );

            return (string) $dateFormatter->format($value);
        }
        if (is_int($value)) {
            return (string) $value;
        }
        if (is_float($value)) {
            if (null !== $options['decimals'] || null !== $options['dec_point'] || null !== $options['thousands_sep']) {
                return number_format(
                    $value,
                    $options['decimals'] ?: 2,
                    $options['dec_point'] ?: '.',
                    $options['thousands_sep'] ?: ','
                );
            }
            $numberFormatter = new NumberFormatter($this->translator->getLocale(), $options['number_format']);

            return (string) $numberFormatter->format($value);
        }
        if (is_iterable($value)) {
            $items = [];
            /** @noinspection ForeachSourceInspection */
            foreach ($value as $key => $item) {
                $rendered = $this->renderValue($item, $options);
                if (!is_numeric($key)) {
                    $rendered = $key.$options['key_value_separator'].$rendered;
                }
                $items[] = $rendered;
            }

            return implode($options['array_glue'], $items);
        }
        if (is_bool($value)) {
            if ($options['bool_use_translator']) {
                return (string) $this->translator->trans(
                    $value ? $options['bool_true'] : $options['bool_false']
                );
            }

            return $value ? $options['bool_true'] : $options['bool_false'];
        }

        return (string) $value;
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'object' => null,
                'column' => null,
                'date_format' => null,
                'date_type' => IntlDateFormatter::MEDIUM,
                'time_type' => IntlDateFormatter::SHORT,
                'number_format' => NumberFormatter::DECIMAL,
                'decimals' => null,
                'dec_point' => null,
                'thousands_sep' => null,
                'array_glue' => ', ',
                'key_value_separator' => ': ',
                'bool_true' => 'sidus.datagrid.boolean.yes',
                'bool_false' => 'sidus.datagrid.boolean.no',
                'bool_use_translator' => true,
            ]
        );
    }
}
