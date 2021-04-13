<?php
namespace codename\core\tests\validator\text\filepath;

use \codename\core\app;

/**
 * I will test the absolute validator
 * @package codename\core
 * @since 2016-11-02
 */
class absolute extends \codename\core\tests\validator\text {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueNotSetBeginSlash() {
        $this->assertEquals('VALIDATION.MUST_BEGIN_WITH_SLASH', $this->getValidator()->validate('example/example')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueSetEndSlash() {
        $this->assertEquals('VALIDATION.MUST_NOT_END_WITH_SLASH', $this->getValidator()->validate('/example/example/')[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->assertEmpty($this->getValidator()->validate('/example/example'));
    }

}
