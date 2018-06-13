<?php
/**
 * @author Raven <karascanvas@qq.com>
 */

namespace CommonLib\Validation\Rules;

use CommonLib\Validation\ValidationRule;

class RequiredRule extends ValidationRule
{

    public function isValid($value, array $data = null)
    {
        return ($value !== null && $value !== '');
    }


    public function getErrorMessage($field = null)
    {
        return sprintf('Field %s is required', $field);
    }

}