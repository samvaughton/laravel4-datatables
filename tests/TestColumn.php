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
        $this->assertEquals(false, $col->canProcess());
        $this->assertEquals(false, $col->getDtRowId());
        $this->assertEquals(false, $col->getDtRowClass());
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

    public function testFunctionCallback()
    {
        $col = new Column("name", array(
            'processor' => function($value, $row, $originalRow) {
                $this->assertEquals('test', $value);
                $this->assertEquals('test', $row['name']);
                $this->assertEquals('test', $originalRow['name']);
                return "~{$value}";
            }
        ));

        $this->assertEquals("~test", $col->process(
            'test', array('name' => 'test'), array('name' => 'test')
        ));
    }

    public function testClassProcessor()
    {
        $col = new Column("name", array(
            'processor' => new ExampleColumnProcessor()
        ));

        $this->assertEquals("test - 1", $col->process(
            'test', array('name' => 'test'), array('name' => 'test')
        ));
    }

}