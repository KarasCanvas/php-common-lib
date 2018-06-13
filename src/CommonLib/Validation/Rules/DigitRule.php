<?php
/**
 * @author Raven <karascanvas@qq.com>
 */

namespace CommonLib\Validation\Rules;

use CommonLib\Validation\ValidationRule;

class DigitRule extends ValidationRule
{

    public function isValid($value, array $data = null)
    {
        return ctype_digit($value);
    }


    public function getErrorMessage($field = null)
    {
        return sprintf('%s must be decimal digit value', $field);
    }

}