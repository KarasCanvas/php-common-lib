<?php
/**
 * @author Raven <karascanvas@qq.com>
 */
namespace CommonLib\Collections;

abstract class ArrayUtils
{
    private function __construct() { }


    public static function isList($value, $allowEmpty = false)
    {
        if (!is_array($value)) {
            return false;
        }
        if (!$value) {
            return $allowEmpty;
        }
        return (array_values($value) === $value);
    }


    public static function merge(array $one, array $other, $preserveNumericKeys = false)
    {
        foreach ($other as $key => $value) {
            if (array_key_exists($key, $one)) {
                if (is_int($key) && !$preserveNumericKeys) {
                    $one[] = $value;
                } elseif (is_array($value) && is_array($one[$key])) {
                    $one[$key] = static::merge($one[$key], $value, $preserveNumericKeys);
                } else {
                    $one[$key] = $value;
                }
            } else {
                $one[$key] = $value;
            }
        }
        return $one;
    }


    /**
     * 将数组转换树的表示形式
     * @param array $list
     * @param string $idKey
     * @param string $parentKey
     * @param string $childrenKey
     * @param int $rootId
     * @return array
     */
    public static function arrayToTree(array $list, $idKey = 'id', $parentKey = 'parent_id', $childrenKey = 'children', $rootId = 0)
    {
        $list = (array)$list;
        $root = array($idKey => $rootId, $childrenKey => array());
        static::arrayToTreeAppendChildren($root, $list, $idKey, $parentKey, $childrenKey);
        return $root;
    }


    protected static function arrayToTreeAppendChildren(array &$node, array &$list, $idKey = 'id', $parentKey = 'parent_id', $childrenKey = 'children')
    {
        $children = array();
        foreach ($list as $item) {
            if (isset($item[$parentKey]) && $item[$parentKey] == $node[$idKey]) {
                static::arrayToTreeAppendChildren($item, $list, $idKey, $parentKey, $childrenKey);
                $children[] = $item;
            }
        }
        if (count($children) > 0) {
            $node[$childrenKey] = $children;
        }
    }




}