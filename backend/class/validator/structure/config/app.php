<?php

namespace codename\core\validator\structure\config;

use codename\core\exception;
use codename\core\validator\structure\config;
use codename\core\validator\validatorInterface;
use ReflectionException;

/**
 * Validating application configurations
 * @package core
 * @since 2016-04-28
 */
class app extends config implements validatorInterface
{
    /**
     * Contains a list of array keys that MUST exist in the validated array
     * @var array
     */
    public $arrKeys = [
      'context',
      'defaultcontext',
      'defaulttemplate',
    ];

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

        foreach ($value['context'] as $context) {
            if ($context['custom'] ?? false) {
                continue;
            }
            if (count($errors = \codename\core\app::getValidator('structure_config_context')->validate($context)) > 0) {
                $this->errorstack->addError('VALUE', 'KEY_CONTEXT_INVALID', $errors);
                return $this->errorstack->getErrors();
            }
        }

        if (count($errors = \codename\core\app::getValidator('text_templatename')->reset()->validate($value['defaulttemplate'])) > 0) {
            $this->errorstack->addError('VALUE', 'KEY_DEFAULTTEMPLATE_INVALID', $errors);
            return $this->errorstack->getErrors();
        }

        if (count($errors = \codename\core\app::getValidator('text_contextname')->reset()->validate($value['defaultcontext'])) > 0) {
            $this->errorstack->addError('VALUE', 'KEY_DEFAULTCONTEXT_INVALID', $errors);
            return $this->errorstack->getErrors();
        }

        return $this->errorstack->getErrors();
    }
}
