<?php
/**
 * @author Raven <karascanvas@qq.com>
 */

namespace CommonLib\Validation\Rules;

use CommonLib\Validation\ValidationRule;

class UrlRule extends ValidationRule
{
    protected $pattern = '/^{schemes}:\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i';

    protected $schemes = ['http', 'https'];

    protected $maxLength = 2000;


    public function isValid($value, array $data = null)
    {
        if (!is_string($value) || strlen($value) > $this->maxLength) {
            return false;
        }
        if (strpos($this->pattern, '{schemes}') !== false) {
            $pattern = str_replace('{schemes}', '(' . implode('|', $this->schemes) . ')', $this->pattern);
        } else {
            $pattern = $this->pattern;
        }
        return preg_match($pattern, $value);
    }


    public function getErrorMessage($field = null)
    {
        return sprintf('%s is not a valid URL.', $field);
    }

}