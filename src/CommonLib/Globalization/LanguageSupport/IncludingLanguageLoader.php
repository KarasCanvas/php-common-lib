<?php
/**
 * @author Raven <karascanvas@qq.com>
 */
namespace CommonLib\Globalization\LanguageSupport;

class IncludingLanguageLoader implements LanguageLoaderInterface
{
    protected $directory = null;
    protected $prefix = null;
    protected $suffix = null;


    public function __construct($directory, $prefix = null, $suffix = '.lang')
    {
        if ($directory == null) {
            throw new \InvalidArgumentException('Argument "$directory" can not be null.');
        }
        $this->directory = rtrim($directory, '/\\');
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }


    protected function getFileName($culture)
    {
        if ($culture == null) {
            $culture = 'default';
        }
        return $this->directory . DIRECTORY_SEPARATOR . ($this->prefix . $culture . $this->suffix) . '.php';
    }


    public function load($culture)
    {
        return include $this->getFileName($culture);
    }

}