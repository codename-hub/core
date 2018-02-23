<?php
namespace codename\core;

/**
 * The abstract credential class is the main extension point for all credential classes.
 * @package core
 * @since 2018-02-22
 */
abstract class credential extends \codename\core\config implements \codename\core\credential\credentialInterface {

  /**
   * [EXCEPTION_REST_CREDENTIAL_VALIDATION description]
   * @var string
   */
  const EXCEPTION_CORE_CREDENTIAL_VALIDATION = 'EXCEPTION_REST_CREDENTIAL_VALIDATION';

  /**
   * validator name to be used for validating input data
   * @var string|null
   */
  protected static $validatorName = null;

  /**
   * @inheritDoc
   */
  public function __construct(array $data)
  {
    // if validator is set, validate!
    if(self::$validatorName != null && count($errors = app::getValidator(self::$validatorName)->validate($data)) > 0) {
      throw new exception(self::EXCEPTION_CORE_CREDENTIAL_VALIDATION, exception::$ERRORLEVEL_FATAL, $errors);
    }
    parent::__construct($data);
  }

  /**
   * @inheritDoc
   */
  public abstract function getIdentifier(): string;

  /**
   * @inheritDoc
   */
  public abstract function getAuthentication();

  /**
   * [public description]
   * @return string
   */
  // public abstract function getAuthenticationHash() : string;

}
