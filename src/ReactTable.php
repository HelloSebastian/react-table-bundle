<?php


namespace HelloSebastian\ReactTableBundle;

use HelloSebastian\ReactTableBundle\Columns\ColumnBuilder;
use HelloSebastian\ReactTableBundle\Data\DataBuilder;
use HelloSebastian\ReactTableBundle\Query\DoctrineQueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

abstract class ReactTable
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var ColumnBuilder
     */
    private $columnBuilder;

    /**
     * @var DataBuilder
     */
    private $dataBuilder;

    /**
     * @var DoctrineQueryBuilder
     */
    private $doctrineQueryBuilder;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var array
     */
    protected $tableProps;

    /**
     * @var array
     */
    protected $persistenceOptions;

    /**
     * @var array
     */
    protected $requestData;


    public function __construct(RouterInterface $router, EntityManagerInterface $em, $defaultTableProps, $defaultPersistenceOptions)
    {
        $this->router = $router;
        $this->em = $em;
        $this->tableProps = $defaultTableProps;
        $this->persistenceOptions = $defaultPersistenceOptions;

        $this->columnBuilder = new ColumnBuilder($router);
        $this->dataBuilder = new DataBuilder($this->columnBuilder);
        $this->doctrineQueryBuilder = new DoctrineQueryBuilder($this->em, $this->getEntityClass(), $this->columnBuilder);
    }

    /**
     * @return boolean
     * @throws \Exception
     */
    public function isCallback()
    {
        return $this->requestData['isCallback'];
    }

    public function handleRequest(Request $request)
    {
        $requestDataResolver = new OptionsResolver();
        $requestDataResolver->setDefaults(array(
            'isCallback' => false,
            'filtered' => array(),
            'sorted' => array(),
            'page' => 1,
            'pageSize' => 25,
            'route' => null
        ));

        $requestData = array();
        if ($request->isMethod("POST")) {
            $requestData = json_decode($request->getContent(), true);
        }

        $requestData['route'] = $request->getRequestUri();
        $this->requestData = $requestDataResolver->resolve($requestData);
    }

    /**
     * @return JsonResponse
     */
    public function getResponse()
    {
        return JsonResponse::create($this->buildTable());
    }

    /**
     * @return false|string
     */
    public function createView()
    {
        //build columns structure without data
        $this->buildColumns($this->columnBuilder);

        //set up table props resolver
        $tablePropsResolver = new OptionsResolver();
        $this->configureTableProps($tablePropsResolver);

        //set up persistence options resolver
        $persistenceOptionsResolver = new OptionsResolver();
        $this->configurePersistenceOptions($persistenceOptionsResolver);

        return json_encode(array(
            'url' => $this->requestData['route'], //url for callbacks
            'columns' => $this->columnBuilder->buildColumnsArray(),
            'tableName' => $this->getTableName(),
            'tableProps' => $tablePropsResolver->resolve($this->tableProps),
            'persistenceOptions' => $persistenceOptionsResolver->resolve($this->persistenceOptions)
        ));
    }

    private function buildTable()
    {
        $this->buildColumns($this->columnBuilder);
        $entities = $this->doctrineQueryBuilder->getSubsetData($this->requestData);

        return array(
            'data' => $this->dataBuilder->buildDataAsArray($entities),
            'totalCount' => $this->doctrineQueryBuilder->getTotalCount()
        );
    }

    public function configureTableProps(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'showPagination' => true,
            'showPaginationTop' => false,
            'showPaginationBottom' => true,
            'showPageSizeOptions' => true,
            'pageSizeOptions' => array(5, 10, 20, 25, 50, 100),
            'defaultPageSize' => 20,
            'showPageJump' => true,
            'collapseOnSortingChange' => true,
            'collapseOnPageChange' => true,
            'collapseOnDataChange' => true,
            'freezeWhenExpanded' => false,
            'sortable' => true,
            'multiSort' => true,
            'resizable' => true,
            'filterable' => true,
            'defaultSortDesc' => false,
            'className' => '',
            'previousText' => 'Previous',
            'nextText' => 'Next',
            'loadingText' => 'Loading...',
            'noDataText' => 'No rows found',
            'pageText' => 'Page',
            'ofText' => 'of',
            'rowsText' => 'Rows',
            'pageJumpText' => 'jump to page',
            'rowsSelectorText' => 'rows per page'
        ));
    }

    public function configurePersistenceOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'resized' => true,
            'filtered' => true,
            'sorted' => true,
            'page' => true,
            'page_size' => true
        ));

        $resolver->setAllowedTypes('resized', 'boolean');
        $resolver->setAllowedTypes('filtered', 'boolean');
        $resolver->setAllowedTypes('sorted', 'boolean');
        $resolver->setAllowedTypes('page', 'boolean');
        $resolver->setAllowedTypes('page_size', 'boolean');
    }

    public function getColumnBuilder()
    {
        return $this->columnBuilder;
    }

    public function getQueryBuilder()
    {
        return $this->doctrineQueryBuilder->getQueryBuilder();
    }

    public function setTableProps($tableProps)
    {
        $this->tableProps = array_merge($this->tableProps, $tableProps);
    }

    public function setPersistenceOptions($persistenceOptions)
    {
        $this->persistenceOptions = array_merge($this->persistenceOptions, $persistenceOptions);
    }

    public function getTableName()
    {
        $className = get_class($this);
        $className = strtolower($className);
        $className = str_replace("\\", "_", $className);

        return $className;
    }

    /**
     * @param ColumnBuilder $columnBuilder
     */
    protected abstract function buildColumns(ColumnBuilder $columnBuilder);

    protected abstract function getEntityClass(): string;

}