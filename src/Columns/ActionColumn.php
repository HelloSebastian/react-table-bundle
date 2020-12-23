<?php


namespace HelloSebastian\ReactTableBundle\Columns;


use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionColumn extends Column
{
    public function __construct($accessor, $options)
    {
        parent::__construct($accessor ?? "actions", "action", $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'id' => 'actions',
            'buttons' => array(),
            'sortable' => false,
            'filterable' => false
        ));

        $resolver->setRequired('id');
        $resolver->setAllowedTypes('id', 'string');
    }

    public function buildColumnArray()
    {
        $options = parent::buildColumnArray();

        $buttons = array();
        foreach ($this->options['buttons'] as $key => $btn) {
            $buttons[$key] = $btn->buildArray();
        }

        return array_merge($options, array(
            'id' => $this->options['id'],
            'buttons' => $buttons
        ));
    }

    public function buildData($entity)
    {
        $item = array();

        foreach ($this->options['buttons'] as $key => $button) {
            $item['route_' . $key] = $this->router->generate($button->getRouteName(), array(
                $button->getRouteParam() => $this->propertyAccessor->getValue($entity, $button->getRouteParam())
            ));
        }

        return $item;
    }
}