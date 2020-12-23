<?php


namespace HelloSebastian\ReactTableBundle\Filter;


use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TextFilter extends AbstractFilter
{
    public const TYPE = "text";

    public function __construct($options)
    {
        parent::__construct($options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'placeholder' => ''
        ));

        $resolver->setAllowedTypes('placeholder', 'string');
    }


    public function addFilterQuery(QueryBuilder $qb, $propertyPath, $value)
    {
        $qb->andWhere($propertyPath . ' LIKE :' . str_replace(".", "_", $propertyPath))
            ->setParameter(str_replace(".", "_", $propertyPath), '%' . $value . '%');
    }

    public function buildArray(): array
    {
        return array(
            'placeholder' => $this->options['placeholder']
        );
    }


}