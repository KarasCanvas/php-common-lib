<?php
/**
 * @author Raven <karascanvas@qq.com>
 */
namespace CommonLib\Validation;

abstract class ValidationRule
{

    public function __construct(array $options)
    {
        if (is_array($options)) {
            foreach ($options as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }


    public abstract function isValid($value, array $data = null);


    public function getErrorMessage($name = null)
    {
        return sprintf('%s is not valid', $name);
    }

}