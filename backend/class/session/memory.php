<?php
namespace codename\core\session;

/**
 * Store sessions in-memory
 * @package core
 * @since 2018-11-01
 */
class memory extends \codename\core\session implements \codename\core\session\sessionInterface {

    /**
     * [protected description]
     * @var \codename\core\datacontainer
     */
    protected $datacontainer = null;

    /**
     * @inheritDoc
     */
    public function __construct(array $data = array())
    {
      parent::__construct($data);
      $this->datacontainer = new \codename\core\datacontainer($data);
    }
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\session_interface::start($data)
     */
    public function start(array $data) : \codename\core\session {
        $this->datacontainer = new \codename\core\datacontainer($data);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\session_interface::destroy()
     */
    public function destroy() {
      $this->datacontainer = null;
        return;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\session_interface::getData($key)
     */
    public function getData(string $key='') {
        return $this->datacontainer->getData($key);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\session_interface::setData($key, $value)
     */
    public function setData(string $key, $value) {
        return $this->datacontainer->setData($key, $value);
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\session_interface::isDefined($key)
     */
    public function isDefined(string $key) : bool {
        return $this->datacontainer->isDefined($key);
    }

    /**
     *
     */
    public function identify() : bool {
        return count($this->datacontainer->getData()) > 0;
    }

    /**
     * @inheritDoc
     */
    public function invalidate($sessionId)
    {
      throw new \LogicException('This session driver does not support Session Invalidation for foreign sessions');
    }

}
