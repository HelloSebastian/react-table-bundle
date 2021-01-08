<?php


namespace HelloSebastian\ReactTableBundle\Filter;


class FilterFactory
{
    /**
     * @param array $filterOption
     * @return AbstractFilter
     * @throws \Exception
     */
    public static function create($filterOption)
    {
        $filterClass = $filterOption[0];
        $options = $filterOption[1];

        if (! \is_string($filterClass)) {
            $type = \gettype($filterClass);
            throw new \Exception("FilterFactory::create(): String expected, {$type} given");
        }

        if (false === class_exists($filterClass)) {
            throw new \Exception("FilterFactory::create(): {$filterClass} does not exist");
        }

        if (!is_array($options)) {
            $type = \gettype($options);
            throw new \Exception("FilterFactory::create(): Array expected, {$type} given");
        }

        return new $filterClass($options);
    }
}