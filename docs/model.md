# Model

A 'model' defines the structure and properties of a specific dataset.
At the same time, it also defines methods to access this data in various ways:
- searching (querying)
- filtering
- storing/updating data
- building ad-hoc data models (e.g. joins)

It does **not** define a value-object on its own. Usually, datasets are kept 'PHP-native' in (assocative) arrays.

A model consists of
- a model configuration (usually a file like `config/model/<schema>_<model>.json`)
- a backing class (f.e. in `backend/class/model/<model>.php` - note this does't contain the schema name at all)

## Basic model structure

~~~json
{
  "field" : [
    "stuff_id",
    "stuff_created",
    "stuff_modified",
    "stuff_name"
  ],
  "primary" : [
    "stuff_id"
  ],
  "datatype" : {
    "stuff_id" : "number_natural",
    "stuff_created" : "text_timestamp",
    "stuff_modified" : "text_timestamp",
    "stuff_name" : "text"
  },
  "options" : {
    "stuff_name" : {
      "length": 64
    }
  },
  "connection" : "myconnection"
}
~~~

You can define a 'model' by creating a JSON file (usually placed in your project folder in `config/model/` and named `<schema>_<model>.json`).
There's a good reason to store the model configuration as a separate file like this - to allow **architect** to find it, build it and not having to parse PHP classes for annotations.

The most used (and required) keys in a model are:
- **field**: an array of fields in this model
- **primary**: an array of (one) field(s) that represent the primary key
- **datatype**: an object that defines a datatype for a given field (see available/basic data types below)

Additionally, you have to define some more (optional) keys:
- **connection** (*string*): if you want to assign this model to an explicit connection/database in your application
- **options** (*object*): additional field properties like `length` or DB-specific modificators
- **default** (*object*): default values for fields, if they're not set during creation of a dataset
- **notnull** (*object*):
- **index** (*array*): an array of single-field or multi-component indexes
- **foreign** (*object*): and object of foreign keys
- **children** (*object*): ORM-fields
- **collection** (*object*): ORM-fields

## Data types

The framework defines some basic data types. Here, we show the (My-)SQL equivalent, to improve understanding.

|Data type      |PHP type         |MySQL equivalent (default)   | Notes     |
|---------      |--------         |--------------               |--------   |
|number_natural |int/integer      |`INT(11)`                    |`BIGINT` if primary key
|number         |float/double     |`NUMERIC`, `DECIMAL`         |`precision` and `length` available as options
|boolean        |bool             |`TINYINT(1)`/`BOOLEAN`       |
|text           |string           |`TEXT`, `MEDIUMTEXT`, `LONGTEXT` |`length` available as option
|text_date      |string (ISO-date)  |`DATE`
|text_timestamp |string (ISO-datetime)  |`DATETIME`
|structure      |array, associative array/object  |`text` (formatted as json)|internally handled as regular `text` field
|virtual        |*(none)*         |*(none)*|reserved field for ORM-use

The available datatypes directly correlate to available validators.
`text` fields may also extend to types like `text_timestamp`, which will
be kept as a `string` in PHP, but as far as applicable, translated to a DB-optimized version, if any.
For example, a `text_timestamp` field will be created as a `DATETIME`

### Implicit default convention

When building database structures using **architect** there are some default conventions that are applied, if there's no special configuration (e.g. in options).
- a **primary key** being a **number_natural** is created as a `BIGINT(20)` with `AUTO_INCREMENT`
- a **text** field is created as a medium-length text field - if `length` is given in options, it will be a `VARCHAR(n)`
- a field used as/in a **foreign** key constraint is automatically adapted to the datatype of the field the key references

### Limitations

-  a text field contained in an index must have a definitive length

## Using a model

### Notable facts

- Many methods on a model instance return the instance itself (`return $this;`).  
  This allows chaining of commands, which is especially useful if you define complex filtering or join a lot of models and want to keep your code 'fluent' for instructions that belong together.
- Regular filters get reset/removed after executing a query.
- ..._created and ..._modified fields are mandatory to be defined, if you bootstrap your application using **architect**.

### Essential model methods

This is just a short overview and explanation of the most essential and most-used methods on a model
without the overhead of describing required and optional arguments.

|Method|Description|
|------|-----------|
->**search** ()|Executes a query
->**getResult** ()|Returns the current/latest query result
->**load** (...)|Loads a dataset by ID/primary key
->**addFilter** (...)| Applies a filter to the next query
->**addFilterCollection** (...)| Applies a collecton of filters to the next query
->**addDefaultFilter** (...)| Applies a filter to **all** following queries
->**addDefaultFilterCollection** (...)| Applies a collecton of filters to **all** following queries
->**addField** (...)| includes a specific field (column) in the resultset
->**hideField** (...)| excludes a specific field (column) in the resultset
->**save** (...)| stores given data
->**addModel** (...)| adds (joins) another compatible model, effectively making the model more complex
->**setVirtualFieldResult** (...)| enables virtual fields in a model (and full ORM functionality)
->**saveWithChildren** (...)|stores given data, ORM-enabled

### Data retrieval (querying/searching)

We assume you have a minimal core app running.

~~~php
// Get a fresh instance of the model
$model = app::getModel('stuff');

// Query the data and return resultset
$result = $model->search()->getResult();

// Do something with the result, e.g. output
print_r($result);
~~~

This will query all the data available in the model - in case of a MySQL/MariaDB, this is similar to querying:
~~~sql
SELECT * FROM stuff
~~~
By default, all fields defined in the model are retrieved for the output resultset. Manually added columns in a table that are 'unknown' to the model won't appear.

## Further reading
Please continue to
- [Model filters](model/model_filters.md) for defining your data searches.
- [Complex models](model/complex_models.md) for adding/joining models and building more complex models.
