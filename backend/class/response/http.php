<?php

namespace codename\core\response;

use codename\core\app;
use codename\core\exception;
use codename\core\response;
use codename\core\sensitiveException;
use ReflectionException;

/**
 * I handle all the data for an HTTP response
 * @package core
 * @since 2016-05-31
 */
class http extends response
{
    /**
     * You are requesting a resource for the front-end to load additionally.
     * I'm afraid that I don't know the type of resource you requested
     * @var string
     */
    public const EXCEPTION_REQUIRERESOURCE_INVALIDRESOURCETYPE = 'EXCEPTION_REQUIRERESOURCE_INVALIDRESOURCETYPE';
    /**
     * You are requesting a resource for the front-end to load additionally.
     * I'm afraid that I did not find the desired resource on the file system.
     * @var string
     */
    public const EXCEPTION_REQUIRERESOURCE_RESOURCENOTFOUND = 'EXCEPTION_REQUIRERESOURCE_RESOURCENOTFOUND';
    /**
     * Contains the status code
     * @var int
     */
    protected int $statusCode = 200;
    /**
     * Contains the status text
     * @var string
     */
    protected string $statusText = 'OK';
    /**
     * CDN prefixes and matching rules
     * @var array
     */
    protected array $cdnPrefixes = [];
    /**
     * Contains data for redirecting the user after finishing the request
     * @var array|string|null
     */
    protected string|array|null $redirect = null;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $data = [])
    {
        parent::__construct();
    }

    /**
     * [reset description]
     * @return response [description]
     */
    public function reset(): response
    {
        $this->data = [];
        return $this;
    }

    /**
     * sets a cdn prefix
     *
     * @param [type] $prefix [description]
     * @param [type] $target [description]
     */
    public function setCDNResourcePrefix($prefix, $target): void
    {
        $this->cdnPrefixes[$prefix] = $target;
    }

    /**
     * Redirects the user at some point to the given destination.
     * Either pass a valid URL to the function (including protocol!) or pass the app/context/view/action data
     *
     * @param string $string [description]
     * @param string|null $context [description]
     * @param string|null $view [description]
     * @param string|null $action [description]
     */
    public function setRedirect(string $string, ?string $context = null, ?string $view = null, ?string $action = null): void
    {
        if (strpos($string, '://') || str_starts_with($string, '/')) {
            $this->redirect = $string;
            return;
        }
        $this->redirect = [
          'app' => $string,
          'context' => $context,
          'view' => $view,
          'action' => $action,
        ];
    }

    /**
     * Sets parameters used for redirection
     * @param array $param [description]
     */
    public function setRedirectArray(array $param): void
    {
        $this->redirect = $param;
    }

    /**
     * This function performs the redirection by using a forward header ("Location: $url").
     * @return void
     * @todo make a makeUrl function for the parameters
     */
    public function doRedirect(): void
    {
        if (is_null($this->redirect)) {
            return;
        }

        if (is_string($this->redirect)) {
            $this->setHeader("Location: " . $this->redirect);
        }

        if (is_array($this->redirect)) {
            $url = '/?' . http_build_query($this->redirect);
            $this->setHeader("Location: " . $url);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setHeader(string $header): void
    {
        header($header);
    }

    /**
     * I store the requirement of additional frontend resources in the response container
     * @param string $type
     * @param string $content
     * @param int $priority [last = -1, everything else: add at index]
     * @return bool
     * @throws ReflectionException
     * @throws exception
     */
    public function requireResource(string $type, string $content, int $priority = -1): bool
    {
        if (!in_array($type, ['js', 'css', 'script', 'style', 'head'])) {
            throw new exception(self::EXCEPTION_REQUIRERESOURCE_INVALIDRESOURCETYPE, exception::$ERRORLEVEL_FATAL, $type);
        }

        if (!array_key_exists($type, $this->resources)) {
            $this->resources[$type] = [];
        }

        if (($type == 'script') || ($type == 'style') || ($type == 'head')) {
            if ($priority >= 0) {
                // insert at given position
                array_splice($this->resources[$type], $priority, 0, $content);
            } else {
                // add to end
                $this->resources[$type][] = $content;
            }
            return true;
        }

        if (!str_contains('://', $content)) {
            //
            // Local asset check
            //
            if (app::getInstance('filesystem_local')->fileAvailable($file = app::getHomedir() . $content)) {
                //
                // Local asset available
                // Current style: load inline, automatically.
                //
                if ($type === 'css') {
                    if (pathinfo($file, PATHINFO_EXTENSION) == 'css') {
                        return self::requireResource('style', file_get_contents($file));
                    } else {
                        // exception
                        throw new exception('EXCEPTION_REQUIRERESOURCE_DISALLOWED', exception::$ERRORLEVEL_FATAL, $content);
                    }
                } elseif ('js') {
                    if (pathinfo($file, PATHINFO_EXTENSION) == 'js') {
                        return self::requireResource('script', file_get_contents($file));
                    } else {
                        // exception
                        throw new exception('EXCEPTION_REQUIRERESOURCE_DISALLOWED', exception::$ERRORLEVEL_FATAL, $content);
                    }
                } else {
                    // error!
                    throw new exception('EXCEPTION_REQUIRERESOURCE_DISALLOWED', exception::$ERRORLEVEL_FATAL, $content);
                }
            }
            //
        // NOTE: if not found locally, it might be an external asset - continue.
            //
        } elseif (!app::getInstance('filesystem_local')->fileAvailable(app::getHomedir() . $content)) {
            throw new exception(self::EXCEPTION_REQUIRERESOURCE_RESOURCENOTFOUND, exception::$ERRORLEVEL_FATAL, $content);
        }

        if (count($this->cdnPrefixes) > 0 && !str_contains('://', $content)) {
            foreach ($this->cdnPrefixes as $prefix => $target) {
                if (str_starts_with($content, $prefix)) {
                    $content = $target . (str_starts_with($content, '/') ? '' : '/') . $content;
                    break;
                }
            }
        }

        if ($priority >= 0) {
            // check for correct position and fix, if needed
            if (in_array($content, $this->resources[$type]) && (($pos = array_search($content, $this->resources[$type])) !== $priority)) {
                if ($pos !== false) {
                    // remove from old position
                    unset($this->resources[$type][$pos]);
                }
            }
            // insert at given index (priority)
            array_splice($this->resources[$type], $priority, 0, $content);
        } elseif (!in_array($content, $this->resources[$type])) {
            // add to end
            $this->resources[$type][] = $content;
        }

        return true;
    }

    /**
     * Returns an array of resources that have been requested by the backend
     * @param string $type
     * @return array
     */
    public function getResources(string $type): array
    {
        if (isset($this->resources[$type])) {
            return $this->resources[$type];
        }
        return [];
    }

    /**
     * Will show a desktop notification on the browser if the client allowed it.
     * @param string $subject
     * @param string $text
     * @param string $image
     * @param string $sound
     * @return void
     * @throws ReflectionException
     * @throws exception
     * @see ./www/public/library/templates/shared/javascript/alpha_engine.js :: doCallback($url, callback());
     */
    public function addNotification(string $subject, string $text, string $image, string $sound): void
    {
        $file = CORE_WEBROOT . $image;
        if (!app::getFilesystem()->fileAvailable($file)) {
            app::getLog('debug')->debug("Cannot send notification, the image $file is not available!");
            return;
        }

        $file = CORE_WEBROOT . $sound;
        if (!app::getFilesystem()->fileAvailable($file)) {
            app::getLog('debug')->debug("Cannot send notification, the sound $file is not available!");
            return;
        }

        $this->addJs("joNotify('$subject', '$text', '$image', '$sound');");
    }

    /**
     * Add a JS resource to the response template
     * @param string $js
     * @return void
     */
    public function addJs(string $js): void
    {
        $jsdo = $this->getData('jsdo');

        if (is_null($jsdo)) {
            $jsdo = [];
        }

        $jsdo[] = $js;
        $this->setData('jsdo', $jsdo);
    }

    /**
     * {@inheritDoc}
     */
    public function displayException(\Exception $e): void
    {
        $this->setStatuscode(500, "Internal Server Error");

        // log to stderr
        // NOTE: we log twice, as the second one might be killed
        // by memory exhaustion
        if ($e instanceof exception && !is_null($e->info)) {
            $info = print_r($e->info, true);
        } else {
            $info = '<none>';
        }

        error_log("[SAFE ERROR LOG] " . "{$e->getMessage()} (Code: {$e->getCode()}) in File: {$e->getFile()}:{$e->getLine()}, Info: $info");

        if (
            defined('CORE_ENVIRONMENT')
            // && CORE_ENVIRONMENT != 'production'
        ) {
            echo "<h3>Hicks!</h3>";
            echo "<h6>{$e->getMessage()} (Code: {$e->getCode()})</h6>";

            if ($e instanceof exception && !is_null($e->info)) {
                echo "<h6>Information:</h6>";
                echo "<pre>";
                print_r($e->info);
                echo "</pre>";
            }

            //
            // CHANGED 2019-09-02: handle sensitive exceptions differently
            //
            if (!($e instanceof sensitiveException)) {
                echo "<h6>Stacktrace:</h6>";
                echo "<pre>";
                print_r($e->getTrace());
                echo "</pre>";
            }
            die();
        }

        $this->pushOutput();
    }

    /**
     * Helper to set HTTP status codes
     * @param int $statusCode
     * @param string $statusText
     * @return response
     */
    public function setStatuscode(int $statusCode, string $statusText): response
    {
        $this->statusCode = $statusCode;
        $this->statusText = $statusText;
        return $this;
    }

    /**
     * {@inheritDoc}
     * simply output/echo to HTTP stream
     */
    public function pushOutput(): void
    {
        http_response_code($this->translateStatusToHttpStatus());
        echo $this->getOutput();
    }

    /**
     * [translateStatusToHttpStatus description]
     * @return int [description]
     */
    protected function translateStatusToHttpStatus(): int
    {
        $translate = [
          self::STATUS_SUCCESS => 200,
          self::STATUS_INTERNAL_ERROR => 500,
          self::STATUS_NOTFOUND => 404,
          self::STATUS_FORBIDDEN => 403,
          self::STATUS_UNAUTHENTICATED => 401,
          self::STATUS_REQUEST_SIZE_TOO_LARGE => 413,
          self::STATUS_BAD_REQUEST => 400,
        ];
        return $translate[$this->status] ?? 418; // fallback: teapot
    }

    /**
     * {@inheritDoc}
     */
    protected function translateStatus(): int
    {
        return $this->translateStatusToHttpStatus();
    }
}
