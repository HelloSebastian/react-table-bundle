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

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
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
     * @param string $attribute
     * @param string $columnClass
     * @param array $options
     * @param int|null $index
     * @return $this
     */
    public function add($attribute, $columnClass, $options = array(), $index = null)
    {
        /** @var Column $column */
        $column = new $columnClass($attribute, $options);
        $column->setRouter($this->router);

        if (is_null($index)) {
            $this->columns[count($this->columns)] = $column;
        } else {
            $this->columns[$index] = $column;
        }

        return $this;
    }

    /**
     * Loop over all columns and build array options for react table.
     *
     * @return array
     */
    public function buildColumnsArray()
    {
        ksort($this->columns);
        $data = array();
        foreach ($this->columns as $column) {
            $data[] = $column->buildColumnArray();
        }

        return $data;
    }

    /**
     * @return Column[]
     */
    public function getColumns()
    {
        ksort($this->columns);
        return $this->columns;
    }

}