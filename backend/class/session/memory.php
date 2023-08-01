<?php

namespace codename\core\session;

use codename\core\datacontainer;
use LogicException;

/**
 * Store sessions in-memory
 * @package core
 * @since 2018-11-01
 */
class memory extends \codename\core\session implements sessionInterface
{
    /**
     * [protected description]
     * @var datacontainer
     */
    protected datacontainer $datacontainer;

    /**
     * {@inheritDoc}
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->datacontainer = new datacontainer([]);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\session_interface::start($data)
     */
    public function start(array $data): \codename\core\session
    {
        $this->datacontainer = new datacontainer($data);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\session_interface::destroy()
     */
    public function destroy(): void
    {
        $this->datacontainer = new datacontainer();
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\session_interface::setData($key, $value)
     */
    public function setData(string $key, mixed $data): void
    {
        $this->datacontainer->setData($key, $data);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\session_interface::isDefined($key)
     */
    public function isDefined(string $key): bool
    {
        return $this->datacontainer->isDefined($key);
    }

    /**
     *
     */
    public function identify(): bool
    {
        return count($this->datacontainer->getData()) > 0;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\session_interface::getData($key)
     */
    public function getData(string $key = ''): mixed
    {
        return $this->datacontainer->getData($key);
    }

    /**
     * {@inheritDoc}
     */
    public function invalidate(int|string $sessionId): void
    {
        throw new LogicException('This session driver does not support Session Invalidation for foreign sessions');
    }
}
