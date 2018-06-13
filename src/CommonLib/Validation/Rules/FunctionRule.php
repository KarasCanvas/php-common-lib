<?php
/**
 * @author Raven <karascanvas@qq.com>
 */

namespace CommonLib\Validation\Rules;

use CommonLib\Validation\ValidationRule;

class FunctionRule extends ValidationRule
{
    protected $func;


    public function __construct($func)
    {
        parent::__construct(null);
        if(!is_callable($func)) {
            throw new \InvalidArgumentException('Argument "func" must be a callable');
        }
        $this->func = $func;
    }


    public function isValid($value, array $data = null)
    {
        return call_user_func($this->func, $value, $data);
    }

}