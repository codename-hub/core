<?php
namespace codename\core\value\structure\api\codename;

/**
 * Data Object for serviceproviders containing all the required information
 * @package core
 * @since 2016-11-08
 */
class serviceprovider extends \codename\core\value\structure {

    /**
     * {@inheritDoc}
     * @see \codename\core\value::$validator
     */
    protected $validator = 'structure_api_codename_serviceprovider';

    /**
     * Returns the host name of the service provider
     * @return string
     */
    public function getHost() : string {
        return $this->value['host'];
    }

    /**
     * Returns the port number of the service provider
     * @return int
     */
    public function getPort() : int {
        return $this->value['port'];
    }

    /**
     * Will return the complete URL to the service provider's base
     * @return string
     */
    public function getUrl() : string {
        return $this->value['host'] . ':' . $this->value['port'];
    }

}
