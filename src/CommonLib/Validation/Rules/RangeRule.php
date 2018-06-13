<?php
/**
 * @author Raven <karascanvas@qq.com>
 */

namespace CommonLib\Validation\Rules;

use CommonLib\Validation\ValidationRule;

class RangeRule extends ValidationRule
{
    protected $min = null;

    protected $max = null;


    public function isValid($value, array $data = null)
    {
        if ($this->min === null) {
            return $value <= $this->max;
        } else if ($this->max === null) {
            return $value >= $this->min;
        }
        return $value >= $this->min && $value <= $this->max;
    }

    public function getErrorMessage($field = null)
    {
        if ($this->min === null) {
            return sprintf('%s must be equal or less than %d', $field, $this->max);
        } else if ($this->max === null) {
            return sprintf('%s must be equal or greater than %d', $field, $this->min);
        }
        return sprintf('%s must be between %d and %d ', $field, $this->min, $this->max);
    }

}