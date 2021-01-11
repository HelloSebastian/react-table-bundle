<?php


namespace HelloSebastian\ReactTableBundle\Columns;


use HelloSebastian\ReactTableBundle\Filter\SelectFilter;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BooleanColumn extends Column
{
    public function __construct($accessor, $options)
    {
        parent::__construct($accessor, "boolean", $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'trueValue' => 'True',
            'falseValue' => 'False',
            'nullValue' => 'Null',
            'filter' => array(SelectFilter::class, array(
                'choices' => array(
                    true => 'True',
                    false => 'False',
                    "null" => 'Null'
                )
            )),
        ));

        $resolver->setAllowedTypes('trueValue', 'string');
        $resolver->setAllowedTypes('falseValue', 'string');
        $resolver->setAllowedTypes('nullValue', 'string');
    }

    public function buildData($entity)
    {
        if (!$this->propertyAccessor->isReadable($entity, $this->getFullPropertyPath())) {
            return $this->getEmptyData();
        }

        $booleanValue = $this->propertyAccessor->getValue($entity, $this->getFullPropertyPath());

        if (is_null($booleanValue)) {
            return $this->options['nullValue'];
        }

        if (!is_bool($booleanValue)) {
            throw new \Exception("Value should be boolean. Type: " . gettype($booleanValue));
        }

        return $this->options[$booleanValue ? 'trueValue' : 'falseValue'];
    }
}