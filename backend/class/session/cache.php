<?php

namespace codename\core\session;

use codename\core\app;
use codename\core\exception;
use codename\core\session;
use LogicException;
use ReflectionException;

/**
 * Storing sessions on a cache service
 * @package core
 * @since 2016-08-11
 */
class cache extends session
{
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\session_interface::start($data)
     */
    public function __construct(array $data)
    {
//        parent::__construct($data);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @param array $data
     * @return session
     * @throws ReflectionException
     * @throws exception
     * @see sessionInterface::start
     */
    public function start(array $data): session
    {
        app::getCache()->set($this->getCacheGroup(), $this->getCacheKey(), $data);
        return $this;
    }

    /**
     * Returns the cache group name for sessions (e.g. a prefix)
     * @return string [description]
     */
    protected function getCacheGroup(): string
    {
        return 'SESSION';
    }

    /**
     * Returns the cache key for sessions.
     * Contains the application name and some kind of session identifier
     * (e.g. cookie value)
     * @return string
     * @throws ReflectionException
     * @throws exception
     */
    protected function getCacheKey(): string
    {
        return "SESSION_" . app::getApp() . "_" . session_id();
    }

    /**
     *
     * {@inheritDoc}
     * @throws ReflectionException
     * @throws exception
     * @see sessionInterface::destroy
     */
    public function destroy(): void
    {
        app::getCache()->clearKey($this->getCacheGroup(), $this->getCacheKey());
        // reset internal data array
        // to be rebuild on next call
        $this->data = [];
    }

    /**
     *
     * {@inheritDoc}
     * @param string $key
     * @param mixed $data
     * @throws ReflectionException
     * @throws exception
     * @see session::setData
     */
    public function setData(string $key, mixed $data): void
    {
        $cacheData = app::getCache()->get($this->getCacheGroup(), $this->getCacheKey());
        if (!is_array($cacheData)) {
            return;
        }
        if (strlen($key) == 0) {
            return;
        }
        if (!array_key_exists($key, $cacheData)) {
            return;
        }
        $cacheData[$key] = $data;
        app::getCache()->set($this->getCacheGroup(), $this->getCacheKey(), $cacheData);
        // reset internal data array
        // to be rebuild on next call
        $this->data = [];
    }

    /**
     * @return bool
     * @throws ReflectionException
     * @throws exception
     */
    public function identify(): bool
    {
        $data = $this->getData();
        $this->data = $data;
        return (is_array($data) && count($data) != 0);
    }

    /**
     * Return the value of the given key. Either pass a direct name, or use a tree to navigate through the data set
     * ->get('my>config>key')
     * @param string $key
     * @return mixed
     * @throws ReflectionException
     * @throws exception
     */
    public function getData(string $key = ''): mixed
    {
        $this->makeData();

        if (strlen($key) == 0) {
            return $this->data;
        }

        if (!str_contains($key, '>')) {
            if ($this->isDefined($key)) {
                return $this->data[$key];
            }
            return null;
        }

        $myConfig = $this->data;
        foreach (explode('>', $key) as $myKey) {
            if (!array_key_exists($myKey, $myConfig)) {
                return null;
            }
            $myConfig = $myConfig[$myKey];
        }

        return $myConfig;
    }

    /**
     * @return void
     * @throws ReflectionException
     * @throws exception
     */
    private function makeData(): void
    {
        if (count($this->data) == 0) {
            $this->data = app::getCache()->get($this->getCacheGroup(), $this->getCacheKey()) ?? [];
        }
    }

    /**
     *
     * {@inheritDoc}
     * @param string $key
     * @return bool
     * @throws ReflectionException
     * @throws exception
     * @see session::isDefined
     */
    public function isDefined(string $key): bool
    {
        $data = app::getCache()->get($this->getCacheGroup(), $this->getCacheKey());
        if (!is_array($data)) {
            return false;
        }
        return array_key_exists($key, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function invalidate(int|string $sessionId): void
    {
        throw new LogicException('Not implemented'); // TODO
        // TODO/CHECK: app::getCache()->clearKey($this->getCacheGroup(), "SESSION");
    }
}
