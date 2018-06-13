<?php
/**
 * @author Raven <karascanvas@qq.com>
 */

namespace CommonLib\Validation\Rules;

use CommonLib\Validation\ValidationRule;

class CompareRule extends ValidationRule
{
    protected $field = null;

    protected $value = null;

    protected $operator = '==';


    public function isValid($value, array $data = null)
    {
        $compareValue = $this->value === null ? $data[$this->field] : $this->value;
        return $this->compare($value, $compareValue, $this->operator);
    }


    protected function compare($value, $compareValue, $operator)
    {
        switch ($operator) {
            case '==':
                return $value == $compareValue;
            case '===':
                return $value === $compareValue;
            case '!=':
                return $value != $compareValue;
            case '!==':
                return $value !== $compareValue;
            case '>':
                return $value > $compareValue;
            case '>=':
                return $value >= $compareValue;
            case '<':
                return $value < $compareValue;
            case '<=':
                return $value <= $compareValue;
            default:
                throw new \LogicException(sprintf('Unknown operator: %s', $this->operator));
        }
    }


    public function getErrorMessage($field = null)
    {
        switch ($this->operator) {
            case '==':
                return sprintf('%s must be equal to specified value.', $field);
            case '===':
                return sprintf('%s must be strict equal to specified value.', $field);
            case '!=':
                return sprintf('%s must not be equal to specified value.', $field);
            case '!==':
                return sprintf('%s must not be strict equal to specified value.', $field);
            case '>':
                return sprintf('%s must be greater than specified value.', $field);
            case '>=':
                return sprintf('%s must be greater than or equal to specified value.', $field);
            case '<':
                return sprintf('%s must be less than specified value.', $field);
            case '<=':
                return sprintf('%s must be less than or equal to specified value.', $field);
            default:
                return parent::getErrorMessage($field);
        }
    }

}