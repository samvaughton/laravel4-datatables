<?php

namespace Samvaughton\Ldt;

class Column
{

    /**
     * Constants to define what sort of column is being represented.
     */
    const TYPE_STATIC = 'static';
    const TYPE_DYNAMIC = 'dynamic';

    /**
     * @var string The column name
     */
    private $name;

    /**
     * @var string The SQL representation of this column.
     */
    private $sqlColumn;

    /**
     * @var array
     */
    private $options = array();

    /**
     * @param $name string|array The column name.
     * @param $options array|null Options
     */
    public function __construct($name, array $options = array())
    {
        $this->initializeName($name);
        $this->initializeOptions($options);
    }

    /**
     * Processes the data based on a user defined function / class.
     *
     * @param $currentValue
     * @param $row
     * @param $originalRow
     * @return mixed
     */
    public function process($currentValue, $row, $originalRow)
    {
        if ($this->canProcess()) {
            $callback = $this->options['processor'];

            if ($callback instanceof ColumnProcessorInterface) {
                return $callback->run($currentValue, $row, $originalRow);
            }

            if (is_callable($callback)) {
                return $callback($currentValue, $row, $originalRow);
            }
        }

        return $currentValue;
    }

    /**
     * Returns whether this column has a callback or not.
     *
     * @return bool
     */
    public function canProcess()
    {
        return $this->options['processor'] !== false;
    }

    /**
     * Returns the name of the column.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSqlColumn()
    {
        return $this->sqlColumn;
    }

    /**
     * Returns the specified option.
     *
     * @param string $key
     * @return mixed
     */
    public function getOption($key)
    {
        return $this->options[$key];
    }

    /**
     * Returns whether the column can be searched or not.
     *
     * @return bool
     */
    public function isSearchable()
    {
        return $this->options['searchable'] && $this->isDynamic();
    }

    /**
     * Returns whether the column can be sorted or not.
     *
     * @return bool
     */
    public function isSortable()
    {
        return $this->options['sortable'] && $this->isDynamic();
    }

    /**
     * Is the columns data source static.
     *
     * @return bool
     */
    public function isStatic()
    {
        return $this->options['type'] === self::TYPE_STATIC;
    }

    /**
     * Is the columns data source dynamic? Ie from a database.
     *
     * @return bool
     */
    public function isDynamic()
    {
        return $this->options['type'] === self::TYPE_DYNAMIC;
    }

    /**
     * Takes a string or an array, if an array is passed then the sqlColumn
     * is set as the second element.
     *
     * @param string|array $name
     */
    private function initializeName($name)
    {
        if (is_array($name)) {
            $this->name = $name[0];
            $this->sqlColumn = $this->parseColumn($name[1]);
            return;
        }

        $this->name = (string) $name;
        $this->sqlColumn = (string) $name;
    }

    /**
     * Parses the column to only get the non-alias section.
     * 'customers.name AS custName' would return 'customers.name'
     *
     * @param string $column
     * @return string
     */
    private function parseColumn($column)
    {
        $sqlParts = preg_split("/ AS /i", $column);
        return $sqlParts[0];
    }

    /**
     * Initializes the default options with a chance to override them.
     *
     * @param array $options
     */
    private function initializeOptions(array $options)
    {
        $this->options = array_merge(array(
            'type' => self::TYPE_DYNAMIC,
            'sortable' => true,
            'searchable' => false,
            'processor' => false
        ), $options);
    }

}