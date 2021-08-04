<?php
namespace codename\core\tests\validator\text;

use \codename\core\app;

/**
 * I will test the json validator
 * @package codename\core
 * @since 2016-11-02
 */
class json extends \codename\core\tests\validator\text {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueEmptyString() {
        $this->assertEmpty($this->getValidator()->validate('') );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalidJson() {
        $this->assertEquals('VALIDATION.JSON_INVALID', $this->getValidator()->validate('AAAAA')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEmpty($this->getValidator()->validate('{"AAAAA":"AAAAA"}'));
    }

}
