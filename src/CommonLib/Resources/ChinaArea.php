<?php
/**
 * @author Raven <karascanvas@qq.com>
 */
namespace CommonLib\Resources;

abstract class ChinaArea
{
    protected static $rawArray = null;

    private function __construct() { }


    public static function getResourceFileName()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'ChinaArea.xml';
    }


    public static function getSimpleXml()
    {
        return simplexml_load_file(self::getResourceFileName(), 'SimpleXmlElement', LIBXML_NOCDATA);
    }


    public static function getRawArray()
    {
        if (self::$rawArray == null) {
            self::$rawArray = self::convertToArray(self::getSimpleXml());
        }
        return self::$rawArray;
    }


    protected static function convertToArray(\SimpleXmlElement $node)
    {
        $data = self::getAttributeArray($node);
        foreach ($node->xpath('province') as $provinceNode) {
            $province = self::getAttributeArray($provinceNode);
            foreach ($provinceNode->xpath('city') as $cityNode) {
                $city = self::getAttributeArray($cityNode);
                foreach ($cityNode->xpath('area') as $areaNode) {
                    $area = self::getAttributeArray($areaNode);
                    $city['areas'][] = $area;
                }
                $province['cities'][] = $city;
            }
            $data['provinces'][] = $province;
        }
        return $data;
    }


    protected static function getAttributeArray(\SimpleXmlElement $node)
    {
        $data = array();
        foreach ($node->attributes() as $key => $value) {
            $data[$key] = (string)$value;
        }
        return $data;
    }


}