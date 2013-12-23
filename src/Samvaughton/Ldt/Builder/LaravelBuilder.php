<?php

namespace Samvaughton\Ldt\Builder;

class LaravelBuilder implements BuilderInterface
{

    /**
     * @var \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder;
     */
    private $query;

    /**
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder; $query
     */
    public function __construct($query)
    {
        if (!$query instanceof \Illuminate\Database\Query\Builder &&
            !$query instanceof \Illuminate\Database\Eloquent\Builder)
        {
            throw new \InvalidArgumentException("The query must either be an instance of fluent or eloquent.");
        }

        $this->query = $query;
    }

    /**
     * Counts the total amount of records from the database.
     *
     * @return int
     */
    public function count()
    {
        $dupe = clone $this->query; // Clone so we don't reset the original queries select.
        return (int) $dupe->count();
    }

    /**
     * Applies pagination to the query based on the client side request.
     *
     * @param string $start
     * @param string $length
     */
    public function paginate($start, $length)
    {
        $this->query->skip($start);
        $this->query->take($length);
    }

    /**
     * Applies ordering to the query based on the client side request.
     *
     * @param array $orderData
     */
    public function order(array $orderData)
    {
        foreach($orderData as $colData) {
            /** @var \Samvaughton\Ldt\Column $column */
            $column = $colData['column'];
            $direction = $colData['direction'];

            // Check if this column is sortable
            if (!$column->isSortable() || !$colData['sortable']) continue;

            $this->query->orderBy($column->getSqlColumn(), $direction);
        }
    }

    /**
     * Applies filtering to the query based on which columns are searchable.
     *
     * @note: Cannot separate this into different methods as PHP 5.3 does not support
     * class scope injection into closures.
     *
     * @param array $filterData
     */
    public function filter(array $filterData)
    {
        $this->query->where(function ($query) use ($filterData) {
            /** @var \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query */
            foreach($filterData['columns'] as $colData) {
                /** @var \Samvaughton\Ldt\Column $column */
                $column = $colData['column'];

                // See if this column is searchable
                if (!$column->isSearchable() || !$colData['searchable']) continue;

                // If the individual column term is empty, use the main term
                $term = (empty($colData['term'])) ? $filterData['term'] : $colData['term'];

                // Check if we have a callback, if so lets use it
                if ($column->canCallFilterProcessor()) {
                    $term = $column->callFilterProcessor($term);
                }

                // Actually apply the filter
                $query->orWhere($column->getSqlColumn(), "LIKE", "%{$term}%");
            }
        });
    }

    /**
     * Returns the database result set.
     *
     * @return array
     */
    public function get()
    {
        $results = $this->query->get();

        if ($results instanceof \Illuminate\Database\Eloquent\Collection) {
            $results = $this->convertEloquentToArray($results);
        }

        return $results;
    }

    /**
     * Converts the eloquent collection into an array -> stdClass data structure.
     *
     * @param \Illuminate\Database\Eloquent\Collection $results
     * @return array
     */
    public function convertEloquentToArray($results)
    {
        $array = array();
        foreach($results->toArray() as $result) {
            $array[] = (object) $result;
        }

        return $array;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

}