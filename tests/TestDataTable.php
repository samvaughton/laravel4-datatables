<?php

use Samvaughton\Ldt\DataTable;

class TestDataTable extends PHPUnit_Framework_TestCase
{

    public function testConstruct()
    {
        $dt = new DataTable(
            \Mockery::mock('\Samvaughton\Ldt\Builder\BuilderInterface'),
            \Mockery::mock('\Samvaughton\Ldt\Request'),
            array(
                \Mockery::mock('\Samvaughton\Ldt\Column'),
            )
        );
    }

    public function tearDown()
    {
        \Mockery::close();
    }

}