<?php
/**
 * @author Raven <karascanvas@qq.com>
 */

namespace CommonLib\Validation\Rules;

use CommonLib\Validation\ValidationRule;

class EmailRule extends ValidationRule
{
    protected $pattern = '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/';

    protected $fullPattern = '/^[^@]*<[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?>$/';

    protected $full = false;

    protected $maxLength = 320;

    protected $checkDNS = false;


    public function isValid($value, array $data = null)
    {
        if (!is_string($value) || strlen($value) >= $this->maxLength) {
            $valid = false;
        } elseif (!preg_match('/^(.*<?)(.*)@(.*?)(>?)$/', $value, $matches)) {
            $valid = false;
        } else {
            $domain = $matches[3];
            $valid = preg_match($this->pattern, $value) || $this->full && preg_match($this->fullPattern, $value);
            if ($valid && $this->checkDNS) {
                $valid = checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
            }
        }
        return $valid;
    }


    public function getErrorMessage($field = null)
    {
        return sprintf('%s is not a valid email address.', $field);
    }

}