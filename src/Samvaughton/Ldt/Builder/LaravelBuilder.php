<?php

namespace Samvaughton\Ldt\Builder;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;

class LaravelBuilder implements BuilderInterface
{

    /**
     * @var Builder|\Illuminate\Database\Eloquent\Builder;
     */
    private $query;

    /**
     * @param Builder|\Illuminate\Database\Eloquent\Builder $query
     * @throws \InvalidArgumentException
     */
    public function __construct($query)
    {
        if (!$query instanceof Builder &&
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

            $this->query->orderBy($column->getName(), $direction);
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
            /** @var Builder|\Illuminate\Database\Eloquent\Builder $query */
            foreach($filterData['columns'] as $colData) {
                /** @var \Samvaughton\Ldt\Column $column */
                $column = $colData['column'];

                if (!$column instanceof \Samvaughton\Ldt\Column) {
                    continue; // Not a column
                }

                // See if this column is searchable
                if (!$column->isSearchable()) continue;

                // If the individual column term is empty, use the main term
                $term = (empty($colData['term'])) ? $filterData['term'] : $colData['term'];

                // Check if we have a callback, if so lets use it
                if ($column->canCallFilterTermProcessor()) {
                    //$term = $column->callFilterTermProcessor($term);
                }

                // Actually apply the filter
                if ($column->canCallFilterQueryProcessor()) {
                    //$column->callFilterQueryProcessor($this, $term);
                } else {
                    // Standard query for filtering
                    $query->orWhere($column->getSqlColumn(), "LIKE", "%{$term}%");
                }
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

        if ($results instanceof Collection) {
            $results = $this->convertEloquentToArray($results);
        }

        return $results;
    }

    /**
     * Converts the eloquent collection into an array -> stdClass data structure.
     *
     * @param Collection $results
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
     * @return \Illuminate\Database\Eloquent\Builder|Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

}