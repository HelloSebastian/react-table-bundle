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

    public function __construct(RouterInterface $router, EntityManagerInterface $em, $defaultTableProps = array(), $defaultPersistenceOptions = array())
    {
        $this->router = $router;
        $this->em = $em;
        $this->defaultTableProps = $defaultTableProps;
        $this->defaultPersistenceOptions = $defaultPersistenceOptions;
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
            $this->defaultPersistenceOptions
        );
    }
}