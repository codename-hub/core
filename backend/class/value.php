<?php
namespace codename\core;

/**
 * This is a future implementation of the dataobject
 * @package core
 * @since 2016-08-10
 */
class value implements \codename\core\value\valueInterface {

    /**
     * I cannot instanciate, because the given $value cannot be validated against my validator.
     * @var string
     */
    CONST EXCEPTION_CONSTRUCT_INVALIDDATATYPE = 'EXCEPTION_CONSTRUCT_INVALIDDATATYPE';

    /**
     * I contain the precise value
     * @var mixed|null
     */
    protected $value = null;

    /**
     * This validator is used to validate the value on generation.
     * @var string
     */
    protected $validator = 'text';

    /**
     * I will set in the value
     * @param mixed|null $value
     */
    public function __construct($value) {
        if(count($errors = \codename\core\app::getValidator($this->validator)->reset()->validate($value)) > 0) {
            throw new \codename\core\exception(self::EXCEPTION_CONSTRUCT_INVALIDDATATYPE, \codename\core\exception::$ERRORLEVEL_FATAL, $errors);
        }
        $this->value = $value;
        unset($this->validator);
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\value\valueInterface::get()
     */
    public function get() {
        return $this->value;
    }

}
