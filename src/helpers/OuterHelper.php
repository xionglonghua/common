<?php

namespace xionglonghua\common\helpers;

use Yii;
use yii\helpers\ArrayHelper;
use xionglonghua\common\device\Mobile;

class OuterHelper extends \xionglonghua\curl\CurlHttp
{
    //私钥
    public $pkey = '';
    //公钥
    public $pubkey = '';
    public $hkey = '';

    public $aeskey = '';
    public $webhkey = '';

    const EMPTY_TOKEN_RET = 'H_OUTER_ARG_EMPTY';

    public static $debug = true;
    public static $category = 'sign.outerHelper';

    // 字符串拼接格式{METHOD}\n{URI}\n{POST_STR}\n{TIME}
    private static $template = "%s\n%s\n%s\n%s";
    // 加密的格式 {ACCESS_TOKEN}~{SIGN}~{TIME}
    private static $encodeTemplate = '%s~%s~%s';

    // hash算法，保证信息不被更改
    public static function getSign($method, $uri, $postArr, $dataArr)
    {
        $time = isset($dataArr['time']) ? $dataArr['time'] : date('Y-m-d H:i:s');
        $postStr = '';
        ksort($postArr);
        foreach ($postArr as $key => $val) {
            $postStr .= '&'.$key.'='.$val;
        }
        $postStr = trim($postStr, '&');
        $str = sprintf(self::$template, $method, $uri, $postStr, $time);
        $hkey = Yii::$app->outer_helper->hkey;
        return hash_hmac('sha1', $str, $hkey, false);
    }

    // 按加密规则进行解码
    public static function decode($access_token, &$dataArr = [])
    {
        if (!ctype_xdigit($access_token)) {
            self::$debug && Yii::info(['apptoken' => $access_token], self::$category);
            return 'H_OUTER_APP_ARG_ERR';
        }
        $crypted = hex2bin($access_token);
        //从配置中获取pKey
        $pKey = Yii::$app->outer_helper->pkey;
        $outPlain = '';
        for ($i = 0; $i < strlen($crypted); $i += 128) {
            $src = substr($crypted, $i, 128);
            $ret = openssl_private_decrypt($src, $out, $pKey);
            $outPlain .= $out;
        }
        $outPlain = trim($outPlain);
        $outList = explode('~', $outPlain);
        if (count($outList) != 3) {
            self::$debug && Yii::info($outList, self::$category);
            return 'H_OUTER_APP_ARG_ERR';
        }
        $dataArr = [
            'token' => $outList[0],
            'sign' => $outList[1],
            'time' => $outList[2],
        ];
        if (!$dataArr['token']) {
            self::$debug && Yii::info(['apptoken' => $dataArr['token']], self::$category);
            return self::EMPTY_TOKEN_RET;
        }
        return false;
    }

    // 按加密规则加密
    public static function encode($access_token, $sign, $time)
    {
        $str = sprintf(self::$encodeTemplate, $access_token, $sign, $time);
        $result = '';
        foreach (str_split($str, 117) as $chunk) {
            $res = null;
            openssl_public_encrypt($chunk, $res, Yii::$app->outer_helper->pubkey, OPENSSL_PKCS1_PADDING);
            $result .= $res;
        }
        return bin2hex($result);
    }

    public function httpExec($action = '/', $params = [])
    {
        //由于请求的params的value应该是被编码过的，不应该含有\r,\r\n在接收端会变化导致签名错误
        foreach ($params as $key => &$value) {
            $value instanceof \CURLFile || $value = str_replace("\r", "\n", $value);
        }
        $method = $this->getMethodStr();
        $access_token = ArrayHelper::getValue($params, 'access_token', '');
        $time = date('Y-m-d H:i:s');
        $sign = self::getSign($method, $action, $params, ['time' => $time]);
        $params['access_token'] = self::encode($access_token, $sign, $time);
        $ret = parent::httpExec($action, $params);
        return $ret;
    }

    public function getMethodStr()
    {
        return $this->method == self::METHOD_GET ? 'GET' : 'POST';
    }

    public function checkSign($access_token, &$dataArr = [])
    {
        $dataArr = [];
        $ret = self::decode($access_token, $dataArr);
        if ($ret) {
            return $ret;
        }
        $method = Yii::$app->request->getMethod();
        $uri = $_SERVER['REQUEST_URI'];
        $postArr = Yii::$app->request->post();
        $postArr['access_token'] = $dataArr['token'];
        $sign = self::getSign($method, $uri, $postArr, $dataArr);
        if ($sign != $dataArr['sign']) {
            return 'H_OUTER_SIGN_ERR';
        }
        return false;
    }

    public static function checkWebAjaxSign($authKey, $signature, &$dataArr = [])
    {
        /**
         * X-token-1, X-token-2
         * sign : sha1(url?ksort(get+post))
         */
        $dataArr = [];
        try {
            $keyList = (array) Yii::$app->outer_helper->aeskey;
            foreach ($keyList as $key) {
                $authKey = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, md5($key), base64_decode($authKey), 'cbc', $key);
                $len = strlen($authKey);
                $pad = ord($authKey[$len - 1]);
                $authKey = substr($authKey, 0, $len - $pad);
                if (strpos($authKey, $key) !== false) {
                    break;
                }
            }
        } catch (\Exception $e) {
            Yii::error("Token解析失败({$e->getCode()}):\n{$e->getMessage()}\n{$e->getTraceAsString()}}");
            return 'H_OUTER_SIGN_ERR';
        }
        $authArr = explode('~', $authKey);
        if (count($authArr) != 3) {
            return 'H_OUTER_SIGN_ERR';
        }
        $token = $authArr[1];
        $time = $authArr[2];
        if (false && (!$token || (time() - intval($time) > 600))) {
            self::$debug && Yii::info(['h5token' => $token, 'time' => $time, '_unixts' => time()], self::$category);
            return 'H_OUTER_ARG_ERR';
        }
        $trace = [];
        if (!self::checkWebSign($signature, $trace)) {
            $trace['authKey'] = $authKey;
            $trace['authArr'] = $authArr;
            Yii::info($trace);
            return 'H_OUTER_SIGN_ERR';
        }
        $dataArr['token'] = $token;
        return false;
    }

    public static function checkWebSign($signature, &$trace)
    {
        $uri = ArrayHelper::getValue($_SERVER, 'REQUEST_URI');
        $parts = parse_url($uri);
        $prefix = ArrayHelper::getValue($parts, 'path');
        $signStr = '';
        $get = [];
        parse_str(ArrayHelper::getValue($parts, 'query'), $get);
        $post = empty($_POST) ? Yii::$app->request->post() : $_POST;
        ksort($get);
        ksort($post);
        foreach ($get as $key => $val) {
            $signStr .= "&$key=$val";
        }
        $signStr = ltrim($signStr, '&');
        foreach ($post as $key => $val) {
            /*
             * 为了兼容前端把数组加入签名
             * 用js数组toString的值兼容
             * 其他系统不提倡或者有更好的解决方案
             * @author:杨国帅
             */
            is_array($val) && $val = '[object Object]';
            $signStr .= "&$key=$val";
        }
        $signStr = ltrim($signStr, '&');
        $keyList = (array) Yii::$app->outer_helper->webhkey;
        $trace = compact('get', 'post', 'signArr', 'prefix', 'signStr', 'signature');
        foreach ($keyList as $key) {
            $sign = hash_hmac('sha1', $prefix . $signStr, $key, false);
            $trace[$key] = $sign;
            if ($sign == $signature) {
                return true;
            }
        }
        return false;
    }
}
SysMsg::register('H_OUTER_NETWORK_ERR', '网络错误');
SysMsg::register('H_OUTER_ARG_ERR', '参数错误');
SysMsg::register('H_OUTER_APP_ARG_ERR', '请重新登录', 10);
SysMsg::register(OuterHelper::EMPTY_TOKEN_RET, '参数为空');
SysMsg::register('H_OUTER_SIGN_ERR', '签名错误');
