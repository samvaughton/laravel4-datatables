<?php

namespace Samvaughton\Ldt\Column;

interface FilterTermProcessorInterface
{

    /**
     * The filter term will be whatever this function returns
     *
     * @param string $term
     * @return string
     */
    public function run($term);

}