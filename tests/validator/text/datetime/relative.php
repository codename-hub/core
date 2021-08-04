<?php
namespace codename\core\tests\validator\text\datetime;

use \codename\core\app;

/**
 * I will test the relative validator
 * @package codename\core
 * @since 2016-11-02
 */
class relative extends \codename\core\tests\validator\text {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueInvalid() {
        $this->assertEquals('VALIDATION.INVALID_RELATIVE_DATETIME', $this->getValidator()->validate('won')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEmpty($this->getValidator()->validate('now'));
    }

}
