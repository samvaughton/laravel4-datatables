# DataTables Library for Laravel 4

[![Build Status](https://travis-ci.org/samvaughton/laravel4-datatables.png?branch=master)](https://travis-ci.org/samvaughton/laravel4-datatables)

This library is for the server side processing of the client requests. It aims to be different than existing DataTable
packages, taking a different approach that allows greater flexibility.

## Composer

```json
"require": {
    "samvaughton/ldt": "dev-master"
}
```

Then run `composer update`.

## Simple Example

```php
$customers = \Customer::select('id', 'name', 'email', 'phone', 'date_registered');

$dth = new DataTable(
    new LaravelBuilder($customers),
    new Request(\Input::all()),
    array(
        new Column('id'),
        new Column('name', array('searchable' => true)),
        new Column('email', array('searchable' => true)),
        new Column('phone'),
        new Column('date_registered', array(
            'rowProcessor' => new \DateColumnProcessor()
        )),
        new Column('actions', array(
            'type' => Column::TYPE_STATIC,
            'rowProcessor' => function($value, $row, $originalRow) {
                return sprintf(
                    '<a href="/customer/edit/%s">Edit</a>',
                    $row['id']
                );
            }
        )
    )
);

return $dth->make();
```

*I have imported the neccassary namespace paths via `use` for `DataTable`, `LaravelBuilder`, `Request` and `Column`. `DateColumnProcessor` is a custom class, you can look at an example in the `Column` namespace.*

Quite a lot is going on here, but it is very readable. The `DataTable` class accepts three parameters. A class that
implements `BuilderInterface` (there is one already built for Laravel), a `Request` class which handles the parsing of
the client side request and thirdly an array of columns which are visible to the user.

The first two parameters will be kept exactly the same 99% of the time.

### Column

The column class accepts two parameters, the first one is required. It can either be a string or an array, this depends
on the complexity of the column.

Each column can either be **dynamic** or **static**. A dynamic column is one that originates from the data source.
Whereas a static column is appended onto the results after being fetched. Such as an actions column that contains
buttons for edit, delete etc..

#### Aliases

If you are using aliases in your query and performing searches as well, then you will need to use an array to define the
SQL column separately. An example will clear this up.

```php
$query = DB::table('customers')->select('customers.name AS customerName');
$dth = new DataTable(
    new LaravelBuilder($customers),
    new Request(\Input::all()),
    array(
        new Column(array('customerName', 'customers.name'), array(
            'searchable' => true
        ))
    )
);
```

The select statement is using an alias, since MySQL cannot utilise aliases within the `WHERE` clause, we have to use its
original column name `customers.name`. Otherwise the generated SQL for `WHERE` would look something like:

    WHERE `customerName` LIKE '%john doe%'

Which is illegal.

#### Options

The column class has default options which are listed below and explained, these can be set via the second parameter
like the example above.

```php
'type'                 => self::TYPE_DYNAMIC
'sortable'             => true
'searchable'           => false
'rowProcessor'         => false
'filterTermProcessor'  => false
'filterQueryProcessor' => false
```
 - `type` can be `TYPE_DYNAMIC` or `TYPE_STATIC`.
 - `sortable` and `searchable` are booleans (true/false).
 - `rowProcessor` is a callback / class that implements the `RowProcessorInterface`.
 - `filterTermProcessor` is a callback / class that implements the `FilterTermProcessorInterface`.
 - `filterQueryProcessor` is a callback / class that implements the `FilterQueryProcessorInterface`. This is a bit more advanced and lets you define your own SQL to filter against.

#### Processor's

The `rowProcessor` options allows you to run a function against each column's data, this is for scenarios where you need to
append some action buttons or convert a unix timestamp to a more readable date.

```php
new Column('actions', array(
    'type' => Column::TYPE_STATIC,
    'rowProcessor' => function($value, $row, $originalRow) {
        return sprintf(
            '<a href="/customer/edit/%s">Edit</a>',
            $row['id']
        );
    }
)
```

This is a static column that appends an edit button onto every row, it utilises the customers `id` from the `$row` array.
You may be wondering what the `$originalRow` is for, this is an untouched row that contains every column from your
select statement. Using the above code, if we were to modify the `id` column and set the value to `null` then this
processor would return:

    <a href="/customer/edit/">Edit</a>

Due to the `id` column being modified before this one. Here we could utilise `$originalRow['id']` to get the unaltered
value.

*If you are trying to use a column that isn't defined within the column array then you have to use `$originalRow` as
these columns are not contained within `$row`.*

Instead of passing a callback, you can also pass a class that implements the `RowProcessorInterface`.

```php
<?php

namespace \Samvaughton\Ldt;

class ExampleColumnProcessor implements RowProcessorInterface
{

    /**
     * This will simply append the date to the column.
     */
    public function run($value, $row, $originalRow)
    {
        return sprintf("%s - %s", $value, date("Y-m-d"));
    }

}
```

The column instantiation may look something like this:

```php
new Column('date', array(
    'type' => Column::TYPE_STATIC,
    'rowProcessor' => new \Samvaughton\Ldt\ExampleColumnProcessor
)
```

The `filterTermProcessor` option is similar to the `rowProcessor` except that it modifies the search term before the query
is executed to fetch the results. Say you only wanted users to search in lowercase you could provide a callback/class
that performs this.

```php
new Column('name', array(
    'filterTermProcessor' => function($term) {
        return strtolower(trim($term));
    }
)
```

**You can find examples of these processors within the `Samvaughton\Ldt\Column` namespace.**

## Contributing

I would love for people to help contribute to this library, send a pull request and I'll check it out!

## Todo:

 - More Tests
 - Better Documentation
