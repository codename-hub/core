<?php
namespace codename\core\tests\validator\structure;

use \codename\core\app;

/**
 * I will test the mailform validator
 * @package codename\core
 * @since 2016-11-02
 */
class mailform extends \codename\core\tests\validator\structure {

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueMissingArrKeys() {
        $errors = $this->getValidator()->validate([]);

        $this->assertNotEmpty($errors);
        $this->assertCount(3, $errors);
        $this->assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[0]['__CODE'] );
        $this->assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[1]['__CODE'] );
        $this->assertEquals('VALIDATION.ARRAY_MISSING_KEY', $errors[2]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testTextInvalidEmailRecipient() {
        $config = [
          'recipient' => '.@example.com',
          'subject'   => 'example',
          'body'      => 'example'
        ];
        $errors = $this->getValidator()->validate($config);

        $this->assertNotEmpty($errors);
        $this->assertCount(2, $errors);
        $this->assertEquals('VALIDATION.EMAIL_INVALID', $errors[0]['__CODE'] );
        $this->assertEquals('VALIDATION.INVALID_EMAIL_ADDRESS', $errors[1]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testTextInvalidEmailCc() {
        $config = [
          'recipient' => 'example@example.com',
          'cc'        => '.@example.com',
          'subject'   => 'example',
          'body'      => 'example'
        ];
        $errors = $this->getValidator()->validate($config);

        $this->assertNotEmpty($errors);
        $this->assertCount(2, $errors);
        $this->assertEquals('VALIDATION.EMAIL_INVALID', $errors[0]['__CODE'] );
        $this->assertEquals('VALIDATION.INVALID_EMAIL_ADDRESS', $errors[1]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testTextInvalidEmailBcc() {
        $config = [
          'recipient' => 'example@example.com',
          'bcc'       => '.@example.com',
          'subject'   => 'example',
          'body'      => 'example'
        ];
        $errors = $this->getValidator()->validate($config);

        $this->assertNotEmpty($errors);
        $this->assertCount(2, $errors);
        $this->assertEquals('VALIDATION.EMAIL_INVALID', $errors[0]['__CODE'] );
        $this->assertEquals('VALIDATION.INVALID_EMAIL_ADDRESS', $errors[1]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testTextInvalidEmailReplyTo() {
        $config = [
          'recipient' => 'example@example.com',
          'reply-to'  => '.@example.com',
          'subject'   => 'example',
          'body'      => 'example'
        ];
        $errors = $this->getValidator()->validate($config);

        $this->assertNotEmpty($errors);
        $this->assertCount(2, $errors);
        $this->assertEquals('VALIDATION.EMAIL_INVALID', $errors[0]['__CODE'] );
        $this->assertEquals('VALIDATION.INVALID_EMAIL_ADDRESS', $errors[1]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testTextInvalidSubject() {
        $config = [
          'recipient' => 'example@example.com',
          'subject'   => '',
          'body'      => 'example'
        ];
        $errors = $this->getValidator()->validate($config);

        $this->assertNotEmpty($errors);
        $this->assertCount(1, $errors);
        $this->assertEquals('VALIDATION.INVALID_EMAIL_SUBJECT', $errors[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testTextInvalidBody() {
        $config = [
          'recipient' => 'example@example.com',
          'subject'   => 'example',
          'body'      => ''
        ];
        $errors = $this->getValidator()->validate($config);

        $this->assertNotEmpty($errors);
        $this->assertCount(1, $errors);
        $this->assertEquals('VALIDATION.INVALID_EMAIL_BODY', $errors[0]['__CODE'] );
    }

    /**
     * Testing validators for Erors
     * @return void
     */
    public function testValueValid() {
        $config = [
          'recipient' => 'example@example.com',
          'subject'   => 'example',
          'body'      => 'example'
        ];
        $this->assertEmpty($this->getValidator()->validate($config));
    }

}
