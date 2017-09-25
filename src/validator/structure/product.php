<?php
namespace codename\core\validator\structure;

class product extends \codename\core\validator\structure implements \codename\core\validator\validatorInterface {

    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = array(
            'product_id',
            'product_count',
            'product_price'
    );

}
