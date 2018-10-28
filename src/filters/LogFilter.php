<?php

namespace xionglonghua\common\filters;

use Yii;
use yii\helpers\Url;
use yii\base\Behavior;
use yii\web\Controller;

/*
CREATE TABLE `access_log` (
  `logId` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT 'url',
  `seg0` varchar(32) NOT NULL DEFAULT '' COMMENT 'controller',
  `seg1` varchar(32) NOT NULL DEFAULT '' COMMENT 'method',
  `seg2` varchar(32) NOT NULL DEFAULT '' COMMENT 'arg1',
  `seg3` varchar(32) NOT NULL DEFAULT '' COMMENT 'arg2',
  `seg4` varchar(32) NOT NULL DEFAULT '' COMMENT 'arg3',
  `ip` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'IP Value',
  `header` text NOT NULL COMMENT 'request header',
  `post` text NOT NULL COMMENT 'body post',
  `creationTime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`logId`),
  KEY `seg0` (`seg0`),
  KEY `ip` (`ip`),
  KEY `userId` (`userId`),
  KEY `seg1` (`seg1`),
  KEY `seg2` (`seg2`),
  KEY `seg3` (`seg3`),
  KEY `seg4` (`seg4`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```
php yii gii/model --tableName=access_log --modelClass=AccessLog


*/

class LogFilter extends Behavior
{
    public $actions = [];
    public $controller;
    public $logModel = 'common\models\AccessLog';

    public function events()
    {
        return [Controller::EVENT_BEFORE_ACTION => 'accessAction'];
    }

    public function accessAction()
    {
        $logModel = $this->logModel;
        $log = new $logModel();
        $segs = array_pad(explode('/', Yii::$app->request->getPathInfo()), 5, '');
        list($log->seg0, $log->seg1, $log->seg2, $log->seg3, $log->seg4) = $segs;
        $log->header = json_encode(Yii::$app->request->getHeaders()->toArray());
        $log->post = json_encode(Yii::$app->request->post());
        $log->url = Yii::$app->request->getHostInfo().Yii::$app->request->url;
        $log->save(false);
        return;
    }
}
