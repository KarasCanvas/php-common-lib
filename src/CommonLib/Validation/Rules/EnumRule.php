<?php
/**
 * @author Raven <karascanvas@qq.com>
 */

namespace CommonLib\Validation\Rules;

use CommonLib\Validation\ValidationRule;

class EnumRule extends ValidationRule
{
    protected $values;


    public function __construct($values)
    {
        parent::__construct(null);
        $this->values = (array)$values;
    }


    public function isValid($value, array $data = null)
    {
        return in_array($value, $this->values);
    }


    public function getErrorMessage($field = null)
    {
        return sprintf('Value of %s must be one of (%s)', $field, implode(',', $this->values));
    }

}