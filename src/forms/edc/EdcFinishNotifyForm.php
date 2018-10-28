<?php

namespace xionglonghua\common\forms\edc;

use xionglonghua\common\helpers\edc\EdcNotifyHandler;
use yii\base\Model;

/**
 * EdcFinishNotifyForm Edc id完成时 通知下游的data格式
 * fromId/fromType/edcId
 */
class EdcFinishNotifyForm extends Model
{
    public $fromId = 0;
    public $fromType = 0;
    public $edcId = 0;

    public function getNotifyType()
    {
        return EdcNotifyHandler::EVENT_TYPE_ON_FINISH;
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['fromType', 'edcId', ], 'integer'],
            [['fromId', 'fromType', 'edcId', ], 'required'],
        ];
    }
}
