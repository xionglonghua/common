<?php

namespace xionglonghua\common\actions;

use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use xionglonghua\common\helpers\SysMsg;

class LoggerAction extends Action
{
    public $types = [
        'info' => 'info',
        'warn' => 'warning',
        'warning' => 'warning',
        'error' => 'error',
    ];

    public $response;

    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if ($arr = $this->getRequestData()) {
            $type = ArrayHelper::getValue($arr, 'type');
            $data = ArrayHelper::getValue($arr, 'data', '');
            $category = Yii::$app->request->getHostName();
            if (isset($this->types[$type])) {
                // traceLevel必须为0
                $traceLevel = Yii::$app->log->traceLevel;
                Yii::$app->log->traceLevel = 0;
                call_user_func(array('Yii', $this->types[$type]), $data, $category);
                Yii::$app->log->traceLevel = $traceLevel;
            }
        }
        if (!$this->response) {
            return SysMsg::getOkData();
        } elseif (is_callable($this->response)) {
            return call_user_func($this->response);
        } else {
            return $this->response;
        }
    }

    private function isJson($val)
    {
        if (!is_string($val)) {
            return false;
        }
        @json_decode($val, true);
        return json_last_error() === JSON_ERROR_NONE;
    }

    protected function getRequestData()
    {
        $data = [];
        if (Yii::$app->request->isConsoleRequest) {
            return $data;
        }
        if ($get = Yii::$app->request->get()) {
            $data = ArrayHelper::merge($data, $get);
        }
        if (Yii::$app->request->getIsPost()) {
            if ($this->isJson(Yii::$app->request->getRawBody())) {
                $post = json_decode(Yii::$app->request->getRawBody(), true);
            } else {
                $post = Yii::$app->request->post();
            }
            $data = ArrayHelper::merge($data, $post);
        }
        return $data;
    }
}
