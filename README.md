### Laravel 4 DataTables Library

[![Build Status](https://travis-ci.org/samvaughton/laravel4-datatables.png?branch=master)](https://travis-ci.org/samvaughton/laravel4-datatables)

This library prefers configuration over convention (as opposed to convention over configuration). Which allows for greater flexibility without dramatically increasing the code required.

#### Composer

```json
"require": {
    "samvaughton/ldt": "dev-master"
}
```

Then run `composer update`.

#### Simple Example

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
Quite a lot is going on here, but it is very readable. One thing to note is the order of the columns have to match.

#### Todo

- Complete README.md
- Add custom filtering callbacks
- Write tests
- Clean up code some more