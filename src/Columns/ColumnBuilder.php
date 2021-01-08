<?php


namespace HelloSebastian\ReactTableBundle\Columns;


use Symfony\Component\Routing\RouterInterface;

class ColumnBuilder
{
    /**
     * @var Column[]
     */
    private $columns = array();

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var array
     */
    private $defaultColumnOptions;

    /**
     * ColumnBuilder constructor. Created in ReactTable.
     *
     * @param RouterInterface $router
     * @param $defaultColumnOptions
     */
    public function __construct(RouterInterface $router, $defaultColumnOptions)
    {
        $this->router = $router;
        $this->defaultColumnOptions = $defaultColumnOptions;
    }

    /**
     * Gets column by accessor. If no column found returns null.
     *
     * @param string $accessor
     * @return Column|null
     */
    public function getColumnByAccessor($accessor)
    {
        foreach ($this->columns as $column) {
            if ($column->getAccessor() == $accessor) {
                return $column;
            }
        }

        return null;
    }

    /**
     * Adds new column to table.
     *
     * @param string $accessor
     * @param string $columnClass
     * @param array $options
     * @param int|null $index
     * @return $this
     */
    public function add($accessor, $columnClass, $options = array(), $index = null)
    {
        /** @var Column $column */
        $column = new $columnClass($accessor, $options);
        $column->setColumnBuilder($this);
        $column->setRouter($this->router);

        if (is_null($index)) {
            $this->columns[count($this->columns)] = $column;
        } else {
            $this->columns[$index] = $column;
        }

        return $this;
    }

    /**
     * Marks column by accessor to remove.
     *
     * @param string $accessor
     */
    public function remove($accessor)
    {
        foreach ($this->columns as $key => $column) {
            if ($column->getAccessor() == $accessor) {
                unset($this->columns[$key]);
            }
        }
    }

    /**
     * Loop over all columns and build array options for react table.
     *
     * @return array
     */
    public function buildColumnsArray()
    {
        //sort by key
        ksort($this->columns);

        $data = array();
        foreach ($this->columns as $column) {
            $data[] = $column->buildColumnArray();
        }

        return $data;
    }

    /**
     * Gets all columns sorted by keys.
     *
     * @return Column[]
     */
    public function getColumns()
    {
        //sort by key
        ksort($this->columns);

        return $this->columns;
    }

    public function getDefaultColumnOptions()
    {
        return $this->defaultColumnOptions;
    }

}