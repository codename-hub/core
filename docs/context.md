# Context

A context is comparable to the concept of a 'controller', but represents an "API Endpoint" on its own.
The core-provided default 'context' assumes the existence of 'views' and 'actions'.
A contexts consists of the respective config in the app.json (to make it available) and a backing class:

~~~php
class start extends \codename\core\context {
  public function view_default(): void {
    // a default view
  }
  public function action_special(): void {
    // an action
  }
}
~~~

## Naming convention

For every context, regardless of implementation, this is obligatory:

- Contexts must be named all lowercase, w/o special characters
- Contexts must be placed in backend/class/**context/** (depending on autoloading configuration)

Depending on the used context base class (e.g. `\codename\core\context`), the following may apply:

- Views in a Context are functions prefixed with **view_** and the respective view names.
- View names may contain underscores, but must be named all lowercase, too.
- Actions in a Context are functions prefixed with **action_** and the respective action name.
- Action names may have additional requirements and limitations regarding special characters.

## Execution order

By default, a context based on `\codename\core\context` is executed in this order:

- **isAllowed**(): bool (optional, might prevent further execution)
- **action_**-Function (optional, if defined)
- **view_**-Function

You may create your own context class implementing `contextInterface` and `customContextInterface` to overcome limitations implied by using **view**s and **action**s. The `customContextInterface` just requires a method `run` to be defined publicly and a `isPublic(): bool` (as this type of context can, by definition, not be fully listed and configured via app.json).

## Available methods

If the context class is based on `\codename\core\context` (or similar, derived ones), the following methods provided by the bootstrapInstance class are available:

- **getModel**(...): gets a new model instance for the given model name
- **getRequest**(): gets current request instance
- **getResponse**(): gets current response instance

## Simple I/O example

The following example assumes existence of a model named 'test' and some minimal application configuration.
This is meant as

~~~php
namespace codename\example\context;
/**
 * A basic context class
 */
class testcontext extends \codename\core\context {
  /**
   * The default view function of this context
   * @return void
   */
  public function view_default(): void {
    // get request parameter
    $someParameter = $this->getRequest()->getData('some_parameter');
    // get model
    $model = $this->getModel('test');
    // store data
    $model->save([
      'test_data' => $someParameter
    ]);
    // get the ID/PKEY value we just created
    $id = $model->lastInsertId();
    // load the freshly created dataset
    $dataset = $model->load($id);
    // put into response
    $this->getResponse()->setData('output_key', $dataset);
  }
}
~~~

If you're using a REST-enabled application and call your endpoint (`/testcontext/default?some_parameter=abc`) the response could look like:

~~~json
{
  "success": 1,
  "data": {
    "output_key": {
      "test_id": 1,
      "test_created": "2021-11-22 12:34:56",
      "test_modified": null,
      "test_data": "abc"
    }
  }
}
~~~

If your application is **not** rest-enabled, you have to do something with your data you put in the response instance.
Assuming you're using **twig** as templateengine, you could have a view-template in frontend/view/testcontext/default.twig:

~~~twig
This is an example demonstrating the response output.
You've created this dataset:
<pre>{{ response.getData('dataset') | json_encode() }}</pre>
~~~
