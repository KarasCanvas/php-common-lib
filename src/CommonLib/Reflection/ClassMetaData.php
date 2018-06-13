<?php
/**
 * @author Raven <karascanvas@qq.com>
 */
namespace CommonLib\Reflection;

class ClassMetaData
{
    private $_class = null;

    public function __construct($class)
    {
        $this->_class = new \ReflectionClass($class);
    }


    public function getDocComment()
    {
        return $this->_class->getDocComment();
    }


    public function getTags(array $tags = null)
    {
        $comment = $this->_class->getDocComment();
        return $this->extractDocTags($comment, $tags);
    }


    public function getMethodsTags(array $tags = null, $inherited = false, $filter = null)
    {
        $methods = $filter ? $this->_class->getMethods($filter) : $this->_class->getMethods();
        if (!$inherited) {
            $methods = array_filter($methods, function ($method) {
                return ($this->_class->getName() == $method->class);
            });
        }
        $data = array();
        foreach ($methods as $method) {
            $data[$method->getName()] = $this->extractDocTags($method->getDocComment(), $tags);
        }
        return $data;
    }


    public function getMethodsTagsFiltered(array $tags = null, $filterCallback = null)
    {
        $methods = $this->_class->getMethods();
        if (is_callable($filterCallback)) {
            $methods = array_filter($methods, function ($method) use ($filterCallback) {
                return call_user_func_array($filterCallback, array($method, $this->_class));
            });
        }
        $data = array();
        foreach ($methods as $method) {
            $data[$method->getName()] = $this->extractDocTags($method->getDocComment(), $tags);
        }
        return $data;
    }


    protected function extractDocTags($comment, array $tags = null)
    {
        $data = $this->extractAllDocTags($comment);
        if ($tags) {
            $temp = array();
            foreach ($tags as $tag) {
                $temp[$tag] = isset($data[$tag]) ? $data[$tag] : null;
            }
            return $temp;
        }
        return $data;
    }


    protected function extractAllDocTags($comment)
    {
        $data = array();
        if (preg_match_all('/@([\w-]+)(\s+([^\r]+))*?\r/i', $comment, $matches)) {
            foreach ($matches[1] as $key => $tag) {
                $data[$tag] = $matches[3][$key];
            }
        }
        return $data;
    }


}