<?php

namespace codename\core\validator\file\image;

use codename\core\validator\file\image;
use codename\core\validator\validatorInterface;

/**
 * Validating ICO files
 * @package core
 * @since 2016-04-28
 */
class ico extends image implements validatorInterface
{
    /**
     * Contains all whitelisted MIME Types
     * @var array
     */
    protected $mime_whitelist = [
      "image/x-icon",
    ];
}
