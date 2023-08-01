<?php

namespace codename\core\validator\file\image;

use codename\core\validator\file\image;
use codename\core\validator\validatorInterface;

/**
 * Validating JPeG files
 * @package core
 * @since 2016-04-28
 */
class jpg extends image implements validatorInterface
{
    /**
     * Contains all whitelisted MIME Types
     * @var array
     */
    protected $mime_whitelist = [
      "image/jpg",
      "image/jpeg",
      "image/jpeg2000",
      "image/jpg2000",
    ];
}
