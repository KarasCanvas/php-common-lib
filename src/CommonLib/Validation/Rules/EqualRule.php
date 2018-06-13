<?php
/**
 * @author Raven <karascanvas@qq.com>
 */

namespace CommonLib\Validation\Rules;

use CommonLib\Validation\ValidationRule;

class EqualRule extends ValidationRule
{
    protected $value;

    protected $strict = false;

    protected $not = false;


    public function isValid($value, array $data = null)
    {
        $result = $this->strict ? $this->value === $value : $this->value == $value;
        return $this->not ? !$result : $result;
    }

}