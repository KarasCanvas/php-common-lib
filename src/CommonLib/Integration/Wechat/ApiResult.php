<?php
namespace CommonLib\Integration\Wechat;
/**
 * @summary 微信接口调用结果
 * @author Raven <karascanvas@qq.com>
 */
class ApiResult
{
    protected $code;
    protected $message;
    protected $data;


    private function __construct($code = 0, $message = null, $data = null)
    {
        $this->code = intval($code);
        $this->message = $message;
        $this->data = $data;
    }


    public function success()
    {
        return ($this->code == 0);
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getData()
    {
        return $this->data;
    }

    public function hasData()
    {
        return $this->data !== null;
    }

    public function getField($name, $default = null)
    {
        if (is_array($this->data) && isset($this->data[$name])) {
            return $this->data[$name];
        }
        return $default;
    }

    public function tryGetField($name, &$value)
    {
        if (is_array($this->data) && isset($this->data[$name])) {
            $value = $this->data[$name];
            return true;
        }
        return false;
    }


    public static function successResult($message = null)
    {
        return new static(0, $message);
    }

    public static function codeResult($code, $message = null)
    {
        return new static($code, $message);
    }

    public static function dataResult($data, $message = null)
    {
        return new static(0, $message, $data);
    }

}