<?php
namespace codename\core\validator\file;

/**
 * Validating image files
 * @package core
 * @since 2016-04-28
 */
class image extends \codename\core\validator implements \codename\core\validator\validatorInterface {

    /**
     * Contains all whitelisted MIME Types
     * @var array
     */
    protected $mime_whitelist = array(
            "image/jpeg",
            "image/jpg",
            "image/jpeg2000",
            "image/jpg2000",
            "image/png",
            "image/gif",
            "image/fif",
            "image/tiff",
            "image/vasa",
            "image/x-icon"
    );

}
