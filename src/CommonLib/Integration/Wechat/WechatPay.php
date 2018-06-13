<?php
namespace CommonLib\Integration\Wechat;
/**
 * @summary 微信公众号支付接口 <http://dwz.cn/RhGbT>
 * @author Raven <karascanvas@qq.com>
 * @date 2015-06-19
 */
class WechatPay
{
    const API_URL = 'https://api.mch.weixin.qq.com/';
    const CUR_TYPE_CNY = 'CNY';
    const SIGN_TYPE_MD5 = 'MD5';
    const RESULT_SUCCESS = 'SUCCESS';
    const RESULT_FAIL = 'FAIL';
    const BILL_TYPE_ALL = 'ALL';
    const BILL_TYPE_SUCCESS = 'SUCCESS';
    const BILL_TYPE_REFUND = 'REFUND';
    const BILL_TYPE_REVOKED = 'REVOKED';
    const TRADE_TYPE_JSAPI = 'JSAPI';
    const TRADE_TYPE_NATIVE = 'NATIVE';
    const TRADE_TYPE_APP = 'APP';


    protected $config;


    public function __construct(array $config)
    {
        if (empty($config)) {
            throw new \InvalidArgumentException('Argument $config can not be empty.');
        }
        $defaults = array(
            'appid'       => null,
            'mch_id'      => null,
            'device_info' => null,
            'sign_type'   => 'MD5',
            'md5_key'     => null,
            'notify_url'  => null,
        );
        $this->config = array_merge($defaults, $config);
    }


    /**
     * 统一下单
     * @param array $params
     * @return array | false
     */
    public function unifiedOrder(array $params)
    {
        $defaults = array(
            'trade_type' => static::TRADE_TYPE_JSAPI,
            'notify_url' => $this->config['notify_url']
        );
        $params = array_merge($defaults, $params);
        return $this->invoke('pay/unifiedorder', $params, true);
    }


    /**
     * 查询订单
     * @param string $transaction_id 微信的订单号，优先使用
     * @param string $out_trade_no 商户订单号
     * @return array | false
     */
    public function orderQuery($transaction_id, $out_trade_no = null)
    {
        if (!empty($transaction_id)) {
            $params = array('transaction_id' => $transaction_id);
        } else {
            $params = array('out_trade_no' => $out_trade_no);
        }
        return $this->invoke('pay/orderquery', $params);
    }


    /**
     * 关闭订单
     * @param string $transaction_id 微信的订单号，优先使用
     * @param string $out_trade_no 商户订单号
     * @return array | false
     */
    public function closeOrder($transaction_id, $out_trade_no = null)
    {
        if (!empty($transaction_id)) {
            $params = array('transaction_id' => $transaction_id);
        } else {
            $params = array('out_trade_no' => $out_trade_no);
        }
        return $this->invoke('pay/closeorder', $params);
    }


    /**
     * 申请退款
     * @param string $transaction_id
     * @param string $out_trade_no
     * @param string $out_refund_no
     * @param integer $total_fee
     * @param integer $refund_fee
     * @return array|bool
     */
    public function refund($transaction_id, $out_trade_no = null, $out_refund_no, $total_fee, $refund_fee)
    {
        $params = array(
            'out_refund_no'   => $out_refund_no,
            'total_fee'       => intval($total_fee),
            'refund_fee'      => intval($refund_fee),
            'refund_fee_type' => static::CUR_TYPE_CNY,
            'op_user_id'      => $this->config['mch_id']
        );
        if (!empty($transaction_id)) {
            $params['transaction_id'] = $transaction_id;
        } else {
            $params['out_trade_no'] = $out_trade_no;
        }
        return $this->invoke('secapi/pay/refund', $params, true);
    }


    /**
     * 查询退款
     * @param $refund_id
     * @param $out_refund_no
     * @param $transaction_id
     * @param $out_trade_no
     * @return array|bool
     */
    public function refundQuery($refund_id, $out_refund_no = null, $transaction_id = null, $out_trade_no = null)
    {
        if (!empty($refund_id)) {
            $params = array('refund_id' => $refund_id);
        } elseif (!empty($out_refund_no)) {
            $params = array('out_refund_no' => $out_refund_no);
        } elseif (!empty($transaction_id)) {
            $params = array('transaction_id' => $transaction_id);
        } else {
            $params = array('out_trade_no' => $out_trade_no);
        }
        return $this->invoke('pay/refundquery', $params, true);
    }


    /**
     * 下载对账单
     * @param string $bill_date 下载对账单的日期，格式：20140603
     * @param string $bill_type [ALL，SUCCESS，REFUND，REVOKED]
     * @return array|bool|mixed
     */
    public function downloadBill($bill_date, $bill_type = 'ALL')
    {
        $params = array(
            'bill_date' => $bill_date,
            'bill_type' => $bill_type
        );
        return $this->invoke('pay/downloadbill', $params, true, true);
    }


    /**
     * 测速上报
     * @param array $params
     * @return array | false
     */
    public function report(array $params)
    {
        $defaults = array(
            'interface_url' => null,
            'execute_time_' => null,
            'return_code'   => null,
            'result_code'   => null,
            'user_ip'       => '127.0.0.1',
            'time'          => date('Ymdhis'),
        );
        $params = array_merge($defaults, $params);
        return $this->invoke('payitil/report', $params, true);
    }


    /**
     * 转换短链接
     * @param string $url
     * @return array | false
     */
    public function shorturl($url)
    {
        $params = array('long_url' => $url);
        return $this->invoke('tools/shorturl', $params, true);
    }


    /**
     * 处理异步通知
     * @param null $xml
     * @return array
     */
    public function processNotification($xml = null)
    {
        if ($xml === null) {
            $xml = file_get_contents('php://input', 'r');
        }
        $data = XmlUtils::deserialize($xml);
        if ($data === false) {
            return array('success' => false, 'message' => 'Invalid xml format.');
        }
        if (!$this->verifySign($data)) {
            return array('success' => false, 'message' => 'Invalid sign.');
        }
        return array('success' => true, 'data' => $data);
    }


    /**
     * 异步通知响应
     * @param bool $success
     * @param null $message
     */
    public function respond($success = true, $message = null)
    {
        $data = array(
            'return_code' => ($success ? static::RESULT_SUCCESS : static::RESULT_FAIL)
        );
        if (!empty($message)) {
            $data['return_msg'] = $message;
        }
        header('Content-Type:text/xml;charset=utf-8');
        exit(XmlUtils::serialize($data));
    }


    /**
     * 获取网页端支付参数
     * @param $prepay_id
     * @return array
     */
    public function getJsPayParameters($prepay_id)
    {
        $params = array(
            'appId'     => $this->config['appid'],
            'timeStamp' => time(),
            'nonceStr'  => $this->generateNonceString(),
            'package'   => 'prepay_id=' . $prepay_id,
            'signType'  => $this->config['sign_type'],
        );
        $params['paySign'] = $this->generateSign($params);
        return $params;
    }


    /**
     * 通过错误代码获取错误信息
     * @param string $code
     * @return string
     */
    public function getErrorMessage($code)
    {
        $code = strtoupper($code);
        if (isset(static::$_error_map[$code])) {
            return static::$_error_map[$code];
        }
        return null;
    }


    protected function getConfig($key, $default = null)
    {
        if (isset($this->config[$key])) {
            return $this->config[$key];
        }
        return $default;
    }


    protected function generateNonceString()
    {
        return strtoupper(md5(uniqid(null, true)));
    }


    protected function filterParameters(array $params)
    {
        if (isset($params['sign'])) {
            unset($params['sign']);
        }
        $temp = array();
        foreach ($params as $key => $value) {
            if (!is_string($value) || $value != '') {
                $temp[$key] = $value;
            }
        }
        return $temp;
    }


    protected function buildSignString(array $params)
    {
        $params = $this->filterParameters($params);
        ksort($params);
        reset($params);
        $temp = array();
        foreach ($params as $key => $value) {
            $temp[] = $key . '=' . $value;
        }
        return implode('&', $temp);
    }


    protected function generateSign(array $params)
    {
        $prestr = $this->buildSignString($params);
        return $this->generateSignMD5($prestr, $this->config['md5_key']);
    }


    protected function generateSignMD5($prestr, $key)
    {
        return strtoupper(md5($prestr . '&key=' . $key));
    }


    protected function verifySign(array $params)
    {
        if (isset($params['sign'])) {
            return ($params['sign'] === $this->generateSign($params));
        }
        return false;
    }


    protected function invoke($path, array $params, $deviceInfoRequired = false, $raw = false)
    {
        $params['appid'] = $this->config['appid'];
        $params['mch_id'] = $this->config['mch_id'];
        if ($deviceInfoRequired) {
            $params['device_info'] = $this->config['device_info'];
        }
        $params['nonce_str'] = $this->generateNonceString();
        $params['sign'] = $this->generateSign($params);
        $response = $this->httpPost(static::API_URL . $path, XmlUtils::serialize($params));
        if ($response === false) {
            return false;
        }
        if ($raw && strtolower(substr($response, 0, 5)) === '<xml>') {
            return $response;
        }
        return XmlUtils::deserialize($response);
    }


    protected function httpPost($url, $data)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $url,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_HEADER         => false,
            CURLOPT_AUTOREFERER    => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }


    protected function httpsPost($url, $data, $cacert_path)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $url,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_HEADER         => false,
            CURLOPT_AUTOREFERER    => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO         => $cacert_path
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }


    private static $_error_map = array(
        'NOAUTH'                => '商户无此接口权限',
        'NOTENOUGH'             => '余额不足',
        'ORDERPAID'             => '商户订单已支付',
        'ORDERCLOSED'           => '订单已关闭',
        'SYSTEMERROR'           => '系统错误',
        'APPID_NOT_EXIST'       => 'APPID不存在',
        'MCHID_NOT_EXIST'       => 'MCHID不存在',
        'APPID_MCHID_NOT_MATCH' => 'appid和mch_id不匹配',
        'LACK_PARAMS'           => '缺少参数',
        'OUT_TRADE_NO_USED'     => '商户订单号重复',
        'SIGNERROR'             => '签名错误',
        'XML_FORMAT_ERROR'      => 'XML格式错误',
        'REQUIRE_POST_METHOD'   => '请使用post方法',
        'POST_DATA_EMPTY'       => 'post数据为空',
        'NOT_UTF8'              => '编码格式错误',
        'ORDERNOTEXIST'         => '此交易订单号不存在',
        'INVALID_TRANSACTIONID' => 'transaction_id无效',
        'PARAM_ERROR'           => '参数错误',
    );

}