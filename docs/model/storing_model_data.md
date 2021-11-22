# Storing model data

## Basics
Two methods can be used for storing a single dataset in your model:
* ->**save** (*array* $data): simple data storage on the current (root) model
* ->**saveWithChildren** (*array* $data): for cases with enabled ORM, if you want to store complex model datasets (children and collections)

### Notable facts
* These methods will ignore all fields dataset that are not part of the model.
* Fields that are defined in the model, but *not defined in the dataset*, will not be set or changed explicitly (if you create a new dataset the respective field's value is set to the default value; usually `null`, if not defined explicitly).

## Validation
The validation, if desired, can be performed beforehand via
~~~php
$returnsBool = $model->isValid($dataset);
~~~

## Creating a dataset
To create a single, **new** dataset in your model, call your model like this:
~~~php
$model->save([
  'stuff_name'        => 'some-string',
  'stuff_otherfield'  => 1234567
]);
$id = $model->lastInsertId();
~~~
Essentially, you're leaving out the primary key (`stuff_id` in this case) which will cause a new dataset to be created.
In this example, we're also retrieving the PKEY value of the entry we just created.

## Updating a dataset

If you want to update an existing (known) entry, perform this:
~~~php
$model->save([
  'stuff_id'          => 2,  // Existing pkey value
  'stuff_name'        => 'other-string',
]);
~~~
If you define the primary key (here: `stuff_id`) in the associative array and give it a meaningful value, this will advise the model to update an existing dataset with the given identifier value.
