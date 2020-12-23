<?php


namespace HelloSebastian\ReactTableBundle\Filter;


use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectFilter extends AbstractFilter
{
    public const TYPE = "select";

    public function __construct($options)
    {
        parent::__construct($options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'choices' => array(),
            'placeholder' => 'All'
        ));

        $resolver->setAllowedTypes('choices', 'array');
        $resolver->setAllowedTypes('placeholder', 'string');
    }

    public function addFilterQuery(QueryBuilder $qb, $propertyPath, $value)
    {
        if (is_null($value) || $value == "null") {
            $qb->andWhere($propertyPath . " IS NULL");
            return;
        }

        $qb->andWhere($propertyPath . ' = :' . str_replace(".", "_", $propertyPath))
            ->setParameter(str_replace(".", "_", $propertyPath), $value);
    }

    public function buildArray(): array
    {
        return array(
            'choices' => $this->options['choices'],
            'placeholder' => $this->options['placeholder']
        );
    }


}