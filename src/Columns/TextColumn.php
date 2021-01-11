<?php


namespace HelloSebastian\ReactTableBundle\Columns;

class TextColumn extends Column
{
    public function __construct($accessor, $options)
    {
        parent::__construct($accessor, "text", $options);
    }

    public function buildData($entity)
    {
        if (!$this->propertyAccessor->isReadable($entity, $this->getFullPropertyPath())) {
            return $this->getEmptyData();
        }

        return $this->propertyAccessor->getValue($entity, $this->getFullPropertyPath());
    }
}