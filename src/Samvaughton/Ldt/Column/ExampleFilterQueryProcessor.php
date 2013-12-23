<?php

namespace Samvaughton\Ldt\Column;

use Samvaughton\Ldt\Builder\BuilderInterface;
use Samvaughton\Ldt\Column;

class ExampleFilterQueryProcessor implements FilterQueryProcessorInterface
{

    /**
     * @param BuilderInterface $builder
     * @param Column $column
     * @param string $term
     * @return void|mixed
     */
    public function run($builder, $column, $term)
    {
        $builder->getQuery()->where($column->getSqlColumn(), '=', trim($term));

        return true;
    }


}