<?php


namespace HelloSebastian\ReactTableBundle;

use HelloSebastian\ReactTableBundle\Columns\ColumnBuilder;
use HelloSebastian\ReactTableBundle\Data\DataBuilder;
use HelloSebastian\ReactTableBundle\Query\DoctrineQueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use HelloSebastian\ReactTableBundle\Response\ReactTableResponse;
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
     * @var DoctrineQueryBuilder
     */
    private $doctrineQueryBuilder;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ReactTableResponse
     */
    private $reactTableResponse;

    /**
     * @var array
     */
    protected $tableProps;

    /**
     * @var array
     */
    protected $persistenceOptions;


    public function __construct(RouterInterface $router, EntityManagerInterface $em, $defaultTableProps, $defaultPersistenceOptions, $defaultColumnOptions)
    {
        $this->router = $router;
        $this->em = $em;
        $this->tableProps = $defaultTableProps;
        $this->persistenceOptions = $defaultPersistenceOptions;

        $this->columnBuilder = new ColumnBuilder($router, $defaultColumnOptions);
        $this->doctrineQueryBuilder = new DoctrineQueryBuilder($this->em, $this->getEntityClass(), $this->columnBuilder);

        $dataBuilder = new DataBuilder($this->columnBuilder);
        $this->reactTableResponse = new ReactTableResponse($this->doctrineQueryBuilder, $dataBuilder);

        $this->buildColumns($this->columnBuilder);
    }

    protected abstract function buildColumns(ColumnBuilder $builder);

    protected abstract function getEntityClass(): string;

    /**
     * @param string $callbackUrl
     */
    public function setCallbackUrl($callbackUrl)
    {
        $this->reactTableResponse->setCallbackUrl($callbackUrl);
    }

    /**
     * @return boolean
     */
    public function isCallback()
    {
        return $this->reactTableResponse->isCallback();
    }

    /**
     * Handles request and gets request information.
     *
     * @param Request $request
     */
    public function handleRequest(Request $request)
    {
        $this->reactTableResponse->handleRequest($request);
    }

    /**
     * Gets data depends on paging, filtering and sorting.
     *
     * @return JsonResponse
     */
    public function getResponse()
    {
        return new JsonResponse($this->reactTableResponse->getData());
    }

    /**
     * Returns table structure as encoded array.
     *
     * @return false|string
     */
    public function createView()
    {
        //set up table props resolver
        $tablePropsResolver = new OptionsResolver();
        $this->configureTableProps($tablePropsResolver);

        //set up persistence options resolver
        $persistenceOptionsResolver = new OptionsResolver();
        $this->configurePersistenceOptions($persistenceOptionsResolver);

        return json_encode(array(
            'url' => $this->reactTableResponse->getCallbackUrl(),
            'columns' => $this->columnBuilder->buildColumnsArray(),
            'tableName' => $this->getTableName(),
            'tableProps' => $tablePropsResolver->resolve($this->tableProps),
            'persistenceOptions' => $persistenceOptionsResolver->resolve($this->persistenceOptions)
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

    public function setPersistenceOptions($persistenceOptions)
    {
        $this->persistenceOptions = array_merge($this->persistenceOptions, $persistenceOptions);
    }

    private function getTableName()
    {
        $className = get_class($this);
        $className = strtolower($className);
        $className = str_replace("\\", "_", $className);

        return $className;
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
}