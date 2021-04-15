<?php
namespace codename\core\tests\validator\structure;

use \codename\core\app;

/**
 * I will test the appstack validator
 * @package codename\core
 * @since 2016-11-02
 */
class appstack extends \codename\core\tests\validator\structure {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testTextAppstackEmpty() {
        $this->assertEquals('VALIDATION.APPSTACK_EMPTY', $this->getValidator()->validate([])[0]['__CODE'] );
        return;
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEmpty($this->getValidator()->validate(['core']));
    }

}
