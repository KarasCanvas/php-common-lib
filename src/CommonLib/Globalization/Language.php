<?php
/**
 * @author Raven <karascanvas@qq.com>
 */
namespace CommonLib\Globalization;

use CommonLib\Globalization\LanguageSupport\IncludingLanguageLoader;
use CommonLib\Globalization\LanguageSupport\LanguageLoaderInterface;
use CommonLib\Globalization\LanguageSupport\LanguagePackage;

abstract class Language
{
    private function __construct() { }

    private static $_lp = null;

    /**
     * @return \CommonLib\Globalization\LanguageSupport\LanguagePackage
     */
    public static function package()
    {
        if (!self::$_lp) {
            $loader = new IncludingLanguageLoader(__DIR__ . DIRECTORY_SEPARATOR . 'Languages');
            self::$_lp = new LanguagePackage($loader);
        }
        return self::$_lp;
    }


    public static function setCulture($culture)
    {
        self::package()->setCulture($culture);
    }


    public static function setLoader(LanguageLoaderInterface $loader)
    {
        self::package()->setCulture($loader);
    }


    public static function get($name)
    {
        if (strlen($name) < 1) {
            return null;
        }
        $value = self::package()->translate($name);
        if ($value == null) {
            $value = $name;
        }
        if (func_num_args() < 2) {
            return $value;
        }
        $args = func_get_args();
        array_shift($args);
        return vsprintf($value, $args);
    }

}