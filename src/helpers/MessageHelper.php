<?php

namespace xionglonghua\common\helpers;

use Yii;
use yii\base\InvalidConfigException;

class MessageHelper extends InnerHelper
{
    public $logger = null;

    public function init()
    {
        parent::init();
        if ($this->logger === null) {
            return;
        }
        $this->logger = Yii::createObject([
            'class' => $this->logger,
        ]);
        if ($this->logger === null) {
            throw new InvalidConfigException('无法创建消息日志记录类');
        }
        if (!method_exists($this->logger, 'log')) {
            throw new InvalidConfigException('消息记录类必须是使用`\\xionglonghua\\common\\traits\\MsgLogTrait`的实例');
        }
        return;
    }

    public function sendEmail(array $config)
    {
        $ret = $this->setPost()->httpExec('/email/send', $config);
        if ($this->logger) {
            $this->logger->log('email', $ret, $config);
        }
        return $ret;
    }

    public function sendSms(array $config)
    {
        $ret = $this->setPost()->httpExec('/sms/send', $config);
        if ($this->logger) {
            $this->logger->log('sms', $ret, $config);
        }
        return $ret;
    }

    public function sendPush(array $config)
    {
        $ret = $this->setPost()->httpExec('/push/send', $config);
        if ($this->logger) {
            $this->logger->log('push', $ret, $config);
        }
        return $ret;
    }
}
