<?php

namespace Samvaughton\Ldt\Builder;

interface BuilderInterface
{

    /**
     * @return int
     */
    public function count();

    /**
     * @param string $start
     * @param string $length
     * @return void
     */
    public function paginate($start, $length);

    /**
     * @param array $orderData
     * @return void
     */
    public function order(array $orderData);

    /**
     * @param array $filterData
     * @return void
     */
    public function filter(array $filterData);

    /**
     * Returns the database result set.
     *
     * @return array
     */
    public function get();

    /**
     * Returns the query object/string.
     *
     * @return mixed
     */
    public function getQuery();

}