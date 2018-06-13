<?php
namespace CommonLib\Integration\Wechat;
/**
 * @summary 微信接口客户端
 * @author Raven <karascanvas@qq.com>
 */
class ApiClient
{
    private function __construct() { }

    /**
     * invoke
     * @param string $url
     * @param array $params
     * @param mixed $data
     * @return ApiResult
     */
    public static function invoke($url, array $params = array(), $data = null)
    {
        $url = $url . '?' . http_build_query($params);
        $response = ($data === null) ? static::httpGet($url) : static::httpPost($url, $data);
        return static::parseResult($response);
    }


    public static function upload($url, $file, $field = 'media')
    {
        $file = realpath($file);
        $h = curl_init($url);
        curl_setopt($h, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($h, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($h, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($h, CURLOPT_HEADER, false);
        curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($h, CURLOPT_POST, true);
        curl_setopt($h, CURLOPT_POSTFIELDS, array($field => curl_file_create($file)));
        $response = curl_exec($h);
        curl_close($h);
        return static::parseResult($response);
    }


    public static function download($url)
    {
        $h = static::initCurl($url);
        curl_setopt($h, CURLOPT_HEADER, true);
        $response = curl_exec($h);
        $errno = curl_error($h);
        if ($errno) {
            return ApiResult::codeResult($errno, curl_error($h));
        }
        $info = curl_getinfo($h);
        curl_close($h);
        $header = substr($response, 0, $info['header_size']);
        $header = static::parseHeader($header);
        if (!isset($header['content-disposition'])) {
            return static::parseResult($response);
        }
        $data = array(
            'type'     => $info['content_type'],
            'filename' => static::extractFileName($header['content-disposition']),
            'content'  => substr($response, $info['header_size']),
        );
        return ApiResult::dataResult($data);
    }


    private static function extractFileName($str)
    {
        $index = strpos(strtolower($str), 'filename=');
        if ($index) {
            return trim(substr($str, $index + 9), ' \t\n\r\0\x0B"\'');
        }
        return false;
    }


    protected static function parseHeader($str)
    {
        $header = array();
        foreach (explode(PHP_EOL, $str) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $index = strpos($line, ':');
            if ($index === false) {
                $header['_'] = $line;
            } else {
                $header[strtolower(substr($line, 0, $index))] = trim(substr($line, $index + 1));
            }
        }
        return $header;
    }


    protected static function httpGet($url)
    {
        $h = static::initCurl($url);
        $response = curl_exec($h);
        curl_close($h);
        return $response;
    }


    protected static function httpPost($url, $data = null)
    {
        $h = static::initCurl($url);
        if ($data === null) {
            curl_setopt($h, CURLOPT_POST, true);
        } else if (is_string($data)) {
            curl_setopt($h, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($h, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($h, CURLOPT_POSTFIELDS, json_encode($data));
        }
        $response = curl_exec($h);
        curl_close($h);
        return $response;
    }


    protected static function initCurl($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        return $ch;
    }


    protected static function parseResult($response)
    {
        if ($response === false) {
            return ApiResult::codeResult(-100, 'Request Error');
        }
        $data = json_decode($response, true);
        if (!is_array($data)) {
            return ApiResult::codeResult(-101, 'Invalid Response');
        }
        if (isset($data['errcode'])) {
            if ($data['errcode'] == 0) {
                return ApiResult::successResult($data['errmsg']);
            }
            return ApiResult::codeResult($data['errcode'], $data['errmsg']);
        }
        return ApiResult::dataResult($data);
    }

}
