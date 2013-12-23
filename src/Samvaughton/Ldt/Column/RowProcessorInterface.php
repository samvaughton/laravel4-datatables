<?php

namespace Samvaughton\Ldt\Column;

interface RowProcessorInterface
{

    /**
     * The field will be set to whatever this function returns.
     *
     * @param string $value
     * @param array $row
     * @param array $originalRow
     * @return string
     */
    public function run($value, $row, $originalRow);

}