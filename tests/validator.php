<?php
namespace codename\core\tests;

/**
 * I am just an extender for the unittest class
 * @package codename\core
 * @since 2016-11-02
 */
abstract class validator extends \PHPUnit\Framework\TestCase {

  /**
   * @inheritDoc
   */
  public static function tearDownAfterClass(): void
  {
    parent::tearDownAfterClass();

    // WARNING: you _HAVE_ to reset this right here
    // as far as you use app::getValidator() somewhere in your tests
    // as it may have side-effects on other tests
    // that rely on a 'fresh' validator (e.g. lifecycle)
    // (due to the fact validators keep their latest state until ->reset())
    \codename\core\tests\overrideableApp::reset();
  }

  /**
   * [getValidator description]
   * @return \codename\core\validator [description]
   */
  protected function getValidator() : \codename\core\validator {
    // load the respective validator via namespace, by instanciated class name
    // we have to remove __CLASS__ (THIS exact class here)

    // extract validator name from current class name, stripped by validator base namespace
    $validatorClass = str_replace(__CLASS__.'\\', '', (new \ReflectionClass($this))->getName());

    // replace \ by _
    $validatorName = str_replace('\\', '_', $validatorClass);

    $validator = \codename\core\app::getValidator($validatorName);
    $validator->reset();
    return $validator;
  }

}
