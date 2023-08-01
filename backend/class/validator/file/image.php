<?php

namespace codename\core\validator\file;

use codename\core\validator;
use codename\core\validator\validatorInterface;

/**
 * Validating image files
 * @package core
 * @since 2016-04-28
 */
class image extends validator implements validatorInterface
{
    /**
     * Contains all whitelisted MIME Types
     * @var array
     */
    protected $mime_whitelist = [
      "image/jpeg",
      "image/jpg",
      "image/jpeg2000",
      "image/jpg2000",
      "image/png",
      "image/gif",
      "image/fif",
      "image/tiff",
      "image/vasa",
      "image/x-icon",
    ];
}
