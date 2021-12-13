<?php
namespace codename\core\validator\text;
use codename\core\validator\text;

class email extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     * Blacklist some email providers
     * @var array of strings
     */
    protected $forbiddenHosts = array('0815.ru','10minutemail.com','3d-painting.com','antichef.net','BeefMilk.com','bio-muesli.info',
        'bio-muesli.net','cust.in','despammed.com','DingBone.com','discardmail.com','discardmail.de','dontsendmespam.de','edv.to',
        'emailias.com','ero-tube.org','film-blog.biz','FudgeRub.com','geschent.biz','great-host.in','guerillamail.org','imails.info',
        'jetable.com','kulturbetrieb.info','kurzepost.de','LookUgly.com','mail4trash.com','mailinator.com','mailnull.com',
        'nervmich.net','nervtmich.net','nomail2me.com','nurfuerspam.de','objectmail.com','owlpic.com','proxymail.eu','rcpt.at',
        'recode.me','s0ny.net','sandelf.de','SmellFear.com','sneakemail.com','snkmail.com','sofort-mail.de','spam.la','spambog.com',
        'spambog.de','spambog.ru','spamex.com','spamgourmet.com','spammotel.com','squizzy.de','super-auswahl.de','teewars.org',
        'tempemail.net','trash-mail.at','trash-mail.com','trash2009.com','trashmail.at','trashmail.de','trashmail.me','trashmail.net',
        'trashmail.ws','watch-harry-potter.com','watchfull.net','wegwerf-email.net','wegwerfadresse.de','wegwerfmail.de',
        'wegwerfmail.net','wegwerfmail.org','willhackforfood.biz','whyspam.me','yopmail.com');

    /**
     * @param bool $nullAllowed
     */
    public function __CONSTRUCT(bool $nullAllowed = false) {
        return parent::__CONSTRUCT($nullAllowed, 0,64, '', '*');
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate($value) : array {
        if(count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }

        if(strlen($value) == 0) {
            return $this->errorstack->getErrors();
        }

        if(!(strpos($value, '@') > 0)) {
            $this->errorstack->addError('VALUE', 'EMAIL_AT_NOT_FOUND', $value);
            return $this->errorstack->getErrors();
        }

        $address = explode('@', $value);

        if(count($address) != 2) {
            $this->errorstack->addError('VALUE', 'EMAIL_AT_NOT_UNIQUE', $value);
            return $this->errorstack->getErrors();
        }

        if(strlen($address[1]) == 0) {
            $this->errorstack->addError('VALUE', 'EMAIL_DOMAIN_INVALID', $value);
            return $this->errorstack->getErrors();
        }

        if(in_array($address[1], $this->forbiddenHosts)) {
            $this->errorstack->addError('VALUE', 'EMAIL_DOMAIN_BLOCKED', $value);
            return $this->errorstack->getErrors();
        }

        if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
          $this->errorstack->addError('VALUE', 'EMAIL_INVALID', $value);
          return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }

}
