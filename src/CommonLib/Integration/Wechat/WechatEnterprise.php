<?php
namespace CommonLib\Integration\Wechat;
/**
 * @summary 微信企业号接口 <http://qydev.weixin.qq.com/>
 * @author Raven <karascanvas@qq.com>
 * @date 2015-06-18
 */
class WechatEnterprise
{
    const OAUTH_SCOPE_BASE = 'snsapi_base';
    const URL_OAUTH = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    const URL_BASE = 'https://qyapi.weixin.qq.com/';
    const API_GET_TOKEN = 'cgi-bin/gettoken';
    const API_GET_USERINFO = 'cgi-bin/user/getuserinfo';

    private function __construct() { }


    protected static function invoke($api, $params, $data = null)
    {
        return ApiClient::invoke(static::URL_BASE . $api, $params, $data);
    }


    public static function getToken($corpid, $secret)
    {
        $params = array(
            'corpid'     => $corpid,
            'corpsecret' => $secret,
        );
        return static::invoke(self::API_GET_TOKEN, $params);
    }


    public static function getOauthUserInfo($access_token, $code, $agentid)
    {
        $params = array(
            'access_token' => $access_token,
            'code'         => $code,
            'agentid'      => $agentid
        );
        return static::invoke(self::API_GET_USERINFO, $params);
    }


    public static function getAuthorizeUrl($appid, $redirect_uri, $state = null)
    {
        $params = array(
            'appid'         => $appid,
            'redirect_uri'  => $redirect_uri,
            'response_type' => 'code',
            'scope'         => self::OAUTH_SCOPE_BASE,
            'state'         => $state
        );
        return static::URL_OAUTH . '?' . http_build_query($params) . '#wechat_redirect';
    }


    public static function authorizeRedirect($appid, $redirect_uri, $state = null)
    {
        $url = static::getAuthorizeUrl($appid, $redirect_uri, $state);
        header("Cache-Control: no-cache, must-revalidate");
        header("Pramga: no-cache");
        header('Location: ' . $url);
        exit();
    }

}