<?php
/**
 * @author Raven <karascanvas@qq.com>
 */
namespace CommonLib\Standard;

abstract class EnumBase
{
    const __default = null;

    private static $_cache = null;

    private $_value = null;


    public function __construct($value = null)
    {
        if ($value === null) {
            $value = self::getMetaData(0);
        }
        if (!self::isValidValue($value)) {
            throw new \InvalidArgumentException("Enum value is not valid.");
        }
        $this->_value = $value;
    }


    public function getValue()
    {
        return $this->_value;
    }


    public function __toString()
    {
        return self::getName($this->_value);
    }


    public function has(EnumBase $value)
    {
        if ($value instanceof EnumBase) {
            return ($this->_value & $value->_value) !== 0;
        }
        return false;
    }


    public function hasValue($value)
    {
        return ($this->_value & $value) !== 0;
    }


    public function equals(EnumBase $other, $strict = true)
    {
        if ($other instanceof EnumBase) {
            return $strict ?
                ($this->_value === $other->_value) :
                ($this->_value == $other->_value);
        }
        return false;
    }


    private static function getMetaData($type = 1)
    {
        if (self::$_cache == null) {
            self::$_cache = [];
        }
        $name = get_called_class();
        if (!array_key_exists($name, self::$_cache)) {
            $class = new \ReflectionClass($name);
            $consts = $class->getConstants();
            $default = $consts['__default'];
            unset($consts['__default']);
            self::$_cache[$name] = [$default, $consts];
        }
        return self::$_cache[$name][$type];
    }


    public static function getValues()
    {
        return array_values(self::getMetaData());
    }


    public static function getNames()
    {
        return array_keys(self::getMetaData());
    }


    public static function getName($value)
    {
        $consts = self::getMetaData();
        foreach ($consts as $name => $val) {
            if ($val == $value) {
                return $name;
            }
        }
        return null;
    }


    public static function isValidName($name, $strict = false)
    {
        $consts = self::getMetaData();
        if ($strict) {
            return array_key_exists($name, $consts);
        }
        return in_array(strtolower($name), array_map('strtolower', array_keys($consts)));
    }


    public static function isValidValue($value)
    {
        $values = array_values(self::getMetaData());
        return in_array($value, $values, $strict = true);
    }

}