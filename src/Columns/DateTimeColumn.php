<?php


namespace HelloSebastian\ReactTableBundle\Columns;


use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeColumn extends Column
{
    public function __construct($accessor, $options)
    {
        parent::__construct($accessor, "text", $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'format' => 'Y-m-d H:i:s',
        ));

        $resolver->setAllowedTypes('format', 'string');
    }

    /**
     * @inheritDoc
     */
    public function buildData($entity)
    {
        if (!is_null($this->options['dataCallback'])) {
            return $this->getOptions()['dataCallback']($entity);
        }

        if (!$this->propertyAccessor->isReadable($entity, $this->getFullPropertyPath())) {
            return $this->getEmptyData();
        }

        $dateTime = $this->propertyAccessor->getValue($entity, $this->getFullPropertyPath());
        if (is_null($dateTime)) {
            return $this->getEmptyData();
        }

        if (!$dateTime instanceof \DateTime) {
            throw new \Exception("DateTimeColumn :: Property should be DateTime. Type: " . gettype($dateTime));
        }

        return $dateTime->format($this->options['format']);
    }
}