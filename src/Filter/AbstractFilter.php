<?php


namespace HelloSebastian\ReactTableBundle\Filter;


use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractFilter
{
    /**
     * @var array
     */
    protected $options;

    public function __construct($options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'filterQuery' => null
        ));

        $resolver->setAllowedTypes('filterQuery', ['Closure', 'null']);
    }

    public function getFilterQueryCallback(): ?\Closure
    {
        return $this->options['filterQuery'];
    }

    public abstract function addFilterQuery(QueryBuilder $qb, $propertyPath, $value);

    public abstract function buildArray(): array;
}