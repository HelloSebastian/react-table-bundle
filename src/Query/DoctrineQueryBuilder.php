<?php


namespace HelloSebastian\ReactTableBundle\Query;

use Doctrine\ORM\EntityManagerInterface;
use HelloSebastian\ReactTableBundle\Columns\ColumnBuilder;
use HelloSebastian\ReactTableBundle\Filter\AbstractFilter;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;

class DoctrineQueryBuilder
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var QueryBuilder
     */
    private $qb;

    /**
     * @var ColumnBuilder
     */
    private $columnBuilder;

    /**
     * @var int
     */
    private $totalCountAfterFiltering;

    /**
     * @var string
     */
    private $entityName;

    /**
     * @var string
     */
    private $entityShortName;


    public function __construct(EntityManagerInterface $em, $entityName, ColumnBuilder $columnBuilder)
    {
        $this->em = $em;
        $this->entityName = $entityName;
        $this->columnBuilder = $columnBuilder;

        $metadata = $this->em->getMetadataFactory()->getMetadataFor($this->entityName);
        $this->entityShortName = $this->getSafeName(strtolower($metadata->getReflectionClass()->getShortName()));
        $this->qb = $this->em->getRepository($this->entityName)->createQueryBuilder($this->entityShortName);
    }

    private function setupJoinFields()
    {
        $joins = array();

        foreach ($this->columnBuilder->getColumns() as $column) {
            if ($column->isJoinField()) {
                $currentPart = $this->qb->getRootAliases()[0];
                $currentAlias = $currentPart;
                $propertyPath = $column->getFullPropertyPath();
                $parts = explode(".", $propertyPath);

                while (\count($parts) > 1) {
                    $previousPart = $currentPart;
                    $previousAlias = $currentAlias;

                    $currentPart = array_shift($parts);
                    $currentAlias = ($previousPart === $this->qb->getRootAliases()[0] ? '' : $previousPart . '_') . $currentPart;

                    if (!\array_key_exists($previousAlias . '.' . $currentPart, $joins)) {
                        $joins[$previousAlias . '.' . $currentPart] = $currentPart;
                    }
                }
            }
        }

        foreach ($joins as $key => $value) {
            $this->qb->leftJoin($key, $value);
        }
    }

    /**
     * Executes query and returns all data.
     *
     * @return array
     */
    public function getAllData()
    {
        $this->setupJoinFields();

        $data = $this->qb->getQuery()->getResult();
        $this->totalCountAfterFiltering = count($data);
        return $data;
    }

    /**
     * Adds filtering and sorting to query, executes the query and returns data.
     *
     * @param array $requestData
     * @return mixed
     */
    public function getSubsetData(array $requestData)
    {
        $this->setupJoinFields();

        if (count($requestData['filtered']) > 0) {
            $this->filtering($requestData['filtered']);
        }

        if (count($requestData['sorted']) > 0) {
            $this->sorting($requestData['sorted']);
        }

        $this->setTotalCountAfterFiltering();

        $this->qb
            ->setFirstResult($requestData['page'] * $requestData['pageSize'])
            ->setMaxResults($requestData['pageSize']);

        return $this->qb->getQuery()->getResult();
    }

    /**
     * Adds sorting to query.
     *
     * @param array $sorts
     */
    private function sorting($sorts)
    {
        foreach ($sorts as $sort) {
            $column = $this->columnBuilder->getColumnByAccessor($sort['id']);

            if ($callback = $column->getSortQueryCallback()) {
                $callback($this->qb, $sort['id'], $sort['desc'] ? 'DESC' : 'ASC');
            } else if ($column->isJoinField()) {
                $this->addSortingQuery($this->qb, $column->getPropertyPath(), $sort['desc'] ? 'DESC' : 'ASC');
            } else {
                $this->addSortingQuery($this->qb, $this->qb->getRootAliases()[0] . '.' . $sort['id'], $sort['desc'] ? 'DESC' : 'ASC');
            }
        }
    }

    /**
     * Adds filtering to query.
     *
     * @param array $filters
     */
    private function filtering($filters)
    {
        foreach ($filters as $filter) {
            $column = $this->columnBuilder->getColumnByAccessor($filter['id']);

            if ($columnFilterArray = $column->getFilter()) {

                /** @var AbstractFilter $columnFilter */
                $columnFilter = new $columnFilterArray[0]($columnFilterArray[1]);

                if ($callback = $columnFilter->getFilterQueryCallback()) {
                    $callback($this->qb, $filter['id'], $filter['value']);
                } else if ($column->isJoinField()) {
                    $columnFilter->addFilterQuery($this->qb, $column->getPropertyPath(), $filter['value']);
                } else {
                    $columnFilter->addFilterQuery($this->qb, $this->qb->getRootAliases()[0] . '.' . $filter['id'], $filter['value']);
                }
            }
        }
    }

    /**
     * Executes sub query to count data after filtering was added to query.
     */
    private function setTotalCountAfterFiltering()
    {
        try {
            $qb = clone $this->qb;
            $this->totalCountAfterFiltering = $qb->select('COUNT(' . $qb->getRootAliases()[0] . '.id)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException $e) {
            $this->totalCountAfterFiltering = 0;
        } catch (NonUniqueResultException $e) {
            $this->totalCountAfterFiltering = 0;
        }
    }

    private function addSortingQuery(QueryBuilder $qb, $propertyPath, $direction)
    {
        $qb->addOrderBy($propertyPath, $direction);
    }

    /**
     * Returns total count of fetched data.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->totalCountAfterFiltering;
    }

    public function getQueryBuilder()
    {
        return $this->qb;
    }

    /**
     * Get safe name.
     *
     * @param $name
     *
     * @return string
     */
    private function getSafeName($name)
    {
        try {
            $reservedKeywordsList = $this->em->getConnection()->getDatabasePlatform()->getReservedKeywordsList();
            $isReservedKeyword = $reservedKeywordsList->isKeyword($name);
        } catch (\Exception $exception) {
            $isReservedKeyword = false;
        }

        return $isReservedKeyword ? "_{$name}" : $name;
    }
}