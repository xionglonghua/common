<?php

namespace xionglonghua\common\forms\edc;

use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * EdcNotifyConfForm Edc id完成消息处理 里的配置
 * 'className' => Model1::class, 'id' => 'id', 'edcId' => 'businessId'
 *
 * @property ActiveRecord $className
 */
class EdcNotifyConfForm extends Model
{
    public $className;
    public $id = 'id';
    public $edcId = 'edcId';

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['className', 'id', 'edcId', ], 'string'],
            [['className', ], 'required'],
        ];
    }
}
