<?php
/**
 * @author Raven <karascanvas@qq.com>
 */
namespace CommonLib\Globalization\LanguageSupport;

class LanguagePackage
{
    const CULTURE_INVARIANT = null;

    /**
     * @var LanguageLoaderInterface loader
     */
    protected $loader = null;
    protected $culture = null;
    protected $data = array();
    protected $cultures = null;


    function __construct(LanguageLoaderInterface $loader, $culture = null)
    {
        if ($loader == null) {
            throw new \InvalidArgumentException('Argument "loader" can not be null.');
        }
        $this->loader = $loader;
        $this->setCulture($culture);
    }


    public function getCulture()
    {
        return $this->culture;
    }


    public function setCulture($culture)
    {
        $this->culture = (string)$culture;
        $this->cultures = $this->getFallbackNames($this->culture);
    }


    public function setLoader(LanguageLoaderInterface $loader)
    {
        if ($loader != null && $loader instanceof LanguageLoaderInterface) {
            $this->loader = $loader;
            $this->data = array();
        }
    }


    protected function getFallbackNames($culture)
    {
        if ($culture == null) {
            return [self::CULTURE_INVARIANT];
        }
        $array = array();
        $parts = explode('-', str_replace('_', '-', $culture));
        for ($n = count($parts); $n > 0; $n--) {
            $array[] = implode('_', array_slice($parts, 0, $n));
        }
        $array[] = self::CULTURE_INVARIANT;
        return $array;
    }


    public function translate($name)
    {
        foreach ($this->cultures as $culture) {
            if (!isset($this->data[$culture])) {
                $this->data[$culture] = $this->loader->load($culture);
                if (!is_array($this->data[$culture])) {
                    $this->data[$culture] = array();
                }
            }
            if (isset($this->data[$culture][$name])) {
                return $this->data[$culture][$name];
            }
        }
        return null;
    }


    public function format($name)
    {
        if (strlen($name) < 1) {
            return null;
        }
        $value = $this->translate($name);
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