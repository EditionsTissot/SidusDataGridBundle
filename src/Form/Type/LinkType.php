<?php
/*
 * This file is part of the Sidus/DataGridBundle package.
 *
 * Copyright (c) 2015-2018 Vincent Chalnot
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sidus\DataGridBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UnexpectedValueException;

/**
 * Fake form element to display a link
 *
 * @author Vincent Chalnot <vincent@sidus.fr>
 */
class LinkType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['route'] = $options['route'];
        $view->vars['route_parameters'] = $options['route_parameters'];
        $view->vars['uri'] = $options['uri'];
    }

    /**
     * @throws AccessException
     * @throws UndefinedOptionsException
     * @throws UnexpectedValueException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'route' => null,
                'route_parameters' => [],
                'uri' => null,
                'attr' => [
                    'class' => 'btn btn-default btn-light',
                ],
            ]
        );
        $resolver->setAllowedTypes('route_parameters', 'array');
        $resolver->setNormalizer(
            'route',
            static function (Options $options, $value) {
                if (!($value xor $options['uri'])) {
                    throw new UnexpectedValueException("You must specify either a 'route' or an 'uri' option");
                }

                return $value;
            }
        );
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sidus_link';
    }
}
