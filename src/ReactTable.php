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
    protected $requestData;


    public function __construct(RouterInterface $router, EntityManagerInterface $em, $defaultTableProps)
    {
        $this->router = $router;
        $this->em = $em;
        $this->tableProps = $defaultTableProps;

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
        $this->buildColumns($this->columnBuilder);

        $resolver = new OptionsResolver();
        $this->configureTableProps($resolver);

        return json_encode(array(
            'url' => $this->requestData['route'],
            'columns' => $this->columnBuilder->buildColumnsArray(),
            'tableProps' => $resolver->resolve($this->tableProps)
        ));
    }

    /**
     * Builds columns of table, handles query for data and builds data array.
     *
     * @return array
     */
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

    /**
     * @param ColumnBuilder $columnBuilder
     */
    protected abstract function buildColumns(ColumnBuilder $columnBuilder);

    protected abstract function getEntityClass(): string;

}