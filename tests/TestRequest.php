<?php

use Samvaughton\Ldt\Request;

class TestRequest extends PHPUnit_Framework_TestCase
{

    public function testGet()
    {
        $stub = array(
            'name' => 'john doe',
            'age' => 10,
            'alive' => true,
            'wants' => null
        );

        $request = new Request($stub);

        $this->assertEquals('john doe', $request->get('name'));
        $this->assertEquals('', $request->get('aiwydg', ''));
        $this->assertEquals('', $request->get('aawdwaiwydg'));
        $this->assertEquals(10, $request->get('age'));
        $this->assertEquals(null, $request->get('dfawdw'));
    }

    public function testSet()
    {
        $stub = array('data' => 'john');
        $request = new Request($stub);
        $request->set('data', 'james');

        $this->assertEquals('james', $request->get('data'));

        $request->set(array(
            'param1' => 'one',
            'param2' => 'two'
        ));

        $this->assertEquals('one', $request->get('param1'));
        $this->assertEquals('two', $request->get('param2'));
    }

    public function testEcho()
    {
        $request = new Request(array(
            'sEcho' => 1
        ));

        $this->assertEquals(1, $request->getEcho());

        $request = new Request(array(
            'sEcho' => 54
        ));

        $this->assertEquals(54, $request->getEcho());

        $request = new Request(array(
            'sEcho' => 0
        ));

        $this->assertEquals(0, $request->getEcho());

        $request = new Request(array(
            'sEcho' => "test"
        ));

        $this->assertEquals(0, $request->getEcho());
    }

    public function testPaging()
    {
        $stubs = array(
            $stub = array(
                'request' => array(
                    'iDisplayStart' => 0,
                    'iDisplayLength' => 10,
                ),
                'actual' => array(
                    'start' => 0,
                    'length' => 10,
                )
            ),
            $stub = array(
                'request' => array(
                    'iDisplayStart' => 20,
                    'iDisplayLength' => 100,
                ),
                'actual' => array(
                    'start' => 20,
                    'length' => 100,
                )
            ),
            $stub = array(
                'request' => array(
                    'iDisplayStart' => "aaa",
                    'iDisplayLength' => "sefg",
                ),
                'actual' => array(
                    'start' => 0,
                    'length' => 0,
                )
            ),
        );

        foreach($stubs as $stub) {
            $request = new Request($stub['request']);
            $this->assertEquals($stub['actual']['start'], $request->getPaginationStart());
            $this->assertEquals($stub['actual']['length'], $request->getPaginationLength());
        }
    }

    public function testSortableColumns()
    {
        $stub = array(
            'sEcho'          => 15,
            'iColumns'       => 7,
            'sColumns'       => '',
            'iDisplayStart'  => 0,
            'iDisplayLength' => 10,
            'mDataProp_0'    => 0,
            'mDataProp_1'    => 1,
            'mDataProp_2'    => 2,
            'mDataProp_3'    => 3,
            'mDataProp_4'    => 4,
            'mDataProp_5'    => 5,
            'mDataProp_6'    => 6,
            'sSearch'        => '',
            'bRegex'         => false,
            'sSearch_0'      => '',
            'bRegex_0'       => false,
            'bSearchable_0'  => true,
            'sSearch_1'      => '',
            'bRegex_1'       => false,
            'bSearchable_1'  => true,
            'sSearch_2'      => '',
            'bRegex_2'       => false,
            'bSearchable_2'  => true,
            'sSearch_3'      => '',
            'bRegex_3'       => false,
            'bSearchable_3'  => true,
            'sSearch_4'      => '',
            'bRegex_4'       => false,
            'bSearchable_4'  => true,
            'sSearch_5'      => '',
            'bRegex_5'       => false,
            'bSearchable_5'  => false,
            'sSearch_6'      => '',
            'bRegex_6'       => false,
            'bSearchable_6'  => true,
            'iSortCol_0'     => 0,
            'sSortDir_0'     => 'asc',
            'iSortingCols'   => 1,
            'bSortable_0'    => true,
            'bSortable_1'    => true,
            'bSortable_2'    => true,
            'bSortable_3'    => true,
            'bSortable_4'    => true,
            'bSortable_5'    => false,
            'bSortable_6'    => true,
        );

        $request = new Request($stub);

        $sortable = $request->getSortableColumns();

        $this->assertEquals($stub['iSortingCols'], count($sortable));

        $isSortableKey = array(true, true, true, true, true, false, true);
        $directionKey = array('asc');
        $colIndexKey = array(0);

        foreach($sortable as $index => $column) {
            $this->assertEquals($isSortableKey[$index], $column['sortable']);
            $this->assertEquals($directionKey[$index], $column['direction']);
            $this->assertEquals($colIndexKey[$index], $column['column']);
        }

    }

    public function testFilterableColumns()
    {
        $stub = array(
            'sEcho'          => 15,
            'iColumns'       => 7,
            'sColumns'       => '',
            'iDisplayStart'  => 0,
            'iDisplayLength' => 10,
            'mDataProp_0'    => 0,
            'mDataProp_1'    => 1,
            'mDataProp_2'    => 2,
            'mDataProp_3'    => 3,
            'mDataProp_4'    => 4,
            'mDataProp_5'    => 5,
            'mDataProp_6'    => 6,
            'sSearch'        => '',
            'bRegex'         => false,
            'sSearch_0'      => '',
            'bRegex_0'       => false,
            'bSearchable_0'  => true,
            'sSearch_1'      => '',
            'bRegex_1'       => false,
            'bSearchable_1'  => true,
            'sSearch_2'      => '',
            'bRegex_2'       => false,
            'bSearchable_2'  => true,
            'sSearch_3'      => '',
            'bRegex_3'       => false,
            'bSearchable_3'  => true,
            'sSearch_4'      => '',
            'bRegex_4'       => false,
            'bSearchable_4'  => true,
            'sSearch_5'      => '',
            'bRegex_5'       => false,
            'bSearchable_5'  => false,
            'sSearch_6'      => '',
            'bRegex_6'       => false,
            'bSearchable_6'  => true,
            'iSortCol_0'     => 0,
            'sSortDir_0'     => 'asc',
            'iSortingCols'   => 1,
            'bSortable_0'    => true,
            'bSortable_1'    => true,
            'bSortable_2'    => true,
            'bSortable_3'    => true,
            'bSortable_4'    => true,
            'bSortable_5'    => false,
            'bSortable_6'    => true,
        );

        $request = new Request($stub);

        $filterable = $request->getFilterableColumns();

        $this->assertEquals($stub['iColumns'], count($filterable['columns']));
        $this->assertEquals(false, $request->isFilterable());

        $request->set('sSearch', 'test');
        $this->assertEquals(true, $request->isFilterable());

        $filterable = $request->getFilterableColumns();
        $this->assertEquals('test', $filterable['term']);

        $isFilterableKey = array(true, true, true, true, true, false, true);
        $colIndexKey = array(0, 1, 2, 3, 4, 5, 6);

        foreach($filterable['columns'] as $index => $column) {
            $this->assertEquals($isFilterableKey[$index], $column['searchable']);
            $this->assertEquals($colIndexKey[$index], $column['column']);
        }
    }

}