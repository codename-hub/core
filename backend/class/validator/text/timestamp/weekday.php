<?php
namespace codename\core\validator\text\timestamp;

class weekday extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

  /**
   * Numeric representation of MONDAY, see: ISO-8601
   * @var int
   */
  const MONDAY = 1;

  /**
   * Numeric representation of TUESDAY, see: ISO-8601
   * @var int
   */
  const TUESDAY = 2;

  /**
   * Numeric representation of WEDNESDAY, see: ISO-8601
   * @var int
   */
  const WEDNESDAY = 3;

  /**
   * Numeric representation of THURSDAY, see: ISO-8601
   * @var int
   */
  const THURSDAY = 4;

  /**
   * Numeric representation of FRIDAY, see: ISO-8601
   * @var int
   */
  const FRIDAY = 5;

  /**
   * Numeric representation of SATURDAY, see: ISO-8601
   * @var int
   */
  const SATURDAY = 6;

  /**
   * Numeric representation of SUNDAY, see: ISO-8601
   * @var int
   */
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

  /**
   * [setAllowedWeekdays description]
   * @param array $allowedWeekdays [description]
   */
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
    if(count(parent::validate($value)) != 0) {
      return $this->errorstack->getErrors();
    }

    if(count($this->allowedWeekdays) === 0) {
      $this->errorstack->addError('VALUE', 'ALLOWED_WEEKDAYS_NOT_SET', $this->allowedWeekdays);
    }

    if(!in_array(date('N', strtotime($value)), $this->allowedWeekdays)) {
      $this->errorstack->addError('VALUE', 'WEEKDAY_NOT_ALLOWED', $value);
    }

    return $this->errorstack->getErrors();
  }

}
