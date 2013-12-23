<?php

use Samvaughton\Ldt\Column;
use Samvaughton\Ldt\ExampleColumnProcessor;

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

    public function testFunctionCallback()
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

    public function testClassProcessor()
    {
        $col = new Column("name", array(
            'rowProcessor' => new ExampleColumnProcessor()
        ));

        $this->assertTrue($col->canCallRowProcessor());

        $this->assertEquals("test - 1", $col->callRowProcessor(
            'test', array('name' => 'test'), array('name' => 'test')
        ));
    }

}