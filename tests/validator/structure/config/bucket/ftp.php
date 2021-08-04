<?php
namespace codename\core\tests\validator\structure\config\bucket;

use \codename\core\app;

/**
 * I will test the ftp validator
 * @package codename\core
 * @since 2016-11-02
 */
class ftp extends \codename\core\tests\validator\structure {

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
          'ftpserver' => 'AAA'
        ];
        $errors = $this->getValidator()->validate($config);

        $this->assertNotEmpty($errors);
        $this->assertCount(1, $errors);
        $this->assertEquals('VALIDATION.PUBLIC_KEY_INVALID', $errors[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testTextInvalidKeyBaseurl() {
        $config = [
          'public'    => true,
          'basedir'   => 'AAA',
          'ftpserver' => 'AAA'
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
    public function testTextInvalidKeySftpserver() {
        $config = [
          'public'    => false,
          'basedir'   => 'AAA',
          'ftpserver' => 'AAA'
        ];
        $errors = $this->getValidator()->validate($config);

        $this->assertNotEmpty($errors);
        $this->assertCount(1, $errors);
        $this->assertEquals('VALIDATION.FTP_CONTAINER_INVALID', $errors[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $config = [
          'public'    => false,
          'basedir'   => 'AAA',
          'ftpserver' => [
            'host'  => 'TODO?',
            'port'  => 'TODO?',
            'user'  => 'TODO?',
            'pass'  => 'TODO?',
          ]
        ];
        $this->assertEmpty($this->getValidator()->validate($config));
    }

}
