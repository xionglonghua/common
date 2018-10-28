<?php

namespace xionglonghua\common\helpers;

use Yii;
use yii\base\InvalidConfigException;

class User
{
    public static function getUserById($id)
    {
        if (empty(Yii::$app->sso)) {
            throw new InvalidConfigException('请配置SSO组件');
        }
        $data = Yii::$app->sso->httpExec('/user/infolist', ['idstring' => $id]);
        $data = $data['data'];
        foreach ($data as $line) {
            if ($line['id'] == $id) {
                return $line['name'].'('.$line['id'].')';
            }
        }
        return $id == 0 ? '' : $id;
    }
}
