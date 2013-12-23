<?php

use Samvaughton\Ldt\Column;
use Samvaughton\Ldt\Column\ExampleFilterQueryProcessor;
use Samvaughton\Ldt\Column\ExampleFilterTermProcessor;
use Samvaughton\Ldt\Column\ExampleRowProcessor;

class TestColumn extends PHPUnit_Framework_TestCase
{

    public function testDefaultOptions()
    {
        $col = new Column("name");
        $this->assertEquals("name", $col->getName());
        $this->assertEquals("name", $col->getSqlColumn());
        $this->assertEquals(true, $col->isSortable());
        $this->assertEquals(true, $col->isDynamic());
        $this->assertEquals(false, $col->isStatic());
        $this->assertEquals(false, $col->isSearchable());
        $this->assertEquals(false, $col->canCallRowProcessor());
    }

    public function testCustomOptions()
    {
        $col = new Column("name", array(
            'sortable' => false,
            'searchable' => true,
            'type' => Column::TYPE_STATIC
        ));

        $this->assertEquals(false, $col->isSortable());
        $this->assertEquals(false, $col->isDynamic());
        $this->assertEquals(true, $col->isStatic());
        $this->assertEquals(false, $col->isSearchable()); // Static column, cant be searched
        $this->assertEquals(false, $col->canCallRowProcessor());
    }

    public function testSqlColumn()
    {
        $col = new Column(array("name", "contacts.name AS contactName"));
        $this->assertEquals("name", $col->getName());
        $this->assertEquals("contacts.name", $col->getSqlColumn());

        $col2 = new Column(array("name", "contacts.name"));
        $this->assertEquals("name", $col2->getName());
        $this->assertEquals("contacts.name", $col2->getSqlColumn());
    }

    public function testToString()
    {
        $col = new Column("name");
        $this->assertEquals("name", (string) $col);
    }

    public function testRowProcessorClosure()
    {
        $self = $this;
        $col = new Column("name", array(
            'rowProcessor' => function($value, $row, $originalRow) use ($self) {
                $self->assertEquals('test', $value);
                $self->assertEquals('test', $row['name']);
                $self->assertEquals('test', $originalRow['name']);
                return "~{$value}";
            }
        ));

        $this->assertTrue($col->canCallRowProcessor());

        $this->assertEquals("~test", $col->callRowProcessor(
            'test', array('name' => 'test'), array('name' => 'test')
        ));
    }

    public function testRowProcessorInterface()
    {
        $col = new Column("name", array(
            'rowProcessor' => new ExampleRowProcessor()
        ));

        $this->assertTrue($col->canCallRowProcessor());

        $this->assertEquals("test - 1", $col->callRowProcessor(
            'test', array('name' => 'test'), array('name' => 'test')
        ));
    }

    public function testFilterTermProcessorClosure()
    {
        $col = new Column("name", array(
            'filterTermProcessor' => function($term) {
                 return strtolower(trim($term));
            }
        ));

        $this->assertTrue($col->canCallFilterTermProcessor());

        $this->assertEquals("test", $col->callFilterTermProcessor(" TEST "));
    }

    public function testFilterTermProcessorInterface()
    {
        $col = new Column("name", array(
            'filterTermProcessor' => new ExampleFilterTermProcessor()
        ));

        $this->assertTrue($col->canCallFilterTermProcessor());

        $this->assertEquals("test", $col->callFilterTermProcessor(" TEST "));
    }

    public function testFilterQueryProcessorClosure()
    {
        $col = new Column("name", array(
            'filterQueryProcessor' => function($builder, $column, $term) {
                return strtolower(trim($term));
            }
        ));

        $this->assertTrue($col->canCallFilterQueryProcessor());

        $this->assertEquals("test", $col->callFilterQueryProcessor(
            \Mockery::mock('BuilderInterface'), " TEST "
        ));
    }

    public function testFilterQueryProcessorInterface()
    {
        $col = new Column("name", array(
            'filterQueryProcessor' => new ExampleFilterQueryProcessor()
        ));

        $this->assertTrue($col->canCallFilterQueryProcessor());

        $this->assertTrue($col->callFilterQueryProcessor(
            \Mockery::mock('BuilderInterface', function($mock) {
                /** @var \Mockery\Mock $mock */
                $mock->shouldReceive('getQuery->where')->once();
            }),
            " TEST "
        ));
    }

}