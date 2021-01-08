<?php

namespace HelloSebastian\ReactTableBundle\Data;


use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionButton
{
    private $options;

    public function __construct($options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    public static function create($routeName, $name, $options = array())
    {
        $options['routeName'] = $routeName;
        $options['name'] = $name;

        return new self($options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'name' => null,
            'classNames' => '',
            'routeName' => null,
            'routeParams' => array('id')
        ));

        $resolver->setRequired('name');
        $resolver->setRequired('routeName');

        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('routeName', 'string');
        $resolver->setAllowedTypes('classNames', 'string');
        $resolver->setAllowedTypes('routeParams', 'array');
    }

    public function buildArray()
    {
        return array(
            'name' => $this->options['name'],
            'classNames' => $this->options['classNames']
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