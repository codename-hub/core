<?php
namespace codename\core\validator\file\image;

/**
 * Validating JPeG files
 * @package core
 * @since 2016-04-28
 */
class jpg extends \codename\core\validator\file\image implements \codename\core\validator\validatorInterface {

    /**
     * Contains all whitelisted MIME Types
     * @var array
     */
    protected $mime_whitelist = array(
            "image/jpg",
            "image/jpeg",
            "image/jpeg2000",
            "image/jpg2000"
    );

}
