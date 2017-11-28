# TUTORIAL - CORE FRAMEWORK QUICKSTART #

This Tutorial is for giving you a quick overview about the most important components of the core framework.

# App Bootstrapping #

__TODO__

# Basic I/O #

__Requirements__
* Context Backing Class
* View/Output Code
* Context Configuration (Visibility/Access)

__Files to be touched__

* _<your-project>_/backend/class/context/__your-context__.php (_create_)
* _<your-project>_/frontend/view/__your-context__/__your-view__.php (_create_)
* _<your-project>_/config/app.json (_modify/create_)

__Step 1: Create your backing class__

Create a new PHP file in backend/class/context/.
This is your "context" (you can think of it as a controller).

```php
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
  }

}
```

_NOTES:_
* Make sure you're using the correct namespace (e.g. _namespace codename\demo\context_ if your app is called "_demo_")
* Make sure your class is inheriting from a context class (e.g. _\codename\core\context_ )
* Make sure you prefix all your view functions with _view