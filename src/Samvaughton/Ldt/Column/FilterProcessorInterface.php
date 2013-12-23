<?php

namespace Samvaughton\Ldt\Column;

interface FilterProcessorInterface
{

    /**
     * The filter term will be whatever this function returns
     *
     * @param string $term
     * @return string
     */
    public function run($term);

}