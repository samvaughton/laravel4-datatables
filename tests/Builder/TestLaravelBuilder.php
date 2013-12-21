<?php

use Samvaughton\Ldt\Builder\BuilderInterface;
use Samvaughton\Ldt\Builder\LaravelBuilder;
use Samvaughton\Ldt\Column;

class TestLaravelBuilder extends PHPUnit_Framework_TestCase
{

    public function testInterface()
    {
        $mock    = \Mockery::mock('\Illuminate\Database\Query\Builder');
        $builder = new LaravelBuilder($mock);
        $this->assertTrue($builder instanceof BuilderInterface);

        $mock    = \Mockery::mock('\Illuminate\Database\Eloquent\Builder');
        $builder = new LaravelBuilder($mock);
        $this->assertTrue($builder instanceof BuilderInterface);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testNonLaravel()
    {
        $mock    = \Mockery::mock('testObject');
        $builder = new LaravelBuilder($mock);
    }

    public function testCount()
    {
        $fluent = \Mockery::mock('Illuminate\Database\Query\Builder', function ($mock) {
            /** @var \Mockery\Mock $mock */
            $mock->shouldReceive('count')->once()->andReturn(5);
        });

        $builder = new LaravelBuilder($fluent);
        $this->assertEquals(5, $builder->count());
    }

    public function testPaginate()
    {
        $fluent = \Mockery::mock('Illuminate\Database\Query\Builder', function ($mock) {
            /** @var \Mockery\Mock $mock */
            $mock->shouldReceive('skip')->once()->with(5);
            $mock->shouldReceive('take')->once()->with(15);
        });

        $builder = new LaravelBuilder($fluent);

        $builder->paginate(5, 15);
    }

    public function testOrdering()
    {
        $fluent = \Mockery::mock('Illuminate\Database\Query\Builder', function ($mock) {
            /** @var \Mockery\Mock $mock */
            $mock->shouldReceive('orderBy')->times(2);
        });

        $builder = new LaravelBuilder($fluent);

        $builder->order(array(
            array(
                'column'    => \Mockery::mock('\Samvaughton\Ldt\Column', function ($mock) {
                        /** @var \Mockery\Mock $mock */
                        $mock->shouldReceive('isSortable')->once()->andReturn(true);
                        $mock->shouldReceive('getSqlColumn')->once();
                    }),
                'direction' => 'asc',
                'sortable'  => true,
            ),
            array(
                'column'    => \Mockery::mock('\Samvaughton\Ldt\Column', function ($mock) {
                        /** @var \Mockery\Mock $mock */
                        $mock->shouldReceive('isSortable')->once()->andReturn(true);
                        $mock->shouldReceive('getSqlColumn')->once();
                    }),
                'direction' => 'desc',
                'sortable'  => true,
            ),
            array(
                'column'    => \Mockery::mock('\Samvaughton\Ldt\Column', function ($mock) {
                        /** @var \Mockery\Mock $mock */
                        $mock->shouldReceive('isSortable')->once()->andReturn(false);
                    }),
                'direction' => 'asc',
                'sortable'  => true,
            ),
            array(
                'column'    => \Mockery::mock('\Samvaughton\Ldt\Column', function ($mock) {
                        /** @var \Mockery\Mock $mock */
                        $mock->shouldReceive('isSortable')->once()->andReturn(true);
                    }),
                'direction' => 'asc',
                'sortable'  => false,
            ),
        ));
    }

    public function testFiltering()
    {
        $query = \Mockery::mock('Illuminate\Database\Query\Builder', function ($mock) {
            /** @var \Mockery\Mock $mock */
            $mock->makePartial();
            $mock->shouldReceive('where')->once()->passthru();
            $mock->shouldReceive('newQuery')->andReturn(
                \Mockery::mock('Illuminate\Database\Query\Builder', function ($mock) {
                    /** @var \Mockery\Mock $mock */
                    $mock->makePartial();
                    $mock->shouldReceive('orWhere')->twice();
                })
            );
        });

        $builder = new LaravelBuilder($query);

        $builder->filter(array(
            'term' => 'search',
            'columns' => array(
                array(
                    'column'    => \Mockery::mock('\Samvaughton\Ldt\Column', function ($mock) {
                            /** @var \Mockery\Mock $mock */
                            $mock->shouldReceive('isSearchable')->once()->andReturn(true);
                            $mock->shouldReceive('getSqlColumn')->once()->andReturn("test");
                        }),
                    'term'  => "search",
                    'searchable'  => true,
                ),
                array(
                    'column'    => \Mockery::mock('\Samvaughton\Ldt\Column', function ($mock) {
                            /** @var \Mockery\Mock $mock */
                            $mock->shouldReceive('isSearchable')->once()->andReturn(true);
                            $mock->shouldReceive('getSqlColumn')->once()->andReturn("test");
                        }),
                    'term'  => "search",
                    'searchable'  => true,
                ),
                array(
                    'column'    => \Mockery::mock('\Samvaughton\Ldt\Column', function ($mock) {
                            /** @var \Mockery\Mock $mock */
                            $mock->shouldReceive('isSearchable')->once()->andReturn(false);
                            $mock->shouldReceive('getSqlColumn')->never();
                        }),
                    'term'  => "search",
                    'searchable'  => true,
                ),
                array(
                    'column'    => \Mockery::mock('\Samvaughton\Ldt\Column', function ($mock) {
                            /** @var \Mockery\Mock $mock */
                            $mock->shouldReceive('isSearchable')->once()->andReturn(true);
                            $mock->shouldReceive('getSqlColumn')->never();
                        }),
                    'term'  => "search",
                    'searchable'  => false,
                ),
            )
        ));
    }

    public function testGet()
    {
        $fluent = \Mockery::mock('Illuminate\Database\Query\Builder', function ($mock) {
            /** @var \Mockery\Mock $mock */
            $mock->shouldReceive('get')->once();
        });

        $builder = new LaravelBuilder($fluent);

        $builder->get();

        $fluent = \Mockery::mock('Illuminate\Database\Query\Builder', function ($mock) {
            /** @var \Mockery\Mock $mock */
            $mock->shouldReceive('get')->once()->andReturn(
                \Mockery::mock('\Illuminate\Database\Eloquent\Collection', function($mock) {
                    /** @var \Mockery\Mock $mock */
                    // By mocking the to array method, we can tell that we have called the convertEloquentMethod
                    $mock->shouldReceive('toArray')->once()->andReturn(array());
                })
            );
        });

        $builder = new LaravelBuilder($fluent);

        $builder->get();
    }

    public function tearDown()
    {
        \Mockery::close();
    }
}