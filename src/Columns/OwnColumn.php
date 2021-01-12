<?php


namespace HelloSebastian\ReactTableBundle\Columns;


use Symfony\Component\OptionsResolver\OptionsResolver;

class OwnColumn extends Column
{
    public function __construct($accessor, $options)
    {
        parent::__construct($accessor, "own", $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'sortable' => false,
            'filterable' => false
        ));

        $resolver->setRequired('dataCallback');
    }

    /**
     * @inheritDoc
     */
    public function buildData($entity)
    {
        throw new \Exception("Should not called because dataCallback is required.");
    }
}