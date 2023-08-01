<?php

namespace codename\core\validator\file\image;

use codename\core\validator\file\image;
use codename\core\validator\validatorInterface;

/**
 * Validating TIFF images / documents
 * @package core
 * @since 2016-04-28
 */
class tiff extends image implements validatorInterface
{
    /**
     * Contains all whitelisted MIME Types
     * @var array
     */
    protected $mime_whitelist = [
      "image/tiff",
    ];
}
