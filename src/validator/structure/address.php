<?php
namespace codename\core\validator\structure;

/**
 * Validating address arrays
 * @package core
 * @since 2016-04-28
 */
class address extends \codename\core\validator\structure implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'country_id',
            'postalcode',
            'city',
            'street',
            'number'
    );

}
