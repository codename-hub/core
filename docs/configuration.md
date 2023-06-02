# Configuration

(Almost) every core-based application has to define two minimal configuration files:

* config/**app.json**: Defines the app's available 'contexts'
* config/**environment.json**: Defines available database connections, buckets, etc. (so-called 'drivers')

## The app.json

An app.json file could look like this:

~~~json
{
  "defaultcontext": "start",
  "defaulttemplateengine": "<the default named template engine>",
  "defaulttemplate": "blank",
  "context": {
    "start": {
      "defaultview" : "default",
      "view": {
        "default": { "public" : true }
      }
    },
    "example": {
      "defaultview": "someview",
      "view": {
        "someview": {
          "_security": {
            "group": "admin"
          }
        }
      }
    }
  }
}
~~~

This defines two available contexts, defaulting to 'start', if nothing is given.
Assuming a web-app purpose running on an Apache Webserver using mod_rewrite, you could call your APIs/URIs like:

- http://example.host/
- http://example.host/start (which is equal to the previous URL due to `defaultcontext`-Fallback)
- http://example.host/example
- http://example.host/example/someview (which is equal to the previous URL due to `defaultview`-Fallback)

If you're using CLI for your application, this would equal to

- `php your-bootstrap.php`
- `php your-bootstrap.php --context=start`
- `php your-bootstrap.php --context=example`
- `php your-bootstrap.php --context=example --view=someview`

## Possible configuration elements

| Key/Object Path                                             | Type      | Required | Description                                                                                              |
|-------------------------------------------------------------|-----------|----------|----------------------------------------------------------------------------------------------------------|
| defaultcontext                                              | string    | Yes      | Default context to use, if not set                                                                       |
| defaulttemplateengine                                       | string    | Yes      | Template engine to use, if not overridden in context-specific configuration, depends on environment.json |
| defaulttemplate                                             | string    | Yes      | Template to use, if not specified by context-specific configuration                                      |
| extensions                                                  | string[]  |          | Core-Extensions to load                                                                                  |     |
| context                                                     | object    | Yes      | Key-Value-style, named contexts and their respective configuration                                       |
| context.\<context.name\>                                    | object    | Yes      | Single context configuration                                                                             |
| context.\<context-name\>.defaultview                        | string    | Yes      | View to use, if not set                                                                                  |
| context.\<context-name\>.view                               | object    | Yes      | Key-Value-style view configurations                                                                      |
| context.\<context-name\>.view.\<view-name\>                 | object    | Yes      | Key-Value-style view configurations                                                                      |
| context.\<context-name\>.view.\<view-name\>.public          | bool/null |          | Public accessibility (skipping authentication)                                                           |     |
| context.\<context-name\>.view.\<view-name\>._security.group | string    |          | User group access                                                                                        |     |

## The environment.json

The environment.json file defines one or more 'environments' for your application.
This could be a configuration for running in a local dev environment and additionally a 'production' use configuration.
Production credentials should never be committed, please use environment variables to configure your application at runtime.

```json
{
  "dev" : {
    "database" : {
      "default" : {
        "driver" : "mysql",
        "host" : "db",
        "user" : "app_example",
        "pass" : "supersecretpassword",
        "database" : "example",
        "port" : 3306,
        "charset" : "utf8"
      }
    },
    "auth": {
    },
    "templateengine" : {
      "default" : {
        "driver" : "twig"
      }
    },
    "filesystem": {
      "local": {
        "driver": "local"
      }
    },
    "translate" : {
      "default" : {
        "driver" : "json"
      }
    },
    "cache": {
      "default": {
        "driver": "memory"
      }
    },
    "session" : {
      "default" : {
        "driver" : "dummy"
      }
    },
    "log" : {
      "errormessage": {
        "driver": "system",
        "data": {
          "name": "some error log",
          "minlevel" : -3
        }
      }
    }
  }
}
```

Essential keys per environment are:

* **database** (if needed): Database connection configuration
* **auth** (if needed): Authentication drivers/clients
* **templateengine**: Templating engines to make available
* **filesystem**: Should define one local filesystem client for internal purposes
* **bucket** (optional): Pre-defined buckets
* **translate**: i18n/translation drivers/clients
* **cache**: cache drivers/clients
* **session**: session drivers/clients
* **log**: log drivers/clients

Every key/section defines a **named** client for a specific purpose.
E.g. you could have a **cache** like

```json
{
  "cache": {
    "mycache": {
      "driver": "memcached",
      "host": "some-memcached.host.internal",
      "port": 11211
    }
  }
}
```

Which would be accessible via `app::getCache('mycache')`.
Similarly, this is possible via `app::getAuth('myauth')`, `app::getFilesystem('myfs')`, ... - you get the point.
**It is recommended to always define a 'default' client per type**, as most parts of the framework rely on default-fallbacks.
The only exception to this regulation is `app::getDb('somedb')`, which gets a client instance for the database.
In most cases, you won't need to call your database client directly.
