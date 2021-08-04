<?php
namespace codename\core\tests\validator\text;

use \codename\core\app;

/**
 * I will test the time validator
 * @package codename\core
 * @since 2016-11-02
 */
class time extends \codename\core\tests\validator\text {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalideString() {
        $this->assertEquals('VALIDATION.VALUE_INVALID_TIME_STRING', $this->getValidator()->validate('123')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueIsHoursInvalid() {
        $this->assertEquals('VALIDATION.VALUE_INVALID_TIME_HOURS', $this->getValidator()->validate('25:10:10')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueIsMinutesInvalid() {
        $this->assertEquals('VALIDATION.VALUE_INVALID_TIME_MINUTES', $this->getValidator()->validate('10:61:01')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueIsSecondsInvalid() {
        $this->assertEquals('VALIDATION.VALUE_INVALID_TIME_SECONDS', $this->getValidator()->validate('10:10:61')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEmpty($this->getValidator()->validate('01:02:03'));
    }

}
