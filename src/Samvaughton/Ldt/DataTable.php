<?php

namespace Samvaughton\Ldt;

/**
 * Class DataTable - Configuration over Convention
 *
 * Combines everything together to actually output the data.
 *
 * @package Samvaughton\Ldt
 */
class DataTable
{

    /**
     * The actual requested parameters from the client side.
     *
     * @var array
     */
    private $input;

    /**
     * The actual prebuilt query from Laravel
     */
    private $query;

    /**
     * Holds the columns that will be rendered and displayed
     *
     * @var Column[]
     */
    private $columns;

    /**
     * The total amount of records the query could possibly return (before pagination).
     *
     * @var int
     */
    private $totalRecords;

    /**
     * The amount of filtered records.
     *
     * @var int
     */
    private $filteredRecords;

    /**
     * Special column parameters that DataTables ignores when displaying data.
     *
     * @var array
     */
    private $specialColumnParameters = array('DT_RowId', 'DT_RowClass');

    /**
     * The column parameter can either accept a string or a Column class,
     * a string will be converted into a column class with no extra settings.
     *
     * @param $input array The requested parameters.
     * @param $query mixed The eloquent/fluent query
     * @param array $columns Column|string
     */
    public function __construct(array $input, $query, array $columns)
    {
        $this->input = $input;
        $this->query = $query;
        $this->initializeColumns($columns);
    }

    /**
     * This is the final method that should be called after all the configuration. This will
     * return either the json encoded version or just an array of the data.
     *
     */
    public function make($jsonEncoded = false)
    {
        $results = $this->getResults();

        $data = array(
            "sEcho" => (int) $this->getParam('sEcho', 0),
            "iTotalRecords" => $this->getTotalCount(),
            "iTotalDisplayRecords" => $this->getFilterCount(),
            "aaData" => $this->parseResults($results),
        );

        return ($jsonEncoded) ? json_encode($data) : $data;
    }

    /**
     * Converts any strings to the column class. This is a bit of a
     * code smell as we are instantiating a new column class and not
     * passing one. Should be OK though as we do accept an array of
     * these classes.
     *
     * @param array $columns
     */
    protected function initializeColumns(array $columns)
    {
        foreach ($columns as $column) {
            $this->columns[] = (!$column instanceof Column) ? new Column((string) $column) : $column;
        }
    }

    /**
     * Returns the database result set.
     *
     * @return array
     */
    private function getResults()
    {
        $this->fetchTotalCount();

        $this->applyPagination();
        $this->applyOrdering();

        $this->applyFiltering();

        return $this->query->get();
    }

    /**
     * Removes any columns from the result set that are not in the column array defined when
     * this class was instantiated. This method also applies any callbacks and rendering of the data.
     *
     * @param array $results
     * @return array
     */
    private function parseResults(array $results)
    {
        $filtered = array();

        $this->appendStaticColumns($results);

        foreach($results as $row) {
            // We want to keep an original copy of the rowData so the column manipulation can utilise it.
            $rowData = (array) $row;
            $filteredRow = $rowData;

            foreach($rowData as $column => $value) {
                $displayColumn = $this->getColumn($column);

                if ($displayColumn === false) {
                    unset($filteredRow[$column]);
                    continue;
                }

                $filteredRow[$column] = $displayColumn->render($value, $filteredRow, $rowData);
            }

            $filtered[] = $this->stripColumnKeys($filteredRow);

        }

        return $filtered;
    }

    /**
     * Removes the column keys from the array to return just the values,
     * makes exceptions for DT_ as they are special columns that DataTables
     * can utilise.
     *
     * @param array $row
     * @return array
     */
    private function stripColumnKeys(array $row)
    {
        $saved = array();

        foreach($this->specialColumnParameters as $param) {
            if (isset($row[$param])) $saved[$param] = $row[$param];
        }

        $values = array_values(array_diff_assoc($row, $this->specialColumnParameters));

        return array_merge($values, $saved);
    }

    /**
     * Adds on the static columns to the data set. A static column is one
     * that does not originate from the data source.
     *
     * @param array $results
     */
    private function appendStaticColumns(array $results)
    {
        $static = $this->getStaticColumns();

        foreach($results as $result) {
            foreach($static as $column) {
                $result->$column = "";
            }
        }
    }

    /**
     * Returns all the columns that are defined as static.
     *
     * @return Column[]
     */
    private function getStaticColumns()
    {
        $static = array();
        foreach($this->columns as $column) {
            if ($column->isStatic()) $static[] = $column;
        }

        return $static;
    }

    /**
     * Counts the total amount of records from the database.
     */
    private function fetchTotalCount()
    {
        $dupe = clone $this->query; // Clone so we don't reset the original queries select.
        $this->totalRecords = $this->filteredRecords = (int) $dupe->count();
    }

    /**
     * Counts the total amount of records from the database.
     */
    private function fetchFilterCount()
    {
        $dupe = clone $this->query; // Clone so we don't reset the original queries select.
        $this->filteredRecords = (int) $dupe->count();
    }

    /**
     * Applies pagination to the query based on the client side request.
     */
    private function applyPagination()
    {
        $this->query
            ->skip($this->getParam('iDisplayStart', 0))
            ->take($this->getParam('iDisplayLength', 10))
        ;
    }

    /**
     * Applies ordering to the query based on the client side request.
     */
    private function applyOrdering()
    {
        $columnsToSort = $this->getParam('iSortingCols', 1);

        for($colNum = 0; $colNum < $columnsToSort; $colNum++) {
            if (is_null($this->getParam("iSortCol_{$colNum}", null))) continue;

            $direction = $this->getParam("sSortDir_{$colNum}", "asc");
            $column = $this->columns[$this->getParam("iSortCol_{$colNum}", 0)];

            if ($column->isDynamic()) {
                $this->query->orderBy(
                    $column->getSqlColumn(),
                    $direction
                );
            }
        }
    }

    /**
     * Applies filtering to the query based on which columns are searchable.
     */
    private function applyFiltering()
    {
        $mainTerm = $this->getParam('sSearch');
        if (empty($mainTerm)) return;

        $columnsToSort = $this->getParam('iColumns', 1);

        $this->query->where(function ($query) use ($mainTerm, $columnsToSort) {
            for($colNum = 0; $colNum < $columnsToSort; $colNum++) {
                $column = $this->columns[$colNum];
                if (!$column->isSearchable()) continue;
                if ($this->getParam("bSearchable_{$colNum}") != true) continue;

                $specificTerm = $this->getParam("sSearch_{$colNum}");
                $term = (empty($specificTerm)) ? $mainTerm : $specificTerm;

                $query->orWhere($column->getSqlColumn(), "LIKE", "%{$term}%");
            }
        });

        $this->fetchFilterCount();
    }

    /**
     * Returns the column if it exists, if not then return false.
     *
     * @param $key string
     * @return Column
     */
    private function getColumn($key)
    {
        foreach($this->columns as $column) {
            if ($column->getName() === $key) return $column;
        }

        return false;
    }

    /**
     * Returns the total amount of records the filtered query returns.
     *
     * @return int
     */
    private function getFilterCount()
    {
        return $this->filteredRecords;
    }

    /**
     * Returns the total amount of records the generated query can possibly return.
     *
     * @return int
     */
    private function getTotalCount()
    {
        return $this->totalRecords;
    }

    /**
     * Simply returns a request parameter. If a default is specified then
     * if the key does not exist that parameter will be returned. If
     * default is left as null then false will be returned.
     *
     * @param $key string
     * @param $default string
     * @return string
     */
    private function getParam($key, $default = null)
    {
        if (isset($this->input[$key])) return $this->input[$key];

        return ($default == null) ? false : $default;
    }

}