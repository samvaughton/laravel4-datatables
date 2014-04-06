<?php

namespace Samvaughton\Ldt;

/**
 * Class DataTable - Configuration Over Convention
 *
 * This class pulls together the various components
 * of this library and ties them all together.
 *
 * @package Samvaughton\Ldt
 */
class DataTable
{

    /**
     * The actual requested parameters from the client side.
     *
     * @var Request
     */
    private $request;

    /**
     * @var Builder\BuilderInterface
     */
    private $builder;

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
     * @param Builder\BuilderInterface $builder
     * @param Request $request
     * @param array $columns
     */
    public function __construct(Builder\BuilderInterface $builder, Request $request,  array $columns)
    {
        $this->builder = $builder;
        $this->request = $request;
        $this->initializeColumns($columns);
    }

    /**
     * This is the final method that should be called after all the configuration. This will
     * return either the json encoded version or just an array of the data.
     *
     */
    public function make($jsonEncoded = true)
    {
        $results = $this->getResults();

        $data = array(
            "sEcho" => $this->request->getEcho(),
            "iTotalRecords" => $this->totalRecords,
            "iTotalDisplayRecords" => $this->filteredRecords,
            "aaData" => $this->parseResults($results),
        );

        return ($jsonEncoded) ? json_encode($data) : $data;
    }

    private function getResults()
    {
        /**
         * Set both counts to be the same right now as the request may not
         * involve any filtering.
         */
        $this->totalRecords = $this->filteredRecords = $this->builder->count();

        /**
         * Apply Pagination
         */
        $this->builder->paginate(
            $this->request->getPaginationStart(),
            $this->request->getPaginationLength()
        );

        /**
         * Apply Filtering
         */
        if ($this->request->isFilterable()) {
            $this->builder->filter($this->parseFilterableColumns());
            $this->filteredRecords = $this->builder->count();
        }

        /**
         * Apply Ordering
         */
        $this->builder->order($this->parseSortableColumns());

        return $this->builder->get();
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
            $filteredRow = $rowData = (array) $row;

            foreach($rowData as $column => $value) {
                $displayColumn = $this->getColumn($column);

                if ($displayColumn === false) {
                    unset($filteredRow[$column]);
                    continue;
                }

                $filteredRow[$column] = $this->stripInvalidChars(
                    $displayColumn->callRowProcessor($value, $filteredRow, $rowData)
                );
            }

            $filtered[] = $this->stripColumnKeys($filteredRow);
        }

        return $filtered;
    }

    /**
     * Strips invalid UTF-8 characters that can cause issues with json_encode()
     *
     * @param string $string
     * @return string
     */
    private function stripInvalidChars($string)
    {
        if (is_null($string) || empty($string) || $string == false) {
            return;
        }

        // Reject overly long 2 byte sequences, as well as characters above U+10000 and replace with ''
        $string = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]'.
            '|[\x00-\x7F][\x80-\xBF]+'.
            '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*'.
            '|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
            '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/S',
            '', $string);

        // Reject overly long 3 byte sequences and UTF-16 surrogates and replace with ''
        $string = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]'.
            '|\xED[\xA0-\xBF][\x80-\xBF]/S','', $string);

        $string = iconv('UTF-8', 'UTF-8//IGNORE', $string);

        $string = str_replace('�', '', $string);

        return $string;
    }

    /**
     * Converts the column numbers to actual column instances.
     *
     * @return array
     */
    private function parseSortableColumns()
    {
        $sortableColumns = $this->request->getSortableColumns();

        /**
         * Convert the column number to an actual column instance.
         */
        array_walk($sortableColumns, function(&$data) {
            $data['column'] = $this->columns[$data['column']];
        });

        return $sortableColumns;
    }

    /**
     * Converts the column numbers to actual column instances.
     *
     * @return array
     */
    private function parseFilterableColumns()
    {
        $filterableColumns = $this->request->getFilterableColumns();

        /**
         * Convert the column number to an actual column instance.
         */
        array_walk($filterableColumns['columns'], function(&$data) {
            $data['column'] = $this->columns[$data['column']];
        });

        return $filterableColumns;
    }

    /**
     * Converts any strings to the column class. This is a bit of a
     * code smell as we are instantiating a new column class and not
     * passing one. Should be OK though as we do accept an array of
     * these classes.
     *
     * @param array $columns
     */
    private function initializeColumns(array $columns)
    {
        foreach ($columns as $column) {
            $this->columns[] = (!$column instanceof Column) ? new Column((string) $column) : $column;
        }
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
     * @return \Samvaughton\Ldt\Builder\BuilderInterface
     */
    public function getBuilder()
    {
        return $this->builder;
    }

}