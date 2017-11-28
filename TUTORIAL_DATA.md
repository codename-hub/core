# TUTORIAL - DATA #

This tutorial shows you how to create a model and how to use them in your code.
__NOTE:__ You may need the architect package to initialize/create your database structure defined in your model definition.

-----------------------

__Requirements__

* Model Definition
* Model Backing Class
* Your imagination - e.g. a blank context to work in

__Files to be touched__

* _<your-project>_/config/model/__your-model__.json (_create_)
* _<your-project>_/backend/class/model/__your-model__.php (_create_)
* _<your-project>_/backend/class/context/__some-place-to-work-in__.php (_create/modify_)

- - - -

__Step 1: Create your model definition__

Create a new json file in config/model/.

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
  "db_column_type" : {
    "stuff_name" : "varchar(64)"
  },
  "connection" : "myconnection"
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

...

- - - -

__Step 4: Use!__

...

- - - -