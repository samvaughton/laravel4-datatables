<?php

use Samvaughton\Ldt\Request;

class TestRequest extends PHPUnit_Framework_TestCase
{

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

}