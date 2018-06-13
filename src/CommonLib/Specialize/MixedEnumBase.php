<?php
/**
 * @author Raven <karascanvas@qq.com>
 */
namespace CommonLib\Specialize;

abstract class MixedEnumBase
{
    protected static $data = null;


    private function __construct() { }


    private static function get($name, array &$info = null)
    {
        if ($name == null) {
            return false;
        }
        $keys = explode('.', strtoupper($name));
        if (count($keys) != 2) {
            return false;
        }
        if (!isset(static::$data[$keys[0]])) {
            return false;
        }
        foreach (static::$data[$keys[0]] as $value => $item) {
            if ($item[0] === $keys[1]) {
                $info = array($value, $item[0], $item[1]);
                return true;
            }
        }
        return false;
    }


    public static function value($name)
    {
        if (static::get($name, $info)) {
            return $info[0];
        }
        return null;
    }


    public static function text($name)
    {
        if (static::get($name, $info)) {
            return $info[2];
        }
        return null;
    }


    public static function map($name)
    {
        $name = strtoupper($name);
        if (isset(static::$data[$name])) {
            $tmp = [];
            foreach (static::$data[$name] as $value => $item) {
                $tmp[$value] = $item[1];
            }
            return $tmp;
        }
        return null;
    }


    public static function data($name, $valueField = 'value', $textField = 'text')
    {
        $name = strtoupper($name);
        if (isset(static::$data[$name])) {
            $tmp = [];
            foreach (static::$data[$name] as $value => $item) {
                $tmp[] = [$valueField => $value, $textField => $item[1]];
            }
            return $tmp;
        }
        return null;
    }


    public static function raw($name)
    {
        $name = strtoupper($name);
        if (isset(static::$data[$name])) {
            return static::$data[$name];
        }
        return null;
    }

}