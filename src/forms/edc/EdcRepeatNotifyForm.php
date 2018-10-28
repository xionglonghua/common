<?php

namespace xionglonghua\common\forms\edc;

use xionglonghua\common\helpers\edc\EdcNotifyHandler;
use yii\base\Model;

/**
 * EdcRepeatNotifyForm Edc id repeat时 通知下游的data格式
 * mainId/edcId
 */
class EdcRepeatNotifyForm extends Model
{
    public $edcId = 0;
    public $mainId = 0;
    public $fromType = 0;

    public function getNotifyType()
    {
        return EdcNotifyHandler::EVENT_TYPE_ON_REPEAT;
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['mainId', 'edcId', 'fromType'], 'integer'],
            [['mainId', 'edcId', ], 'required'],
        ];
    }
}
