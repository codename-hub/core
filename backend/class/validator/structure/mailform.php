<?php
namespace codename\core\validator\structure;
use \codename\core\app;

class mailform extends \codename\core\validator\structure implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'recipient',
            'subject', // optional?
            'body'
    );

    /**
     * @inheritDoc
     */
    public function validate($value): array
    {
      // Check email addresses
      if($value['recipient'] && count($errors = app::getValidator('text_email')->validate($value['recipient'])) > 0) {
        $this->errorstack->addError('VALUE', 'INVALID_EMAIL_ADDRESS', $errors);
      }
      if($value['cc'] && count($errors = app::getValidator('text_email')->validate($value['cc'])) > 0) {
        $this->errorstack->addError('VALUE', 'INVALID_EMAIL_ADDRESS', $errors);
      }
      if($value['bcc'] && count($errors = app::getValidator('text_email')->validate($value['bcc'])) > 0) {
        $this->errorstack->addError('VALUE', 'INVALID_EMAIL_ADDRESS', $errors);
      }
      if($value['reply-to'] && count($errors = app::getValidator('text_email')->validate($value['reply-to'])) > 0) {
        $this->errorstack->addError('VALUE', 'INVALID_EMAIL_ADDRESS', $errors);
      }

      // Check body length
      if(strlen($value['body']) == 0) { // or bigger than??
        $this->errorstack->addError('VALUE', 'INVALID_EMAIL_BODY', $errors);
      }
      // Check body length
      if(strlen($value['subject']) == 0) { // or bigger than??
        $this->errorstack->addError('VALUE', 'INVALID_EMAIL_SUBJECT', $errors);
      }

      // @TODO check template (for existance/validity?)

      return array_merge($errors, $this->errorstack->getErrors());
    }
}
