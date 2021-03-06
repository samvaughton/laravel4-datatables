<?php

namespace Samvaughton\Ldt\Column;

class ExampleFilterTermProcessor implements FilterTermProcessorInterface
{

    /**
     * This will simply append the amount of columns to the end of the columns value.
     */
    public function run($term)
    {
        return strtolower(trim($term));
    }

}