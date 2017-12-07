<?php
namespace codename\core\validator\file\image;

/**
 * Validating gif
 * @package core
 * @since 2016-04-28
 */
class gif extends \codename\core\validator\file\image {

    /**
     * Contains all whitelisted MIME Types
     * @var array
     */
    protected $mime_whitelist = array(
            "image/gif"
    );

}
