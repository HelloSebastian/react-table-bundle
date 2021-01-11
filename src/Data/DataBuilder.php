<?php


namespace HelloSebastian\ReactTableBundle\Data;


use HelloSebastian\ReactTableBundle\Columns\Column;
use HelloSebastian\ReactTableBundle\Columns\ColumnBuilder;

class DataBuilder
{
    /**
     * @var ColumnBuilder
     */
    private $columnBuilder;

    public function __construct(ColumnBuilder $columnBuilder)
    {
        $this->columnBuilder = $columnBuilder;
    }

    /**
     * @param $entities
     * @return array
     */
    public function buildDataAsArray($entities)
    {
        $data = array();
        foreach ($entities as $entity) {

            $row = array();

            /** @var Column $column */
            foreach ($this->columnBuilder->getColumns() as $column) {

                if (!is_null($column->getDataCallback())) {
                    $row[$column->getAccessor()] = $column->getDataCallback()($entity);
                    continue;
                }

                $row[$column->getAccessor()] = $column->buildData($entity);
            }

            $data[] = $row;
        }

        return $data;
    }

}