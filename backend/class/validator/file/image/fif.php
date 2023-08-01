<?php

namespace codename\core\validator\file\image;

use codename\core\validator\file\image;
use codename\core\validator\validatorInterface;

/**
 * Validating fif images
 * @package core
 * @since 2016-04-28
 */
class fif extends image implements validatorInterface
{
    /**
     * Contains all whitelisted MIME Types
     * @var array
     */
    protected $mime_whitelist = [
      "image/fif",
    ];
}
