# TUTORIAL - DATA #

This tutorial shows you how to create a model and how to use them in your code.
__NOTE:__ You may need the architect package to initialize/create your database structure defined in your model definition.

-----------------------

__Requirements__

* Model Definition
* Model Backing Class
* Your imagination - e.g. a blank context to work in

__Files to be touched__

* _<your-project>_/config/model/__your-schema_your-model__.json (_create_)
* _<your-project>_/backend/class/model/__your-model__.php (_create_)
* _<your-project>_/backend/class/context/__some-place-to-work-in__.php (_create/modify_)

- - - -

__Step 1: Create your model definition__

Create a new json file in config/model/, e.g. demo_stuff.json.
It is always <schema>_<model>.json. Schema MAY be "demo", but it can also be "abc" or whatever you want it to become.
The schema translates to DB Schemata (or, if you're using MySQL, it matches up with a "database"). And yes, you can cross-join them.

~~~json
{
  "field": [
    "stuff_id",
    "stuff_created",
    "stuff_modified",
    "stuff_name"
  ],
  "primary": [
    "stuff_id"
  ],
  "datatype": {
    "stuff_id": "number_natural",
    "stuff_created": "text_timestamp",
    "stuff_modified": "text_timestamp",
    "stuff_name": "text"
  },
  "options": {
    "stuff_name": {
      "length": 64
    }
  },
  "connection": "myconnection"
}
~~~

__NOTES:__

* Required keys:
  field (array of field names / column names)
  primary (array of fields to be the primary key - should be only 1 (!) at this time)
  datatype (the core-framework-specific datatypes - one of those: text, number, number_natural, boolean, text, structure, ... - you can even use text_email)
* Optional keys:
  foreign (for foreign key definitions)
  db_column_type (see below)
  connection (for using a specific db connection defined in environment.json)
* You may use "db_column_type" to specify the exact database-specific datatype. If undefined for a given key, the default values are used.

- - - -

__Step 2: Build__

If you don't want to create the databases, tables, fields and constraints yourself, you should use the __Architect__ to build you definition.
You have to have architect installed as a composer package.
If you're using the default prepared docker-compose environment, you can open up http://architect.localhost:8080/ to view pending changes. Click on Details of your app.
To execute the pending tasks, append the GET-Parameter exec=1 to the url.

- - - -

__Step 3: Create the backing class__

Create a new PHP file at backend/class/model/<your-schema>_<your-model-name>.json

~~~php
<?php
namespace codename\demo\model;

/**
 * this is an example model
 * without function
 */
class stuff extends \codename\core\model\schematic\mysql {

  /**
   *
   * {@inheritDoc}
   */
  public function __construct(array $modeldata) {
      parent::__construct($modeldata);
      $this->setConfig(null, 'demo', 'stuff');
  }

}
~~~

__NOTES:__

* watch the namespace
* correctly name your class and let it inherit from the right model base class (see above)
* watch: $this->setConfig(null, '<schema>', '<model>');

- - - -

__Step 4: Use!__

~~~php
$model = app::getModel('stuff'); // do your stuff.
~~~

__NOTES:__

* If you're using app::getModel(), you have to be in the namespace codename\core; (or even your own app namespace). Otherwise, require it using "use \codename\core\app;".
* If you're working inside a context, you can also use $this->getModel( ... ), as the base context class provides you this method.

- - - -