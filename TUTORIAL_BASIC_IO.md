# TUTORIAL - BASIC IO #

This Tutorial is for giving you a quick overview about the most important components of the core framework.

Requires the following knowledge:

* __TUTORIAL - APP BOOTSTRAPPING__

-----------------------

__Requirements__

* Context Backing Class
* View/Output Code
* Context Configuration (Visibility/Access)

__Files to be touched__

* _<your-project>_/backend/class/context/__your-context__.php (_create_)
* _<your-project>_/frontend/view/__your-context__/__your-view__.php (_create_)
* _<your-project>_/config/app.json (_modify/create_)

- - - -

__Step 1: Create your backing class__

Create a new PHP file in backend/class/context/.
This is your "context" (you can think of it as a controller).

~~~php
<?php
namespace codename\demo\context;
use codename\core\exception;

/**
 * sample context
 */
class mycontext extends \codename\core\context {

  /**
   * some description
   */
  public function view_myview() {
    // ... do stuff.
    $this->getResponse()->setData('mykey', 'myvalue');
  }

}
~~~

_NOTES:_

* Make sure you're using the correct namespace (e.g. _namespace codename\demo\context_ if your app is called "_demo_")
* Make sure your class is inheriting from a context class (e.g. _\codename\core\context_ )
* Make sure you prefix all your view functions with view_
* You may omit the PHP closing tag (some modern-stylish PHP programming stuff...)
* Don't you ever dare to use __camelCasing__ for context backing class files

- - - -

__Step 2: Create your view code__

Create a new PHP file (depending on your preferred templating engine)
at frontend/view/__your-context__/__your-view__.php
This may be the raw HTML/PHP-Inline code of your view.
If you're using __Twig__ for templating/writing views, it may be called "__your-view__.twig"

~~~php
<?php namespace codename\demo;?>
<p>Some output code</p>
<p>Get a value from the response: <?= app::getResponse()->getData('mykey') ?>
~~~

_NOTES:_

* The example is a bare inline PHP code
* Don't forget to namespace the code if you're using *.php files (irrelevant if you're using Twig)
* Then, you can access the response container via app::getResponse()->getData( ... );

- - - -

__Step 3: Allow your view to be accessed__

Open your app configuration at __config/app.json__.
Under the key "__context__" create a json object declaring your context outline:

~~~json
{
  "context": {
    "mycontext": {
      "defaultview": "myview",
      "view": {
        "myview": {
          "public": true
        }
      }
    }
  }
}
~~~

_NOTES:_

* Required keys for each context:
  defaultview
  view
* Set __"public" : true__ for a view to be accessed without authentication. This is fine for testing purposes.
* Optional keys:
  "type": optionally, you can define "crud" or another inheritable context type. Then, you might not need to define stuff that is already present in the base context type.
  "template": explicitly use a template here
  "defaulttemplateengine": explicitly use a template engine defined in environment.json here

__Step 3.5: Test!__

Fine, now you're good to go!
Fire up your browser at _http://your.url/?context=mycontext&view=myview_ (or even leave the view parameter, as you've defined the default view in your app.json).