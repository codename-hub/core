<?php

namespace codename\core\validator\structure;

use codename\core\app;
use codename\core\exception;
use codename\core\validator\structure;
use codename\core\validator\validatorInterface;
use ReflectionException;

class cart extends structure implements validatorInterface
{
    /**
     *
     * {@inheritDoc}
     * @param mixed $value
     * @return array
     * @throws ReflectionException
     * @throws exception
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate(mixed $value): array
    {
        if (count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }

        if (is_null($value)) {
            return $this->errorstack->getErrors();
        }

        foreach ($value as $product) {
            if (count($errors = app::getValidator('structure_product')->validate($product)) > 0) {
                $this->errorstack->addError('VALUE', 'INVALID_PRODUCT_FOUND', $errors);
                break;
            }
        }

        return $this->errorstack->getErrors();
    }
}
