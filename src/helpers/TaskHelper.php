<?php

namespace xionglonghua\common\helpers;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\base\ErrorException;

class TaskHelper extends \xionglonghua\common\helpers\InnerHelper
{
    /*
     ** params
     **   -- title
     **   -- desc
     **   -- executorId
     **   -- workflowId
     **   -- type
     **   -- bid
     */
    public function create(array $params)
    {
        $array = [];
        $title = ArrayHelper::getValue($params, 'title', '');
        $desc = ArrayHelper::getValue($params, 'desc', '');
        $workflowId = ArrayHelper::getValue($params, 'workflowId', 0);
        $type = ArrayHelper::getValue($params, 'type', '');
        $bid = ArrayHelper::getValue($params, 'bid', 0);
        $executorId = ArrayHelper::getValue($params, 'executorId', 0);
        if (!$workflowId) {
            throw new InvalidConfigException('请给出工作流模板ID');
        }
        if (!$type) {
            throw new InvalidConfigException('请填写业务类型');
        }
        $array['title'] = $title;
        $array['desc'] = $desc;
        $array['workflowId'] = $workflowId;
        $array['type'] = $type;
        $array['bid'] = $bid;
        $array['executorId'] = $executorId;

        $data = $this->setPost()->httpExec('/task/new', $array);
        if ($data['code']) {
            throw new ErrorException($data['message']);
        }
        return $data['data']['taskId'];
    }

    public function search($type, $bid)
    {
        $array = [
            'type' => $type,
            'bid' => $bid,
        ];
        $data = $this->setGet()->httpExec('/task/search', $array);
        if ($data['code']) {
            Yii::warning($data['message']);
            return false;
        }
        return $data['data']['taskId'];
    }

    public function process($taskId, $actionName)
    {
        $array = [
            'taskId' => $taskId,
            'action' => $actionName,
        ];
        $data = $this->setPost()->httpExec('/task/process', $array);
        if ($data['code']) {
            throw new ErrorException($data['message']);
        }
    }

    public function choose($taskId, $executorId)
    {
        $array = [
            'taskId' => $taskId,
            'executorId' => $executorId,
        ];
        $data = Yii::$app->task->setPost()->httpExec('/task/choose', $array);
        if ($data['code']) {
            throw new ErrorException($data['message']);
        }
    }

    public function info($taskId)
    {
        $array = [
            'taskId' => $taskId,
        ];
        $data = Yii::$app->task->setGet()->httpExec('/task/info', $array);
        if ($data['code']) {
            throw new ErrorException($data['message']);
        }
        return $data['data']['info'];
    }

    public function getWorkflowIdByName($name)
    {
        $array = ['name' => $name];
        $data = Yii::$app->task->setGet()->httpExec('/workflow/get-id-by-name', $array);
        if ($data['code']) {
            throw new ErrorException($data['message']);
        }
        return $data['data']['id'];
    }
}
