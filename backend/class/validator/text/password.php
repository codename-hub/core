<?php

namespace codename\core\validator\text;

use codename\core\validator\text;
use codename\core\validator\validatorInterface;

class password extends text implements validatorInterface
{
    /**
     *
     * {@inheritDoc}
     * @see \codename\core\validator_interface::validate($value)
     */
    public function validate(mixed $value): array
    {
        parent::__construct(false, 6, 20);
        if (count(parent::validate($value)) != 0) {
            return $this->errorstack->getErrors();
        }

        $complexity = [
          'UPPERCASE' => 'A B C D E F G H I J K L M N O P Q R S T U V W X Y Z',
          'LOWERCASE' => 'a b c d e f g h i j k l m n o p q r s t u v w x y z',
          'NUMERIC' => '0 1 2 3 4 5 6 7 8 9',
          'SPECIAL' => '! § $ % & / ( ) = ? [ ] | { } @ . ; : _  - # * " , \' ',
        ];

        foreach ($complexity as $type => $string) {
            $found = false;
            foreach (explode(' ', $string) as $char) {
                if (strlen($char) == 0) {
                    continue;
                }
                if (str_contains($value, $char)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $this->errorstack->addError('VALUE', 'PASSWORD_' . $type . '_CHARACTER_NOT_FOUND', $value);
            }
        }

        return $this->errorstack->getErrors();
    }
}
