<?php
namespace \codename\core\validator\image;

/**
 * Validating VASA images
 * @package core
 * @since 2016-04-28
 */
class vasa extends \codename\core\validator\image implements \codename\core\validator\validatorInterface {

    /**
     * Contains all whitelisted MIME Types
     * @var array
     */
    protected $mime_whitelist = array(
            "image/vasa"
    );
    
}
