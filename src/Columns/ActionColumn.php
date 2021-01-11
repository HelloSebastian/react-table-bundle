<?php


namespace HelloSebastian\ReactTableBundle\Columns;


use HelloSebastian\ReactTableBundle\Data\ActionButton;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionColumn extends Column
{

    public function __construct($accessor, $options)
    {
        parent::__construct($accessor ?? "actions", "action", $options);
    }

    private function buildButtons($buttons)
    {
        foreach ($buttons as $key => $button) {
            if (is_array($button)) {
                $buttons[$key] = new ActionButton($this, $button);
            }
        }

        $this->options['buttons'] = $buttons;
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
        $this->buildButtons($this->options['buttons']);
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
        $this->buildButtons($this->options['buttons']);
        $item = array();

        /**
         * @var int $key
         * @var ActionButton $button
         */
        foreach ($this->options['buttons'] as $key => $button) {
            $routeParams = array();
            foreach ($button->getRouteParams() as $param) {
                $routeParams[$param] = $this->propertyAccessor->getValue($entity, $param);
            }

            $item['route_' . $key] = $this->router->generate($button->getRouteName(), $routeParams);
        }

        return $item;
    }

}