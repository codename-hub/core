<?php
namespace codename\core\validator\file\image;

/**
 * Validating PNG images
 * @package core
 * @since 2016-04-28
 */
class png extends \codename\core\validator\file\image implements \codename\core\validator\validatorInterface {

    /**
     * Contains all whitelisted MIME Types
     * @var array
     */
    protected $mime_whitelist = array(
            "image/png"
    );

}
