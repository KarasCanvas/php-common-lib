<?php
/**
 * @author Raven <karascanvas@qq.com>
 */

namespace CommonLib\Validation\Rules;

use CommonLib\Validation\ValidationRule;

class BooleanRule extends ValidationRule
{
    protected $strict = false;


    public function isValid($value, array $data = null)
    {
        if($this->strict) {
            return ($value === 1 || $value === 0);
        }
        return ($value == 1 || $value == 0);
    }


    public function getErrorMessage($field = null)
    {
        return sprintf('%s must be 1 or 0', $field);
    }

}