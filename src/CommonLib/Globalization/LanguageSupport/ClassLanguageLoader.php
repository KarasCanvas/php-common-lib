<?php
/**
 * @author Raven <karascanvas@qq.com>
 */
namespace CommonLib\Globalization\LanguageSupport;

class ClassLanguageLoader implements LanguageLoaderInterface
{
    protected $namespace = null;
    protected $prefix = null;
    protected $suffix = null;

    public function __construct($namespace, $prefix = 'Language_', $suffix = null)
    {
        if ($namespace == null) {
            throw new \InvalidArgumentException('Argument "$namespace" can not be null.');
        }
        $this->namespace = $namespace;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }


    protected function getClassName($culture)
    {
        if ($culture == null) {
            $culture = 'Default';
        }
        return $this->namespace . '\\' . ($this->prefix . $culture . $this->suffix);
    }


    public function load($culture)
    {
        try {
            $class = new \ReflectionClass($this->getClassName($culture));
        } catch (\Exception $exception) {
            return null;
        }
        if (!$class->isAbstract() && $class->implementsInterface(LanguageDataInterface::class)) {
            $instance = $class->newInstance();
            return $instance->data();
        }
        return null;
    }

}