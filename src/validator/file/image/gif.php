<?php
namespace codename\core\validator\image;

/**
 * Validating gif
 * @package core
 * @since 2016-04-28
 */
class gif extends \codename\core\validator\image implements \codename\core\validator\image {

    /**
     * Contains all whitelisted MIME Types
     * @var array
     */
    protected $mime_whitelist = array(
            "image/gif"
    );
    
}
