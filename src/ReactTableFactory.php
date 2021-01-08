<?php


namespace HelloSebastian\ReactTableBundle;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;

class ReactTableFactory
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var array
     */
    private $defaultTableProps;

    /**
     * @var array
     */
    private $defaultPersistenceOptions;

    /**
     * @var array
     */
    private $defaultColumnOptions;

    public function __construct(RouterInterface $router, EntityManagerInterface $em, $defaultConfig = array())
    {
        $this->router = $router;
        $this->em = $em;

        $this->defaultTableProps = $defaultConfig['default_table_props'];
        $this->defaultPersistenceOptions = $defaultConfig['default_persistence_options'];
        $this->defaultColumnOptions = $defaultConfig['default_column_options'];
    }

    /**
     * @param $reactTableClass
     * @return ReactTable
     * @throws \Exception
     */
    public function create($reactTableClass)
    {
        if (!\is_string($reactTableClass)) {
            $type = \gettype($reactTableClass);

            throw new \Exception("ReactTableFactory::create(): String expected, {$type} given");
        }

        if (false === class_exists($reactTableClass)) {
            throw new \Exception("ReactTableFactory::create(): {$reactTableClass} does not exist");
        }

        return new $reactTableClass(
            $this->router,
            $this->em,
            $this->defaultTableProps,
            $this->defaultPersistenceOptions,
            $this->defaultColumnOptions
        );
    }
}