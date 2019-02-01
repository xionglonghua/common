<?php

namespace xionglonghua\common\filters;

use Yii;
use yii\base\Behavior;
use yii\web\Controller;
use yii\web\BadRequestHttpException;
use xionglonghua\common\helpers\InnerHelper;
use xionglonghua\common\helpers\StringHelper;
use yii\helpers\ArrayHelper;

//内部接口行为
class InnerFilter extends Behavior
{
    public $actions = [];
    public $controller;
    private $_innerKey;

    public function events()
    {
        return [Controller::EVENT_BEFORE_ACTION => 'beforeAction'];
    }

    public function beforeAction($event)
    {
        $action = $event->action->id;
        if (in_array($action, $this->actions)) {
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            // ip不正确
            /*if (!StringHelper::isInnerIP()) {
                throw new BadRequestHttpException(Yii::t('yii', 'IP地址不正确'), 1);
            }*/
            //prettyurl处理参数，会把uri的slash部分变成get参数
            //因此，$_GET和$app->request->get都不是原始get，但是queryString不变。
            //For a POST request, include get params as a part of path(i.e. action).
            $path = $_SERVER['REQUEST_URI'];
            if (Yii::$app->request->isGet) {
                $method = 'GET';
                parse_str(Yii::$app->request->queryString, $params);
                $path = explode('?', $path)[0];
            } else {
                $method = 'POST';
                $params = Yii::$app->request->post();
            }
            if (empty($params['_sign']) || empty($params['_key']) || empty($params['_ts']) || empty($params['_nonce'])) {
                throw new BadRequestHttpException(Yii::t('yii', '参数错误'), 1);
            }
            $this->_innerKey = $params['_key'];
            $secret = $this->getSecret($params['_key']);
            if (!$secret) {
                throw new BadRequestHttpException(Yii::t('yii', '该key不存在'), 1);
            }
            $checkSign = $params['_sign'];
            $ts = $params['_ts'];
            //检查时间戳是否正确
            if (abs(time() - $ts) > 5 * 60) {
                throw new BadRequestHttpException(Yii::t('yii', '时间错误'), 1);
            }
            $sign = InnerHelper::getSign($method, $path, $params, $secret);
            if ($checkSign != $sign) {
                throw new BadRequestHttpException(Yii::t('yii', '签名错误'), 20);
            }
        }
    }

    public function getSecret($key)
    {
        return ArrayHelper::getValue(Yii::$app->params, 'keylist.'.$key);
    }

    public function getInnerAccessKey()
    {
        return $this->_innerKey;
    }
}
