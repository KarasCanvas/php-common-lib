<?php
/**
 * @author Raven <karascanvas@qq.com>
 */

namespace CommonLib\Validation;

class Validator
{
    const RULE_BASE_CLASS = '\CommonLib\Validation\ValidationRule';
    const RULE_NAMESPACE = '\CommonLib\Validation\Rules';


    protected $map = array(
        'invalid'  => 'InvalidRule',
        'boolean'  => 'BooleanRule',
        'bool'     => 'BooleanRule',
        'notempty' => 'RequiredRule',
        'require'  => 'RequiredRule',
        'required' => 'RequiredRule',
        'regex'    => 'RegularExpressionRule',
        'digit'    => 'DigitRule',
        'ctype'    => 'CTypeRule',
        'equal'    => 'EqualRule',
        'enum'     => 'EnumRule',
        'length'   => 'StringLengthRule',
        'range'    => 'RangeRule',
        'compare'  => 'CompareRule',
        'email'    => 'EmailRule',
        'url'      => 'UrlRule',
        'uri'      => 'UrlRule',
        'function' => 'FunctionRule',
        'func'     => 'FunctionRule',
    );


    public function setRule($name, $class)
    {
        $this->map[$name] = $class;
    }


    public function setRules(array $rules, $namespace = null)
    {
        $rules = (array)$rules;
        if($namespace !== null) {
            $namespace = rtrim($namespace, '\\');
            array_walk($rules, function(&$value) use($namespace) {
                $value = $namespace . '\\' . $value;
            });
        }
        $this->map = array_merge($this->map, $rules);
    }


    public function validate(array $data, array $rules, $all = false)
    {
        $state = new ValidationState();
        foreach ($rules as $item) {
            $item = static::normalizeItem($item);
            if ($item === null) {
                continue;
            }
            $rule = $this->getRuleInstance($item['rule'], $item['params']);
            foreach ($item['fields'] as $field) {
                if (!$this->validateField($rule, $data, $item, $field, $message)) {
                    $state->addError($field, $message);
                    if (!$all) {
                        break;
                    }
                }
            }
        }
        return $state;
    }


    protected function validateField(ValidationRule $rule, array $data, array $item, $field, &$message = null)
    {
        $value = isset($data[$field]) ? $data[$field] : null;
        if ($rule->isValid($value, $data)) {
            return true;
        }
        $message = $item['error'] == null
            ? $rule->getErrorMessage($field)
            : sprintf($item['error'], $field);
        return false;
    }


    /**
     * getRuleInstance
     * @param string $name
     * @param mixed $arg
     * @return ValidationRule
     */
    protected function getRuleInstance($name, $arg)
    {
        if (!isset($this->map[$name])) {
            if ($name == 'class' && class_exists($arg)) {
                $class = static::getReflectionClass($arg);
                if ($class->isSubclassOf(self::RULE_BASE_CLASS)) {
                    return $class->newInstance();
                }
            }
            throw new \LogicException(sprintf('Validation rule "%s" not found.', $name));
        }
        $className = $this->map[$name];
        if (stripos($className, '\\') === false) {
            $className = self::RULE_NAMESPACE . '\\' . $className;
        }
        if ($arg === null) {
            return new $className();
        }
        return new $className($arg);
    }


    protected static function normalizeItem($entry)
    {
        if (is_array($entry) && count($entry) >= 2) {
            return array(
                'fields' => (array)$entry[0],
                'rule'   => $entry[1],
                'params' => isset($entry[2]) ? $entry[2] : null,
                'error'  => isset($entry[3]) ? $entry[3] : null,
            );
        }
        return null;
    }


    /**
     * getReflectionClass
     * @author Raven
     * @param string $name
     * @return \ReflectionClass
     */
    protected static function getReflectionClass($name)
    {
        static $cache = array();
        if (!isset($cache[$name])) {
            $cache[$name] = new \ReflectionClass($name);
        }
        return $cache[$name];
    }

}