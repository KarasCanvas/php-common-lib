<?php
/**
 * @author Raven <karascanvas@qq.com>
 */

namespace CommonLib\Validation\Rules;

use CommonLib\Validation\ValidationRule;

class StringLengthRule extends ValidationRule
{
    protected $min = null;

    protected $max = null;


    public function isValid($value, array $data = null)
    {
        $len = strlen($value);
        if ($this->min === null) {
            return $len <= $this->max;
        } else if ($this->max === null) {
            return $len >= $this->min;
        }
        return $len >= $this->min && $len <= $this->max;
    }

    public function getErrorMessage($field = null)
    {
        if ($this->min === null) {
            return sprintf('Length of %s must be equal or less than %d', $field, $this->max);
        } else if ($this->max === null) {
            return sprintf('Length of %s must be equal or greater than %d', $field, $this->min);
        }
        return sprintf('Length of %s must be between %d and %d ', $field, $this->min, $this->max);
    }

}