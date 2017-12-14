<?php
namespace codename\core\validator\file\image;

/**
 * Validating ICO files
 * @package core
 * @since 2016-04-28
 */
class ico extends \codename\core\validator\file\image implements \codename\core\validator\validatorInterface {

    /**
     * Contains all whitelisted MIME Types
     * @var array
     */
    protected $mime_whitelist = array(
            "image/x-icon"
    );

}
