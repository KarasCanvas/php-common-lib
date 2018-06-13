<?php
/**
 * @author Raven <karascanvas@qq.com>
 */
namespace CommonLib\IO;

use CommonLib\Globalization\Language;

abstract class PathUtils
{
    private function __construct() { }


    const SLASH = '/';
    const BACK_SLASH = '\\';

    /**
     * normalizeSeparator
     * @author Raven
     * @param string $path
     * @param string $separator
     * @return string | mixed
     */
    public static function normalizeSeparator($path, $separator = DIRECTORY_SEPARATOR)
    {
        if ($separator != self::SLASH && $separator != self::BACK_SLASH) {
            throw new \InvalidArgumentException(Language::get('SEPARATOR_MUST_BE_SLASH_OR_BACK_SLASH'));
        }
        $search = ($separator == self::SLASH) ? self::BACK_SLASH : self::SLASH;
        return str_replace($search, $separator, $path);
    }


    /**
     * combine
     * @author Raven
     * @param array $paths
     * @param string $separator
     * @return null|string
     */
    public static function combine(array $paths, $separator = DIRECTORY_SEPARATOR)
    {
        if (empty($paths)) {
            return null;
        }
        $parts = array();
        $first = true;
        foreach (((array)$paths) as $path) {
            $path = strval($path);
            if ($path == '') {
                continue;
            }
            $path = static::normalizeSeparator($path, $separator);
            $items = explode($separator, $path);
            $count = count($items);
            for ($i = 0; $i < $count; $i++) {
                $item = $items[$i];
                switch ($item) {
                    case '' :
                        if ($i == 0) {
                            $parts = [''];
                        }
                        break;
                    case '.' :
                        if ($first && $i == 0) {
                            $parts[] = $item;
                        }
                        break;
                    case '..' :
                        if ($first && $i == 0) {
                            $parts[] = $item;
                        } else {
                            $parts = array_slice($parts, 0, (count($parts) - 1));
                        }
                        break;
                    default:
                        $parts[] = $item;
                        break;
                }
            }
            $first = false;
        }
        return implode($separator, $parts);
    }

}