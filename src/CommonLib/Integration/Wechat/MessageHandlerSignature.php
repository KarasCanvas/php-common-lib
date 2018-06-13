<?php
/**
 * @author Raven <karascanvas@qq.com>
 */

namespace CommonLib\Integration\Wechat;

abstract class MessageHandlerSignature
{
    private function __construct() { }


    public static function generate($token, $timestamp, $nonce)
    {
        $data = array($token, $timestamp, $nonce);
        sort($data, SORT_STRING);
        return sha1(implode($data));
    }


    public static function validate($token, $timestamp, $nonce, $signature)
    {
        return static::generate($token, $timestamp, $nonce) == $signature;
    }


    public static function process($token, $timestamp, $nonce, $signature, $echostr)
    {
        if(static::validate($token, $timestamp, $nonce, $signature))
        {
            exit(strval($echostr));
        }
    }

}