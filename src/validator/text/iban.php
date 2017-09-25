<?php
namespace codename\core\validator\text;

use codename\core\validator\text;

class iban extends \codename\core\validator\text implements \codename\core\validator\validatorInterface {

    /**
     * Contains the validatable countries and their specific IBAN string length requirement
     * @var array of strings
     */
    protected $countries = array('al'=>28,'ad'=>24,'at'=>20,'az'=>28,'bh'=>22,'be'=>16,'ba'=>20,'br'=>29,'bg'=>22,'cr'=>21,'hr'=>21,
        'cy'=>28,'cz'=>24,'dk'=>18,'do'=>28,'ee'=>20,'fo'=>18,'fi'=>18,'fr'=>27,'ge'=>22,'de'=>22,'gi'=>23,'gr'=>27,'gl'=>18,
        'gt'=>28,'hu'=>28,'is'=>26,'ie'=>22,'il'=>23,'it'=>27,'jo'=>30,'kz'=>20,'kw'=>30,'lv'=>21,'lb'=>28,'li'=>21,'lt'=>20,
        'lu'=>20,'mk'=>19,'mt'=>31,'mr'=>27,'mu'=>30,'mc'=>27,'md'=>24,'me'=>22,'nl'=>18,'no'=>15,'pk'=>24,'ps'=>29,'pl'=>28,
        'pt'=>25,'qa'=>29,'ro'=>24,'sm'=>27,'sa'=>24,'rs'=>22,'sk'=>24,'si'=>19,'es'=>24,'se'=>24,'ch'=>21,'tn'=>24,'tr'=>26,
        'ae'=>23,'gb'=>22,'vg'=>24);
    
    /**
     * Contains the values for the Checksum calculation based on the country 
     * @var array
     */
    protected $chars = array('a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,'l'=>21,
        'm'=>22,'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,'y'=>34,'z'=>35);
    
    /**
     * 
     */
    public function __CONSTRUCT() {
        return parent::__CONSTRUCT(true, 15, 30, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', '');
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
            $this->errorstack->addError('VALUE', 'IBAN_STRING_IS_EMPTY', $value);
            return $this->errorstack->getErrors();
        }
        
        $value = strtolower($value);
        
        $countrycode = substr($value, 0, 2);
        
        if(!array_key_exists($countrycode, $this->countries)) {
            $this->errorstack->addError('VALUE', 'IBAN_COUNTRY_NOT_FOUND', $value);
            return $this->errorstack->getErrors();
        }
        
        // check the country's IBAN code length
        if(strlen($value) != $this->countries[substr($value,0,2)]){
            $this->errorstack->addError('VALUE', 'IBAN_LENGH_NOT_MATCHING_COUNTRY', $value);
            return $this->errorstack->getErrors();
        }
    
        // Do some magic
        $MovedChar = substr($value, 4).substr($value,0,4);
        $MovedCharArray = str_split($MovedChar);
        $NewString = '';
    
        foreach($MovedCharArray AS $key => $value){
            if(!is_numeric($MovedCharArray[$key]) && array_key_exists($MovedCharArray[$key], $this->chars)) {
                $MovedCharArray[$key] = $this->chars[$MovedCharArray[$key]];
            }
            $NewString .= $MovedCharArray[$key];
        }
    
        if(bcmod($NewString, '97') != 1) {
            $this->errorstack->addError('VALUE', 'IBAN_CHECKSUM_FAILED', $value);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }

}
