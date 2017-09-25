<?php
namespace codename\core\validator\number;

/**
 * Validating integers
 * @package core
 * @author Kevin Dargel
 * @since 2017-05-31
 */
class integer extends \codename\core\validator\number implements \codename\core\validator\validatorInterface {
  /**
   * @inheritDoc
   */
  public function __CONSTRUCT(  bool $nullAllowed = true,float $minvalue = null,float $maxvalue = null,int $maxprecision = null) {
    $value = parent::__CONSTRUCT($nullAllowed, $minvalue, $maxvalue, 0);
    return $value;
  }
}
