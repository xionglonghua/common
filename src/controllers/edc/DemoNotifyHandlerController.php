<?php

namespace xionglonghua\common\controllers\edc;

use xionglonghua\common\controllers\QueueController;
use xionglonghua\common\helpers\edc\EdcNotifyHandler;
use Yii;

/**
 * Demo: edcUp完成后修改人库的关联
 *
 * @package console\controllers
 */
class DemoNotifyHandlerController extends QueueController
{
    // 这里写上订阅的队列名
    public $queue = 'edcUpPerson';

    /**
     * @return bool
     */
    public function actionStart()
    {
        return parent::actionStart();
    }

    public function processData()
    {
        try {
            $ret = $this->dealData($this->oneData);
            $ret ? Yii::info($this->oneData, 'person.upsync') : $this->failure();
        } catch (\Exception $exception) {
            // 处理失败, 会把数据放回队列，并计算重试次数
            $this->failure();
        }
    }

    private function dealData($data)
    {
        $flag = true;
        if ($data['type'] === EdcNotifyHandler::EVENT_TYPE_ON_FINISH) {
            $flag = EdcNotifyHandler::handleFinish($data['data']);
        } elseif ($data['type'] === EdcNotifyHandler::EVENT_TYPE_ON_REPEAT) {
            $flag = EdcNotifyHandler::handleRepeat($data['data']);
        } elseif ($data['type'] === EdcNotifyHandler::EVENT_TYPE_ON_DATA_FINISH) {
            $flag = true;
        } elseif ($data['type'] === EdcNotifyHandler::EVENT_TYPE_ON_DATA_REPEAT) {
            $flag = true;
        }
        return $flag;
    }
}
