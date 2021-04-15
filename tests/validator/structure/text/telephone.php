<?php
namespace codename\core\tests\validator\structure\text;

/**
 * I will test the telephone validator
 * @package codename\core
 * @since 2016-11-02
 */
class telephone extends \codename\core\tests\validator\structure {

    /**
     * Testing validators for Erors
     * @return void
     */
     public function testValueInvalidPhoneNumber() {
       $this->assertEquals('VALIDATION.INVALID_PHONE_NUMBER', $this->getValidator()->validate([ 'AAA' ])[0]['__CODE'] );
     }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEmpty($this->getValidator()->validate([]));
    }

}
