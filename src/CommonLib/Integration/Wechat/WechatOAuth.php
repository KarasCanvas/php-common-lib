<?php
namespace CommonLib\Integration\Wechat;
/**
 * @summary 微信OAuth <http://dwz.cn/Domlv>
 * @author Raven <karascanvas@qq.com>
 * @date 2015-06-10
 */
class WechatOAuth
{
    const SCOPE_BASE = 'snsapi_base';
    const SCOPE_USERINFO = 'snsapi_userinfo';

    const URL_AUTHORIZE = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    const URL_BASE = 'https://api.weixin.qq.com/sns';
    const URL_ACCESS_TOKEN = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    const URL_REFRESH_TOKEN = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';
    const URL_VERIFY_TOKEN = 'https://api.weixin.qq.com/sns/auth';
    const URL_USERINFO = 'https://api.weixin.qq.com/sns/userinfo';

    private function __construct() { }


    protected static function httpGet($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }


    protected static function invoke($url, array $params = array())
    {
        $response = static::httpGet($url . '?' . http_build_query($params));
        if ($response === false) {
            return array('success' => false, 'message' => 'Request Error.');
        }
        $data = json_decode($response, true);
        if (!is_array($data)) {
            return array('success' => false, 'message' => 'Invalid Response.');
        }
        if (isset($data['errcode'])) {
            if ($data['errcode'] == 0) {
                return array('success' => true);
            }
            return array('success' => false, 'code' => $data['errcode'], 'message' => $data['errmsg']);
        }
        return array('success' => true, 'data' => $data);
    }


    public static function stateEncode($data, $compress = true)
    {
        if ($compress) {
            return bin2hex(gzdeflate(serialize($data)));
        }
        return bin2hex(serialize($data));
    }


    public static function stateDecode($data, $compress = true)
    {
        if ($compress) {
            return unserialize(gzinflate(hex2bin($data)));
        }
        return unserialize(hex2bin($data));
    }


    public static function getAuthorizeUrl($appid, $redirect_uri, $state = null, $scope = self::SCOPE_BASE)
    {
        if ($scope != self::SCOPE_BASE && $scope != self::SCOPE_USERINFO) {
            $scope = self::SCOPE_BASE;
        }
        $params = array(
            'appid'         => $appid,
            'redirect_uri'  => $redirect_uri,
            'response_type' => 'code',
            'scope'         => $scope,
            'state'         => $state
        );
        return static::URL_AUTHORIZE . '?' . http_build_query($params) . '#wechat_redirect';
    }


    public static function authorizeRedirect($appid, $redirect_uri, $state = null, $scope = self::SCOPE_BASE)
    {
        $url = static::getAuthorizeUrl($appid, $redirect_uri, $state, $scope);
        header("Cache-Control: no-cache, must-revalidate");
        header("Pramga: no-cache");
        header('Location: ' . $url);
        exit();
    }


    public static function getAccessToken($appid, $app_secret, $code)
    {
        $params = array(
            'appid'      => $appid,
            'secret'     => $app_secret,
            'code'       => $code,
            'grant_type' => 'authorization_code'
        );
        return static::invoke(static::URL_ACCESS_TOKEN, $params);
    }


    public static function refreshAccessToken($appid, $access_token)
    {
        $params = array(
            'appid'          => $appid,
            'grant_type'     => 'refresh_token',
            'refresh_token ' => $access_token
        );
        return static::invoke(static::URL_REFRESH_TOKEN, $params);
    }


    public static function verifyAccessToken($access_token, $openid)
    {
        $params = array(
            'access_token' => $access_token,
            'openid'       => $openid
        );
        return static::invoke(static::URL_VERIFY_TOKEN, $params);
    }


    public static function getUserInfo($access_token, $openid, $lang = 'zh_CN')
    {
        if (!in_array(array('zh_CN', 'zh_TW', 'en'), $lang)) {
            $lang = 'zh_CN';
        }
        $params = array(
            'access_token ' => $access_token,
            'openid'        => $openid,
            'lang '         => $lang
        );
        return static::invoke(static::URL_USERINFO, $params);
    }

}