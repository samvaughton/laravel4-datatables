### Laravel 4 DataTables Library

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

$dth = new DataTable(\Input::all(), $customers, array(
    new Column('id'),
    new Column('name'), array('searchable' => true)),
    new Column('email', array('searchable' => true)),
    new Column('phone'),
    new Column('date_registered', array(
        'render' => function($value, $row, $originalRow) {
            return date("Y-m-d", strtotime($value));
        }
    )),
    new Column('actions', array(
        'type' => 'static',
        'render' => function($value, $row, $originalRow) {
            return sprintf(
                '<a href="/customer/edit/%s">Edit</a>',
                $row['id']
            );
        }
    )
));

return $dth->make(true);
```
Quite a lot is going on here, but it is very readable. One thing to note is the order of the columns have to match.

#### Todo

- Complete README.md