<?php
namespace codename\core\tests\validator\text;

/**
 * I will test the text_timezone validator
 * @package codename\core
 * @since 2020-01-03
 */
class timezone extends \codename\core\tests\validator\text {

    /**
     * Testing all available timezone identifiers
     * @return void
     */
    public function testAllValidTimezoneIdentifiers() {
        $identifiers = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
        $validator = $this->getValidator();
        foreach($identifiers as $idx => $id) {
          $validator->reset();
          $this->assertEmpty($res = $validator->validate($id), $id . print_r($res, true));
        }
    }

    /**
     * Testing an invalid timezone identifier on a foreign planet
     * @return void
     */
    public function testInvalidTimezoneIdentifier() {
        $this->assertEquals('VALIDATION.INVALID_TIMEZONE', $this->getValidator()->validate('Mars/Phobos')[0]['__CODE'] );
    }

    // /**
    //  * Testing an invalid time offset (+25 hrs)
    //  * @return void
    //  */
    // public function testInvalidTimezoneOffset() {
    //     $this->assertEquals('VALIDATION.INVALID_TIMEZONE', $this->getValidator()->validate('+2500')[0]['__CODE']);
    // }

    /**
     * Testing a valid timezone offset
     * @return void
     */
    public function testValidTimezoneOffset() {
        $this->assertEmpty($this->getValidator()->validate('+0200'));
    }

    /**
     * Testing a valid timezone offset
     * @return void
     */
    public function testValidTimezoneIdentifier() {
        $this->assertEmpty($this->getValidator()->validate('Europe/Berlin'));
    }

}
