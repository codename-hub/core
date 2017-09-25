<?php
namespace codename\core\validator\text\timestamp;

class weekday extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

  // Numeric representation of the weekday, see: ISO-8601
  const MONDAY = 1;
  const TUESDAY = 2;
  const WEDNESDAY = 3;
  const THURSDAY = 4;
  const FRIDAY = 5;
  const SATURDAY = 6;
  const SUNDAY = 7;

  /**
   * array of allowed weekdays (ISO-8601)
   * @var int[]
   */
  protected $allowedWeekdays = array();

  /**
   *
   * {@inheritDoc}
   * @see \codename\core\validator_text::__construct($nullAllowed, $minlength, $maxlength, $allowedchars, $forbiddenchars)
   */
  public function __CONSTRUCT(bool $nullAllowed = false, array $allowedWeekdays = array()) {
    $this->setAllowedWeekdays($allowedWeekdays);
    parent::__CONSTRUCT($nullAllowed, 1, 32, '0123456789 :-.');
    return $this;
  }

  public function setAllowedWeekdays(array $allowedWeekdays = array()) {
    foreach($allowedWeekdays as &$v) {
      $v = intval($v);
    }
    $this->allowedWeekdays = $allowedWeekdays;
    foreach($this->allowedWeekdays as $d) {
      if(!in_array($d, array(self::MONDAY, self::TUESDAY, self::WEDNESDAY, self::THURSDAY, self::FRIDAY, self::SATURDAY, self::SUNDAY))) {
        // error?
      }
    }
  }

  /**
   * @inheritDoc
   */
  public function validate($value): array
  {
    $errors = parent::validate($value);
    if(!in_array(date('N', strtotime($value)), $this->allowedWeekdays)) {
      $this->errorstack->addError('VALUE', 'WEEKDAY_NOT_ALLOWED', $value);
    }
    return $this->errorstack->getErrors();
  }

}
