<?php

namespace codename\core;

use codename\core\auth\credentialAuthInterface;
use codename\core\auth\groupInterface;

/**
 * The abstract auth class is the main extension point for all authentication classes.
 * @package core
 * @since 2016-02-01
 */
abstract class auth implements credentialAuthInterface, groupInterface
{
}
