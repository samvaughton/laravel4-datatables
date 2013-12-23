<?php

namespace Samvaughton\Ldt\Column;

class ExampleRowProcessor implements RowProcessorInterface
{

    /**
     * This will simply append the amount of columns to the end of the columns value.
     */
    public function run($value, $row, $originalRow)
    {
        return sprintf("%s - %s", $value, count($row));
    }

}