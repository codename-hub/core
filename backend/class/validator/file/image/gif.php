<?php

namespace codename\core\validator\file\image;

use codename\core\validator\file\image;

/**
 * Validating gif
 * @package core
 * @since 2016-04-28
 */
class gif extends image
{
    /**
     * Contains all whitelisted MIME Types
     * @var array
     */
    protected $mime_whitelist = [
      "image/gif",
    ];
}
