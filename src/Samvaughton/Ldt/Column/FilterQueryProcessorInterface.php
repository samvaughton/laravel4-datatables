<?php

namespace Samvaughton\Ldt\Column;

use Samvaughton\Ldt\Builder\BuilderInterface;
use Samvaughton\Ldt\Column;

interface FilterQueryProcessorInterface
{

    /**
     * @param BuilderInterface $builder
     * @param Column $column
     * @param string $term
     * @return void|mixed
     */
    public function run($builder, $column, $term);

}