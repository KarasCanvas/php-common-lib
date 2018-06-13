<?php
/**
 * @author Raven <karascanvas@qq.com>
 */

namespace CommonLib\Validation;


class ValidationState
{
    protected $errors = array();


    public function isValid()
    {
        return count($this->errors) < 1;
    }


    public function hasError()
    {
        return count($this->errors) > 0;
    }


    public function addError($field, $message)
    {
        if (isset($this->errors[$field])) {
            $this->errors[$field][] = $message;
        } else {
            $this->errors[$field] = array($message);
        }
    }


    public function getErrors()
    {
        return $this->errors;
    }


    public function firstError()
    {
        foreach ($this->errors as $item) {
            return $item[0];
        }
        return null;
    }

}