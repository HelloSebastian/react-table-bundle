<?php


namespace HelloSebastian\ReactTableBundle\Columns;


use HelloSebastian\ReactTableBundle\Filter\AbstractFilter;
use HelloSebastian\ReactTableBundle\Filter\FilterFactory;
use HelloSebastian\ReactTableBundle\Filter\TextFilter;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\RouterInterface;

abstract class Column
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * @var ColumnBuilder
     */
    protected $columnBuilder;

    public function __construct($accessor, $type, $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();

        $options['propertyPath'] = $accessor;
        $options['isJoinField'] = (false !== strpos($accessor,"."));
        $options['accessor'] = str_replace(".", "_", $accessor);
        $options['type'] = $type;

        $this->options = $resolver->resolve($options);
    }

    /**
     * Sets in ColumnBuilder.
     *
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Sets in ColumnBuilder.
     *
     * @param ColumnBuilder $columnBuilder
     */
    public function setColumnBuilder(ColumnBuilder $columnBuilder)
    {
        $this->columnBuilder = $columnBuilder;
    }

    /**
     * @return array
     */
    public function buildColumnArray()
    {
        $filterOptions = $this->getToFilteredOutputOptionKeys();

        $filteredOptions = array_filter(
            $this->options,
            function ($key) use ($filterOptions) {
                return !in_array($key, $filterOptions);
            },
            ARRAY_FILTER_USE_KEY
        );

        //if no width is set, remove field
        if (is_null($filteredOptions['width'])) {
            unset($filteredOptions['width']);
        }

        if (!is_null($filteredOptions['filter'])) {
            /** @var AbstractFilter $filter */
            $filter = FilterFactory::create($filteredOptions['filter']);

            $filteredOptions['filter'] = array(
                'type' => $filter::TYPE,
                'options' => $filter->buildArray()
            );
        }

        return $filteredOptions;
    }

    protected function getToFilteredOutputOptionKeys() : array
    {
        return array(
            'emptyData',
            'propertyPath',
            'isJoinField',
            'sortQuery',
            'dataCallback'
        );
    }

    /**
     * @param $entity
     * @return array
     */
    public abstract function buildData($entity);

    /**
     * @param string $key
     * @return mixed
     * @throws \Exception
     */
    public function getOption($key)
    {
        if (!isset($this->options[$key])){
            throw new \Exception("Option not found");
        }

        return $this->options[$key];
    }

    /**
     * Replaces option.
     *
     * @param string $key
     * @param mixed $value
     */
    public function replaceOption($key, $value)
    {
        $options = $this->options;
        $options[$key] = $value;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getAccessor()
    {
        return $this->options['accessor'];
    }

    public function getPropertyPath()
    {
        if ($this->options['isJoinField']) {
            $parts = explode(".", $this->options['propertyPath']);
            $c = count($parts);
            return $parts[$c - 2] . '.' . $parts[$c - 1];
        }

        return $this->options['propertyPath'];
    }

    public function getFullPropertyPath()
    {
        return $this->options['propertyPath'];
    }

    public function getEmptyData()
    {
        return $this->options['emptyData'];
    }

    public function getSortQueryCallback()
    {
        return $this->options['sortQuery'];
    }

    public function getFilter()
    {
        return $this->options['filter'];
    }

    public function isJoinField()
    {
        return $this->options['isJoinField'];
    }

    public function getColumnBuilder()
    {
        return $this->columnBuilder;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'Header' => '',
            'accessor' => '',
            'type' => null,
            'width' => null,
            'filterable' => true,
            'sortable' => true,
            'show' => true,
            'filter' => array(TextFilter::class, array()),

            //internals fields
            'emptyData' => '',
            'propertyPath' => '',
            'isJoinField' => false,
            'sortQuery' => null,
            'dataCallback' => null
        ));

        $resolver->setRequired('accessor');
        $resolver->setRequired('propertyPath');
        $resolver->setRequired('type');

        $resolver->setAllowedTypes('Header', 'string');
        $resolver->setAllowedTypes('accessor', ['string']);
        $resolver->setAllowedTypes('type', ['string', 'null']);
        $resolver->setAllowedTypes('width', ['integer', 'null']);
        $resolver->setAllowedTypes('filterable', ['boolean']);
        $resolver->setAllowedTypes('sortable', ['boolean']);
        $resolver->setAllowedTypes('show', ['boolean']);
        $resolver->setAllowedTypes('filter', ['array', 'null']);
        $resolver->setAllowedTypes('emptyData', ['string']);
        $resolver->setAllowedTypes('propertyPath', ['string']);
        $resolver->setAllowedTypes('isJoinField', ['boolean']);
        $resolver->setAllowedTypes('sortQuery', ['Closure', 'null']);
        $resolver->setAllowedTypes('dataCallback', ['Closure', 'null']);
    }

}