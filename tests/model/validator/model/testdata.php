<?php

namespace codename\core\tests\model\validator\model;

use codename\core\validator\structure;

class testdata extends structure
{
    /**
     * {@inheritDoc}
     */
    public function validate(mixed $value): array
    {
        parent::validate($value);

        $field = 'testdata_text';
        if ($value[$field] == 'disallowed_value') {
            $this->errorstack->addError($field, 'FIELD_INVALID', $value[$field]);
        }

        if (($value['testdata_text'] == 'disallowed_condition') && ($value['testdata_date'] == '2021-01-01')) {
            $this->errorstack->addError('GENERIC_ERROR', 'DISALLOWED_CONDITION', [
              'testdata_text' => $value['testdata_text'],
              'testdata_date' => $value['testdata_date'],
            ]);
        }

        return $this->getErrors();
    }
}
