<?php
/**
 * @author Raven <karascanvas@qq.com>
 */

namespace CommonLib\Validation\Rules;

use CommonLib\Validation\ValidationRule;

class InvalidRule extends ValidationRule
{
    public function isValid($value, array $data = null)
    {
        return false;
    }
}