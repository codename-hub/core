# Model filters

## Operators

You can apply filters and filter collections to a model using the following methods on a model instance.

Available operators:

| Operator | Name                  | Effect (primitive value)         | Effect (null value)                      | Effect array value                                                |
|----------|-----------------------|----------------------------------|------------------------------------------|-------------------------------------------------------------------|
| `=`      | Equal                 | Equality comparison              | Equality comparison e.g. `IS NULL`       | Equality comparison against a set of values, e.g. `IN (...)`      |
| `!=`     | Not-Equal             | Inequality comparison            | Inequality comparison e.g. `IS NOT NULL` | Inequality comparison against a set of values, e.g.`NOT IN (...)` |
| `>`      | Greater than          | Greater than comparison          | *invalid*                                | *invalid*                                                         |
| `>=`     | Greater than or equal | Greater than or equal comparison | *invalid*                                | *invalid*                                                         |
| `<`      | Less than             | Less than comparison             | *invalid*                                | *invalid*                                                         |
| `<=`     | Less than or equal    | Less than or equal comparison    | *invalid*                                | *invalid*                                                         |
| `LIKE`   | Similarity            | `LIKE '...'` (case-insensitive)  | *invalid*                                | *invalid*                                                         |

## Filter methods

* **addFilter**($field, $value, $operator = '=', $conjunction = null)
    * Required arguments:
        * `$field` (string): the field to apply the filter to
        * `$value`: the filter value (possible values: a primitive, null or an array, depending on operator)
    * Optional arguments
        * `$operator` (*optional*, default: `=`): a valid operator
        * `$conjunction` (*optional*, default: `AND`): boolean conjunction for this filter (`AND` or `OR`)

* **addFilterCollection**($filters, $groupOperator = 'AND', $groupName = 'default', $conjunction = null)  
  Required arguments:
    * `$filters` (array): an **array** of filters, composed of items like: `[ 'field' => 'field-name', 'operator' => '=', 'value' => 123 ]`
    * `$groupOperator`: boolean conjunction between every single filter in `$filters`

  Optional arguments:
    * `$groupName` (*optional*, default: `default`): a named group for controlling boolean logic and cross-model filtering
    * `$conjunction` (*optional*, default: `AND`): conjunction of multiple same-name groups (`AND` or `OR`)


* **addDefaultFilter**($field, $value, $operator = '=', $conjunction = null)  
  the same as `addFilter`, but kept alive across multiple `search()`es. Non-removable once set.

* **addDefaultFilterCollection**($filters, $groupOperator = 'AND', $groupName = 'default', $conjunction = null)  
  the same as `addFilterCollection`, but kept alive across multiple `search()`es. Non-removable once set.

## Filtering examples and SQL equivalents

For these examples, we assume the above `stuff` model being a MySQL-driven model and initialized like:

~~~php
$model = app::getModel('stuff')
~~~

The following examples list

**Simple equality filter**, will match all entries with stuff_name = 'my-stuff':

~~~php
$model
  ->addFilter('stuff_name', 'my-stuff')
  ->search()->getResult();
~~~

~~~sql
SELECT * FROM stuff
WHERE stuff_name = 'my_stuff'
~~~

**Array equality filter**, will match all entries with stuff_name being 'my-stuff' or 'other-stuff'

~~~php
$model
  ->addFilter('stuff_name', [ 'my-stuff', 'other-stuff' ])
  ->search()->getResult();
~~~

~~~sql
SELECT * FROM stuff
WHERE stuff_name IN ('my_stuff', 'other-stuff')
~~~

**Not-Null filter**, will match all entries with stuff_name NOT being NULL:

~~~php
$model
  ->addFilter('stuff_name', null, '!=')
  ->search()->getResult();
~~~

~~~sql
SELECT * FROM stuff
WHERE stuff_name IS NOT NULL
~~~

**GTE filter**, will match all entries with stuff_id being greater than or equal to 123

~~~php
$model
  ->addFilter('stuff_id', 123, '>=')
  ->search()->getResult();
~~~

~~~sql
SELECT * FROM stuff
WHERE stuff_id >= 123
~~~

**Simple filtercollection**, this example should yield the same result as above (see **Array equality filter**), all entries with stuff_name being 'my-stuff' or 'other-stuff'

~~~php
$model
  ->addFilterCollection([
    [ 'field' => 'stuff_name', 'operator' => '=', 'value' => 'my-stuff'     ]
    [ 'field' => 'stuff_name', 'operator' => '=', 'value' => 'other-stuff'  ]
  ], 'OR')
  ->search()->getResult();
~~~

~~~sql
SELECT * FROM stuff
WHERE (stuff_name = 'my_stuff' OR stuff_name = 'other-stuff')
~~~

**Complex filter collection** with two or more groups.
Filter groups as a whole are always assumed to be concatenated via `AND`.

~~~php
$model
  ->addFilterCollection([
    [ 'field' => 'stuff_name', 'operator' => '=', 'value' => 'my-stuff'       ]
    [ 'field' => 'stuff_name', 'operator' => '=', 'value' => 'other-stuff'    ]
  ], 'OR', 'first-group')
  ->addFilterCollection([
    [ 'field' => 'stuff_id',    'operator' => '>=', 'value' => 123            ]
    [ 'field' => 'stuff_name',  'operator' => '=',  'value' => 'some-stuff'   ]
  ], 'AND', 'second-group', 'OR')
  ->addFilterCollection([
    [ 'field' => 'stuff_id',    'operator' => '<',  'value' => 123            ]
    [ 'field' => 'stuff_name',  'operator' => '=',  'value' => 'more-stuff'   ]
  ], 'AND', 'second-group', 'OR')
  ->search()->getResult();
~~~

~~~sql
SELECT * FROM stuff
WHERE
  (stuff_name = 'my_stuff' OR stuff_name = 'other-stuff') -- first-group
  -- second-group begin
  AND
  (
    (stuff_id >= 123  AND stuff_name = 'some-stuff') -- second-group, filtercollection 1
    OR
    (stuff_id <  123  AND stuff_name = 'more-stuff') -- second-group, filtercollection 2
  )
  -- second-group end
~~~
