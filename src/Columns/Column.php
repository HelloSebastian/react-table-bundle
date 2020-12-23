<?php


namespace HelloSebastian\ReactTableBundle\Columns;


use HelloSebastian\ReactTableBundle\Filter\AbstractFilter;
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

    public function __construct($accessor, $type, $options)
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();

        $options['_propertyPath'] = $accessor;
        $options['_isJoinField'] = (false !== strpos($accessor,"."));
        $options['accessor'] = str_replace(".", "_", $accessor);
        $options['type'] = $type;

        $this->options = $resolver->resolve($options);
    }

    /**
     * @return array
     */
    public function buildColumnArray()
    {
        //return no internal fields
        $data = array_filter($this->options, function ($key) {
            return $key[0] != "_";
        }, ARRAY_FILTER_USE_KEY);

        //if no width is set, remove field
        if (is_null($data['width'])) {
            unset($data['width']);
        }

        if (!is_null($data['filter'])) {
            /** @var AbstractFilter $filter */
            $filter = new $data['filter'][0]($data['filter'][1]);

            $data['filter'] = array(
                'type' => $filter::TYPE,
                'options' => $filter->buildArray()
            );
        }

        return $data;
    }

    /**
     * @param $entity
     * @return array
     */
    public abstract function buildData($entity);

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
     * @param string $key
     * @return mixed
     * @throws \Exception
     */
    public function getOption(string $key)
    {
        if (!isset($this->options[$key])){
            throw new \Exception("Option not found");
        }

        return $this->options[$key];
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
        if ($this->options['_isJoinField']) {
            $parts = explode(".", $this->options['_propertyPath']);
            $c = count($parts);
            return $parts[$c - 2] . '.' . $parts[$c - 1];
        }

        return $this->options['_propertyPath'];
    }

    public function getFullPropertyPath()
    {
        return $this->options['_propertyPath'];
    }

    public function getEmptyData()
    {
        return $this->options['_emptyData'];
    }

    public function getSortQueryCallback()
    {
        return $this->options['_sortQuery'];
    }

    public function getFilter()
    {
        return $this->options['filter'];
    }

    public function isJoinField()
    {
        return $this->options['_isJoinField'];
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
            '_emptyData' => '',
            '_propertyPath' => '',
            '_isJoinField' => false,
            '_sortQuery' => null,
            '_dataCallback' => null
        ));

        $resolver->setRequired('accessor');
        $resolver->setRequired('_propertyPath');
        $resolver->setRequired('type');

        $resolver->setAllowedTypes('Header', 'string');
        $resolver->setAllowedTypes('accessor', ['string']);
        $resolver->setAllowedTypes('type', ['string', 'null']);
        $resolver->setAllowedTypes('width', ['integer', 'null']);
        $resolver->setAllowedTypes('filterable', ['boolean']);
        $resolver->setAllowedTypes('sortable', ['boolean']);
        $resolver->setAllowedTypes('show', ['boolean']);
        $resolver->setAllowedTypes('filter', ['array', 'null']);
        $resolver->setAllowedTypes('_emptyData', ['string']);
        $resolver->setAllowedTypes('_propertyPath', ['string']);
        $resolver->setAllowedTypes('_isJoinField', ['boolean']);
        $resolver->setAllowedTypes('_sortQuery', ['Closure', 'null']);
        $resolver->setAllowedTypes('_dataCallback', ['Closure', 'null']);
    }

}