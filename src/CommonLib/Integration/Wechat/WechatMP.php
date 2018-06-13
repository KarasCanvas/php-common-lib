<?php
namespace CommonLib\Integration\Wechat;
/**
 * @summary 微信公众平台接口 <http://mp.weixin.qq.com/wiki/>
 * @author Raven <karascanvas@qq.com>
 * @date 2015-06-19
 */
class WechatMP
{
    const URL_BASE = 'https://api.weixin.qq.com/';
    const GRANT_TYPE_CLIENT_CREDENTIAL = 'client_credential';

    private function __construct() { }


    public static function invoke($path, $params, $data = null)
    {
        return ApiClient::invoke(static::URL_BASE . $path, $params, $data);
    }


    public static function getToken($appid, $secret, $grantType = self::GRANT_TYPE_CLIENT_CREDENTIAL)
    {
        $params = array(
            'grant_type' => $grantType,
            'appid'      => $appid,
            'secret'     => $secret,
        );
        return static::invoke('cgi-bin/token', $params);
    }


    public static function getCallbackIP($accessToken)
    {
        $params = array('access_token' => $accessToken);
        return static::invoke('cgi-bin/getcallbackip', $params);
    }


    public static function sendTemplateMessage($accessToken, $touser, $templateId, $url = null, $data = array(),  $topcolor = '#FF0000')
    {
        $params = array('access_token' => $accessToken);
        $body = array(
            'touser'      => $touser,
            'template_id' => $templateId,
            'url'         => $url,
            'topcolor'    => $topcolor,
            'data'        => $data
        );
        return static::invoke('cgi-bin/message/template/send', $params, $body);
    }


}