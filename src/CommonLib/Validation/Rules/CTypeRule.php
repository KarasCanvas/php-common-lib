<?php
/**
 * @author Raven <karascanvas@qq.com>
 */

namespace CommonLib\Validation\Rules;

use CommonLib\Validation\ValidationRule;

/**
 * @summary CTypeRule <http://php.net/ctype>
 * @author Raven <karascanvas@qq.com>
 */
class CTypeRule extends ValidationRule
{
    protected $func;

    public function __construct($name)
    {
        parent::__construct(null);
        $func = 'ctype_'. $name;
        if(!function_exists($func)) {
            throw new \InvalidArgumentException(sprintf('Function "%s" not exists', $func));
        }
        $this->func = $func;
    }


    public function isValid($value, array $data = null)
    {
        return call_user_func($this->func, $value);
    }

}