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
      if(count(parent::validate($value)) != 0) {
          return $this->errorstack->getErrors();
      }

      if(is_null($value)) {
          return $this->errorstack->getErrors();
      }

      $textEmailErrors = [];

      // Check email addresses
      if(($value['recipient'] ?? false) && count($errors = app::getValidator('text_email')->reset()->validate($value['recipient'])) > 0) {
        $this->errorstack->addError('VALUE', 'INVALID_EMAIL_ADDRESS', $errors);
        $textEmailErrors = array_merge($textEmailErrors, $errors);
      }
      if(($value['cc'] ?? false) && count($errors = app::getValidator('text_email')->reset()->validate($value['cc'])) > 0) {
        $this->errorstack->addError('VALUE', 'INVALID_EMAIL_ADDRESS', $errors);
        $textEmailErrors = array_merge($textEmailErrors, $errors);
      }
      if(($value['bcc'] ?? false) && count($errors = app::getValidator('text_email')->reset()->validate($value['bcc'])) > 0) {
        $this->errorstack->addError('VALUE', 'INVALID_EMAIL_ADDRESS', $errors);
        $textEmailErrors = array_merge($textEmailErrors, $errors);
      }
      if(($value['reply-to'] ?? false) && count($errors = app::getValidator('text_email')->reset()->validate($value['reply-to'])) > 0) {
        $this->errorstack->addError('VALUE', 'INVALID_EMAIL_ADDRESS', $errors);
        $textEmailErrors = array_merge($textEmailErrors, $errors);
      }

      // Check body length
      if(!($value['body'] ?? false) || strlen($value['body']) == 0) { // or bigger than??
        $this->errorstack->addError('VALUE', 'INVALID_EMAIL_BODY', $textEmailErrors);
      }
      // Check body length
      if(!($value['subject'] ?? false) || strlen($value['subject']) == 0) { // or bigger than??
        $this->errorstack->addError('VALUE', 'INVALID_EMAIL_SUBJECT', $textEmailErrors);
      }

      // @TODO check template (for existance/validity?)

      return array_merge($textEmailErrors, $this->errorstack->getErrors());
    }
}
