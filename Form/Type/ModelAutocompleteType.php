<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Sonata\AdminBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdPropertyTransformer;

/**
 * This type defines a standard text field with autocomplete feature.
 *
 * @author Andrej Hudec <pulzarraider@gmail.com>
 */
class ModelAutocompleteType extends AbstractType
{
    const SEARCH_TYPE_BEGINS_WITH = 'begins_with';
    const SEARCH_TYPE_CONTAINS = 'contains';
    const SEARCH_TYPE_ENDS_WITH = 'ends_with';

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->addViewTransformer(new ModelToIdPropertyTransformer($options['model_manager'], $options['class'], $options['property']), true)
        ;

        // form type is created from `text` and `hidden` form elements.
        $builder->add('identifier', 'hidden');
        $builder->add('title', 'text', array('attr'=>array('class'=>'span5')));

        $builder->setAttribute('property', $options['property']);
        $builder->setAttribute('callback', $options['callback']);
        $builder->setAttribute('minimum_input_length', $options['minimum_input_length']);
        $builder->setAttribute('items_per_page', $options['items_per_page']);
        $builder->setAttribute('search_type', $options['search_type']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['placeholder'] = $options['placeholder'];
        $view->vars['minimum_input_length'] = $options['minimum_input_length'];
        $view->vars['items_per_page'] = $options['items_per_page'];

        // ajax parameters
        $view->vars['url'] = $options['url'];
        $view->vars['route'] = $options['route'];
        $view->vars['req_params'] = $options['req_params'];
        $view->vars['req_param_name_search'] = $options['req_param_name_search'];
        $view->vars['req_param_name_page_number'] = $options['req_param_name_page_number'];
        $view->vars['req_param_name_page_limit'] = $options['req_param_name_page_limit'];

        // dropdown list css class
        $view->vars['dropdown_css_class'] = $options['dropdown_css_class'];
    }

    /**
     * {@inheritDoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'compound'                        => true,
            'model_manager'                   => null,
            'class'                           => null,
            'property'                        => null,
            'callback'                        => null,
            'search_type'                     => self::SEARCH_TYPE_CONTAINS,

            'placeholder'                     => '',
            'minimum_input_length'            => 3, //minimum 3 chars should be typed to load ajax data
            'items_per_page'                  => 10, //number of items per page

            // ajax parameters
            'url'                             => '',
            'route'                           => array('name'=>'sonata_admin_retrieve_autocomplete_items', 'parameters'=>array()),
            'req_params'                      => array(),
            'req_param_name_search'           => 'q',
            'req_param_name_page_number'      => 'page',
            'req_param_name_page_limit'       => 'limit',

            // dropdown list css class
            'dropdown_css_class'              => 'sonata-autocomplete-dropdown',
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return 'field';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sonata_type_model_autocomplete';
    }
}
