<?php

namespace Samvaughton\Ldt;

/**
 * Class Request
 *
 * Wraps around the request parameters and provides a nice interface to interact with them.
 *
 * @package Samvaughton\Ldt
 */
class Request
{

    /**
     * @var array
     */
    private $request = array();

    /**
     * @param array $request
     */
    public function __construct(array $request)
    {
        $this->request = $request;
    }

    /**
     * @return int
     */
    public function getEcho()
    {
        return (int) $this->get('sEcho');
    }

    /**
     * @return int
     */
    public function getPaginationStart()
    {
        return (int) $this->get('iDisplayStart', 0);
    }

    /**
     * @return int
     */
    public function getPaginationLength()
    {
        return (int) $this->get('iDisplayLength', 10);
    }

    /**
     * @return array
     */
    public function getSortableColumns()
    {
        $columns = array();

        for ($colNum = 0; $colNum < $this->get('iSortingCols', 1); $colNum++) {
            if (is_null($this->get("iSortCol_{$colNum}", null))) continue;

            $columns[] = array(
                'column' => $this->get("iSortCol_{$colNum}", 0),
                'direction' => $this->get("sSortDir_{$colNum}", "asc"),
                'sortable' => ($this->get("bSortable_{$colNum}", false) == true) ? true : false
            );
        }

        return $columns;
    }

    /**
     * @return bool
     */
    public function isFilterable()
    {
        return !empty($this->request['sSearch']);
    }

    /**
     * @return array
     */
    public function getFilterableColumns()
    {
        $filter = array('term' => $this->get('sSearch', ''), 'columns' => array());

        for($colNum = 0; $colNum < $this->get('iColumns', 1); $colNum++) {
            $filter['columns'][] = array(
                'column' => $colNum,
                'term' => $this->get("sSearch_{$colNum}", ''),
                'searchable' => ($this->get("bSearchable_{$colNum}") == 'true') ? true : false
            );
        }

        return $filter;
    }

    /**
     * Retrieves an individual parameter with an optional default.
     *
     * @param $key
     * @param string $default
     * @return string
     */
    private function get($key, $default = '')
    {
        return (isset($this->request[$key])) ? $this->request[$key] : $default;
    }

    /**
     * Change a request parameter.
     *
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $this->request[$key] = $value;
    }

}