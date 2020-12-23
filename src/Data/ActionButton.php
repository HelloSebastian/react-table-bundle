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

    public static function create($routeName, $name, $additionalClassNames = "", $options = array())
    {
        $options['routeName'] = $routeName;
        $options['name'] = $name;
        $options['additionalClassNames'] = $additionalClassNames;

        return new self($options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'name' => null,
            'classNames' => 'btn btn-xs btn-default ',
            'additionalClassNames' => '',
            'routeName' => null,
            'routeParam' => 'id'
        ));

        $resolver->setRequired('name');
        $resolver->setRequired('routeName');

        $resolver->setAllowedTypes('name', 'string');
        $resolver->setAllowedTypes('routeName', 'string');
        $resolver->setAllowedTypes('classNames', 'string');
        $resolver->setAllowedTypes('additionalClassNames', 'string');
        $resolver->setAllowedTypes('routeParam', ['string', 'array']);
    }

    public function buildArray()
    {
        return array(
            'name' => $this->options['name'],
            'classNames' => $this->options['classNames'] . $this->options['additionalClassNames']
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

    public function getRouteParam()
    {
        return $this->options['routeParam'];
    }

    public function getOptions()
    {
        return $this->options;
    }

}