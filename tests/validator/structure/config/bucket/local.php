<?php
namespace codename\core\tests\validator\structure\config\bucket;

use \codename\core\app;

/**
 * I will test the local validator
 * @package codename\core
 * @since 2016-11-02
 */
class local extends \codename\core\tests\validator\structure {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueMissingArrKeys() {
        $errors = $this->getValidator()->validate([]);

        $this->assertNotEmpty($errors);
        $this->assertCount(2, $errors);
        $this->assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[0]['__CODE'] );
        $this->assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[1]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testTextInvalidKeyPublic() {
        $config = [
          'public'    => 'AAA',
          'basedir'   => 'AAA',
        ];
        $errors = $this->getValidator()->validate($config);

        $this->assertNotEmpty($errors);
        $this->assertCount(1, $errors);
        $this->assertEquals('VALIDATION.PUBLIC_KEY_NOT_FOUND', $errors[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testTextInvalidKeyBaseurl() {
        $config = [
          'public'    => true,
          'basedir'   => 'AAA',
        ];
        $errors = $this->getValidator()->validate($config);

        $this->assertNotEmpty($errors);
        $this->assertCount(1, $errors);
        $this->assertEquals('VALIDATION.BASEURL_NOT_FOUND', $errors[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testTextDirectoryNotFound() {
        $this->markTestIncomplete('TODO: app::getFilesystem()');

        $config = [
          'public'    => false,
          'basedir'   => 'AAA',
        ];
        $errors = $this->getValidator()->validate($config);

        $this->assertNotEmpty($errors);
        $this->assertCount(1, $errors);
        $this->assertEquals('VALIDATION.DIRECTORY_NOT_FOUND', $errors[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $this->markTestIncomplete('TODO: app::getFilesystem()');
        
        $config = [
          'public'    => false,
          'basedir'   => 'AAA',
        ];
        $this->assertEmpty($this->getValidator()->validate($config));
    }

}
