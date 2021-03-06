<?php

namespace HelloSebastian\ReactTableBundle\Data;


use HelloSebastian\ReactTableBundle\Columns\Column;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionButton
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var Column
     */
    private $column;

    public function __construct($column, $options)
    {
        $this->column = $column;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'displayName' => null,
            'classNames' => '',
            'additionalClassNames' => '',
            'routeName' => null,
            'routeParams' => array('id')
        ));

        $resolver->setRequired('displayName');
        $resolver->setRequired('routeName');

        $resolver->setAllowedTypes('displayName', 'string');
        $resolver->setAllowedTypes('routeName', 'string');
        $resolver->setAllowedTypes('classNames', 'string');
        $resolver->setAllowedTypes('additionalClassNames', 'string');
        $resolver->setAllowedTypes('routeParams', 'array');
    }

    public function buildArray()
    {
        $defaultClassNames = $this->column->getColumnBuilder()->getDefaultColumnOptions()['action_column']['default_class_names'];

        if (!empty($this->options['classNames'])) {
            $classNames = $this->options['classNames'] . ' ' . $this->options['additionalClassNames'];
        } else {
            $classNames = $defaultClassNames . ' ' . $this->options['additionalClassNames'];
        }

        return array(
            'name' => $this->options['displayName'],
            'classNames' => $classNames
        );
    }

    public function getDisplayName()
    {
        return $this->options['name'];
    }

    public function getRouteName()
    {
        return $this->options['routeName'];
    }

    public function getRouteParams()
    {
        return $this->options['routeParams'];
    }

    public function getOptions()
    {
        return $this->options;
    }

}