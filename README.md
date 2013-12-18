# DataTables Library for Laravel 4

[![Build Status](https://travis-ci.org/samvaughton/laravel4-datatables.png?branch=master)](https://travis-ci.org/samvaughton/laravel4-datatables)

This library prefers configuration over convention (as opposed to convention over configuration). Which allows for greater flexibility without dramatically increasing the code required.

## Composer

```json
"require": {
    "samvaughton/ldt": "dev-master"
}
```

Then run `composer update`.

## Simple Example

```php
$customers = \Customer::select(
    'id', 'name', 'email_address AS email',
    'phone_mobile AS phone', 'date_registered'
);

$dth = new DataTable(
    new LaravelBuilder($customers),
    new Request(\Input::all()),
    array(
        new Column('id'),
        new Column('name'), array('searchable' => true)),
        new Column('email', array('searchable' => true)),
        new Column('phone'),
        new Column('date_registered', array(
            'processor' => new \DateColumnProcessor()
        )),
        new Column('actions', array(
            'type' => Column::TYPE_STATIC,
            'processor' => function($value, $row, $originalRow) {
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
Quite a lot is going on here, but it is very readable. The `DataTable` class accepts three parameters. A class that implements `BuilderInterface` (there is
one already built for Laravel), a `Request` class which handles the parsing of the client side request and thirdly an array of columns which are visible to the user.

The first two parameters will be kept exactly the same 99% of the time.

### Column

The column class accepts two parameters, the first one is required. It can either be a string or an array, this depends on the complexity of the column.

Each column can either be **dynamic** or **static**. A dynamic column is one that originates from the data source. Whereas
static column appended onto the results after being fetched. Such as an actions column that contains edit, delete etc..

#### Aliases

If you are creating alias for in your query and performing searches these columns also, then you will need to use the array to define the SQL column as well. An example will clear this up.

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

The select statement is using an alias, since MySQL cannot utilise aliases within the `WHERE` clause, we have to use its original column name `customers.name`.

#### Options

The column class has default options which are listed below and explained, these can be set via the second parameter as in the example above.

```php
'type'       => self::TYPE_DYNAMIC
'sortable'   => true
'searchable' => false
'processor'  => false
```
 - `type` can be `self::TYPE_DYNAMIC` or `self::TYPE_STATIC`.
 - `sortable` and `searchable` are booleans (true/false).
 - `processor` is a callback / class that is run after the results have been fetched to convert data say from a UNIX timestamp to a more readable date.

## Todo

- Complete README.md
- Add custom filtering callbacks
- Write tests
- Clean up code some more