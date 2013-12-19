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

    public function testMake()
    {
        return;
        $dt = new DataTable(
            \Mockery::mock('\Samvaughton\Ldt\Builder\BuilderInterface'),
            \Mockery::mock('\Samvaughton\Ldt\Request'),
            array(
                \Mockery::mock('\Samvaughton\Ldt\Column', function ($mock) {
                    /** @var \Mockery\Mock $mock */

                }),
            )
        );

        $actual = $dt->make(true);
        $expected = json_encode(array(
            'sEcho' => 4,
            'iTotalDisplayRecords' => 20,
            'iTotalRecords' => 20,
            'aaData' => array(
                array('john', 15),
                array('jane', 14),
            )
        ));

        $this->assertEquals($expected, $actual);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

}