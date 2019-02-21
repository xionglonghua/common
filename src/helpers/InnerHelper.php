<?php

namespace xionglonghua\common\helpers;

use Yii;
use yii\di\Instance;
use yii\helpers\ArrayHelper;

class InnerHelper extends \lspbupt\curl\CurlHttp
{
    public $responseHandler;
    private $_resHandler;
    public $appsecret;
    public $appkey;
    //储存curl_getinfo()的关联数组
    public $info;

    //客户端请求选项，用位标志
    private $option = 0;
    //获取原始的请求结果而不是以json对待
    const INNER_OPT_RAW_RESPONSE = 1;

    public function init()
    {
        $this->afterRequest = function ($response, $curlHttp) {
            //闭包内用$this很奇怪，都改成第二个参数。
            $category = 'curl.'.$curlHttp->appkey;
            //如果是原始结果，直接返回, 要注意原始结果一般都是file download, 不要把response打印出来，否则会直接mem*2, 具体看Yii::info的实现
            if ($curlHttp->option & self::INNER_OPT_RAW_RESPONSE) {
                Yii::info('ok!'.$curlHttp->getUrl(), $category);
                return $response;
            }

            $logInfo = sprintf(' [%s] [%s]', $curlHttp->getUrl(), $response);
            $data = [
                'code' => 1,
                'message' => '网络错误',
                'data' => [],
            ];
            $ret = json_decode($response, true);
            if (empty($ret) || !isset($ret['code'])) {
                Yii::warning('error!'.$logInfo, $category);
                return $data;
            }
            $data['code'] = $ret['code'];
            $data['message'] = ArrayHelper::getValue($ret, 'message', '网络错误');
            $data['data'] = ArrayHelper::getValue($ret, 'data', []);
            if (!empty($ret['code'])) {
                Yii::info('error!'.$logInfo, $category);
                return $data;
            }
            Yii::info('ok!'.$logInfo, $category);
            return $data;
        };
        return parent::init();
    }

    protected function beforeCurl($params)
    {
        if ($this->getResHandler()) {
            $this->getResHandler()->preset($this);
        }
        return parent::beforeCurl($params);
    }

    //请求之后的操作
    protected function afterCurl($data)
    {
        //在释放$ch之前保存现场。
        $this->info = curl_getinfo($this->getCurl());
        $data = parent::afterCurl($data);
        if ($this->getResHandler()) {
            $data = $this->getResHandler()->handle($this, $data);
        }
        return $data;
    }

    public function getResHandler()
    {
        if (!$this->responseHandler) {
            return false;
        }
        if (is_null($this->_resHandler)) {
            $this->_resHandler = Instance::ensure($this->responseHandler, ResponseHelper::class);
        }
        return $this->_resHandler;
    }

    public function send($action = '/', $params = [], $option = 0, &$code = null)
    {
        //由于请求的params的value应该是被编码过的，不应该含有\r,\r\n在接收端会变化导致签名错误
        foreach ($params as $key => &$value) {
            $value instanceof \CURLFile || $value = str_replace("\r", "\n", $value);
        }
        //设置客户端选项
        $this->option = $option;
        $method = $this->getMethodStr();
        $params['_key'] = $this->appkey;
        $params['_ts'] = time();
        $params['_nonce'] = uniqid();
        $sign = self::getSign($method, $action, $params, $this->appsecret);
        $params['_sign'] = $sign;
        $ret = parent::send($action, $params);
        //获取httpcode，如果请求失败传回-1
        $code = ArrayHelper::getValue($this->info, 'http_code', -1);
        return $ret;
    }

    /**
     * @deprecated 推荐使用send方法
     *
     * @param string $action
     * @param array  $params
     * @param int    $option
     * @param null   $code
     *
     * @return mixed
     */
    public function httpExec($action = '/', $params = [], $option = 0, &$code = null)
    {
        return $this->send($action, $params, $option, $code);
    }

    public function getMethodStr()
    {
        return $this->method == self::METHOD_GET ? 'GET' : 'POST';
    }

    public static function getSign($method, $action, $arr, $secret)
    {
        if (isset($arr['_sign'])) {
            unset($arr['_sign']);
        }
        ksort($arr);
        $temp = '';
        foreach ($arr as $key => $val) {
            //对于文件，不进行签名，接收方会在$_FILES[]/"files"(for python)中。
            //接收请求的时候，$_FILES不会在这个数组，因此不会计算签名。
            if ($val instanceof \CURLFile) {
                continue;
            }
            $temp .= self::percentEncode($key).'='.self::percentEncode($val).'&';
        }
        $str = $method.'&'.self::percentEncode($action).'&'.self::percentEncode(trim($temp, '&'));
        return hash_hmac('sha1', $str, $secret.'&');
    }

    public function percentEncode($str)
    {
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }
}
SysMsg::register('H_INNER_NETWORK_ERR', '网络错误');
