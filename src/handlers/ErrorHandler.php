<?php

namespace xionglonghua\common\handlers;

class ErrorHandler extends \yii\web\ErrorHandler
{
    protected function convertExceptionToArray($exception)
    {
        $arr = parent::convertExceptionToArray($exception);
        $arr['data'] = '';
        if (empty($arr['code'])) {
            $arr['code'] = 1;
        }
        return $arr;
    }
}
