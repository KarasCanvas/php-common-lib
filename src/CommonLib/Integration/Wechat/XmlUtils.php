<?php
/**
 * @author Raven <karascanvas@qq.com>
 */
namespace CommonLib\Integration\Wechat;

class XmlUtils
{
    private function __construct() { }


    public static function deserialize($xml)
    {
        $node = simplexml_load_string($xml, 'SimpleXmlElement', LIBXML_NOCDATA);
        if ($node === false) {
            return false;
        }
        return static::convertToArray($node);
    }


    public static function serialize(array $array)
    {
        $xml = new \SimpleXMLElement('<xml/>');
        static::appendChildren($xml, (array)$array);
        return $xml->asXML();
    }


    private static function convertToArray(\SimpleXMLElement $node)
    {
        $array = (array)$node;
        if (count($array) == 1 && isset($array['item']) && static::isListArray($array['item'])) {
            $array = $array['item'];
        }
        foreach ($array as $key => &$value) {
            if ($value instanceof \SimpleXMLElement) {
                $value = static::convertToArray($value);
            }
        }
        return $array;
    }


    private static function appendChildren(\SimpleXMLElement $node, array $array)
    {
        if (static::isListArray($array)) {
            foreach ($array as $item) {
                if (is_array($item)) {
                    static::appendChildren($node->addChild('item'), $item);
                } else {
                    $node->addChild('item', $item);
                }
            }
        } else {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    static::appendChildren($node->addChild($key), $value);
                } else {
                    $node->addChild($key, $value);
                }
            }
        }
    }


    private static function isListArray($value)
    {
        if (!is_array($value) || empty($value)) {
            return false;
        }
        return (array_values($value) === $value);
    }

}