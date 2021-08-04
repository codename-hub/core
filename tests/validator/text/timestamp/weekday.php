<?php
namespace codename\core\tests\validator\text\timestamp;

use \codename\core\app;

/**
 * I will test the weekday validator
 * @package codename\core
 * @since 2016-11-02
 */
class weekday extends \codename\core\tests\validator\text {

  /**
   * @inheritDoc
   */
  public function getValidator($allWeekdays = true): \codename\core\validator
  {
    $weekdays = [];
    if($allWeekdays) {
      $weekdays[] = \codename\core\validator\text\timestamp\weekday::MONDAY;
      $weekdays[] = \codename\core\validator\text\timestamp\weekday::TUESDAY;
      $weekdays[] = \codename\core\validator\text\timestamp\weekday::WEDNESDAY;
      $weekdays[] = \codename\core\validator\text\timestamp\weekday::THURSDAY;
      $weekdays[] = \codename\core\validator\text\timestamp\weekday::FRIDAY;
    }
    return new \codename\core\validator\text\timestamp\weekday(false, $weekdays);
  }

  /**
   * Testing validators for Erors
   * @return void
   */
  public function testValueAllowedWeekdaysNotSet() {
      $this->assertEquals('VALIDATION.ALLOWED_WEEKDAYS_NOT_SET', $this->getValidator(false)->validate('2021-04-17')[0]['__CODE'] );
  }

  /**
   * Testing validators for Erors
   * @return void
   */
  public function testValueNotAllowed() {
      $this->assertEquals('VALIDATION.WEEKDAY_NOT_ALLOWED', $this->getValidator()->validate('2021-04-17')[0]['__CODE'] );
  }

  /**
   * Testing validators for Erors
   * @return void
   */
  public function testValueValid() {
      $this->assertEmpty($this->getValidator()->validate('2021-04-13'));
  }

}
