<?php

namespace xionglonghua\common\helpers;

use Yii;
use xionglonghua\curl\CurlHttp;

class AdminHelper extends CurlHttp
{
    public $secret;
    protected $type;
    protected $class;
    protected $path;
    private $isRPC = false;

    protected static $TYPE_ARRAY = ['model', 'helper', 'library'];
    protected static $PATH_ARRAY = ['common', 'security', 'sql', 'stats', 'weixin'];

    public function __construct()
    {
        $this->host = \yii\helpers\ArrayHelper::getValue(Yii::$app->params, 'admin.host');
        $this->port = \yii\helpers\ArrayHelper::getValue(Yii::$app->params, 'admin.port');
        $this->secret = \yii\helpers\ArrayHelper::getValue(Yii::$app->params, 'admin.secret');
        $this->beforeRequest = function ($params, $curlHttp) {
            $accessToken = md5($curlHttp->secret. date('Y-m-d H'));
            $params['accessToken'] = $accessToken;
            return $params;
        };
        $this->afterRequest = function ($response, $curlHttp) {
            $code = curl_getinfo($curlHttp->getCurl(), CURLINFO_HTTP_CODE);
            $data = [
                'code' => 1,
                'message' => '网络错误',
                'data' => [],
            ];
            if ($code == 200) {
                if ($curlHttp->isRPC) {
                    if (!$curlHttp->_isJson($response) && !$curlHttp->_isHTML($response)) {
                        Yii::info('ok!'.$response, 'curl.admin');
                        return unserialize(base64_decode($response));
                    }
                    Yii::warning('error!'.$response, 'curl.admin');
                    return null;
                }
                $ret = json_decode($response);
                if (empty($ret)) {
                    Yii::warning('error!'.$response, 'curl.admin');
                    return $data;
                }
                if (!empty($ret->code)) {
                    Yii::warning('error!'.$response, 'curl.admin');
                    return $ret;
                }
                Yii::info('ok!', 'curl.admin');
                return $ret;
            }
            if ($curlHttp->isRPC) {
                Yii::warning('error!'.$response, 'curl.admin');
                return null;
            }
            Yii::error('error', 'curl.admin');
            return $data;
        };
    }

    public function __get($string)
    {
        if (!$this->type && in_array($string, self::$TYPE_ARRAY)) {
            $this->type = $string;
        } elseif (!$this->path && in_array($string, self::$PATH_ARRAY)) {
            $this->path = $string;
        } else {
            $this->class = $string;
        }
        return $this;
    }

    public function __call($method, $args = null)
    {
        $this->_setDefault();
        $this->isRPC = true;
        $url = '/crm/admin/'.$this->type.'/'.$this->class.'/'.$method;
        $params = [
            'path' => $this->path,
            'args' => base64_encode(serialize($args)),
        ];
        $data = $this->setPost()->httpExec($url, $params);
        $this->_reset();
        return $data;
    }

    private function _isJson($str)
    {
        return substr(trim($str), 0, 1) === '{';
    }

    private function _isHTML($str)
    {
        return substr(trim($str), 0, 1) === '<';
    }

    private function _setDefault()
    {
        $this->type || $this->type = 'model';
        if ($this->path && !$this->class) {
            $this->class = $this->path;
            $this->path = '';
        }
    }

    private function _reset()
    {
        $this->type = $this->class = $this->path = null;
        $this->isRPC = false;
    }

    public function getUserMessages($userId = null)
    {
        empty($userId) && $userId = Yii::$app->user->identity->userId;
        return $this->setPost()->httpExec('/crm/home/getUserMessages', ['userId' => $userId]);
    }

    public function getNavBarMsg($userId = null)
    {
        empty($userId) && $userId = Yii::$app->user->identity->userId;
        return $this->setPost()->httpExec('/crm/navbar/msg', ['userId' => $userId]);
    }

    public function updateKPIStatistic($type, $start, $userId = null)
    {
        empty($userId) && $userId = Yii::$app->user->identity->userId;
        $url = "/crm/home/updateKPIStatistic/$type/$start";
        return $this->setPost()->httpExec($url, ['userId' => $userId]);
    }

    public function getRemindProjects($page = 1, $limit = 10, $userId = null)
    {
        empty($userId) && $userId = Yii::$app->user->identity->userId;
        $url = '/crm/home/getRemindProjects';
        return $this->setPost()->httpExec($url, ['userId' => $userId]);
    }

    public function getOperationProjects($page = 0, $limit = 10, $userId = null)
    {
        empty($userId) && $userId = Yii::$app->user->identity->userId;
        $url = '/crm/home/getOperationProjects';
        return $this->setPost()->httpExec($url, ['userId' => $userId]);
    }

    public function comMeeting($page = 0, $limit = 10, $userId = null)
    {
        empty($userId) && $userId = Yii::$app->user->identity->userId;
        $url = '/crm/home/comMeeting';
        return $this->setPost()->httpExec($url, ['userId' => $userId]);
    }

    public function getLastMonthChampion($userId = null)
    {
        empty($userId) && $userId = Yii::$app->user->identity->userId;
        return $this->setPost()->httpExec('/crm/home/getLastMonthChampion', ['userId' => $userId]);
    }

    public function getMyProjectCount($userId = null)
    {
        empty($userId) && $userId = Yii::$app->user->identity->userId;
        return $this->setPost()->httpExec('/crm/home/getMyProjectCount', ['userId' => $userId]);
    }

    public function getMyProjectList($data)
    {
        $params = [
            'userId' => Yii::$app->user->identity->userId,
            'data' => json_encode($data),
        ];
        return $this->setPost()->httpExec('/crm/my_project/index', $params);
    }

    public function getMyprojectFromCache($tab = 'follow', $keyword = '', $userId = null, $groupId = null)
    {
        $data = [
            'finance' => null,
            'priority' => null,
            'sign' => null,
            'orderBy' => null,
            'tab' => $tab,
            'keyword' => $keyword,
        ];
        if ($userId) {
            $data['userId'] = $userId;
            $data['groupId'] = null;
        } else {
            $data['userId'] = null;
            $data['groupId'] = $groupId;
        }
        $key = md5(json_encode($data));
        $ret = Yii::$app->cache->get($key, '');
        if (!empty($ret)) {
            return json_decode($ret);
        }
        $ret = $this->getMyProjectList($data);
        Yii::$app->cache->set($key, json_encode($ret));
        return $ret;
    }

    public function getUserById($userId = null)
    {
        empty($userId) && $userId = Yii::$app->user->identity->userId;
        return $this->setPost()->httpExec('/crm/user_list/getUserById', ['userId' => $userId]);
    }

    public function getUsersByKeyword($keyword)
    {
        return $this->setPost()->httpExec('/crm/user_list/searchKeyword', ['keyword' => $keyword]);
    }
}
