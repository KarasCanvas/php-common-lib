<?php
/**
 * @author Raven <karascanvas@qq.com>
 */

namespace CommonLib\Validation\Rules;

use CommonLib\Validation\ValidationRule;

class RegularExpressionRule extends ValidationRule
{
    protected $pattern = null;

    protected $not = false;


    public function isValid($value, array $data = null)
    {
        $valid = preg_match($this->pattern, $value);
        return $this->not ? (!$valid) : $valid;
    }

}