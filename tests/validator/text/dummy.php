<?php
namespace codename\core\tests\validator\text;

use \codename\core\app;

/**
 * I will test the dummy validator
 * @package codename\core
 * @since 2016-11-02
 */
class dummy extends \codename\core\tests\validator\text {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEmpty($this->getValidator()->validate('core'));
    }

}
