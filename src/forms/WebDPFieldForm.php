<?php

namespace xionglonghua\common\forms;

use yii\base\Model;

/**
 * WebDPFieldForm is the fieldMap for WebDataProvider
 *
 * @see WebDataProvider
 */
class WebDPFieldForm extends Model
{
    public $code = '';
    public $total = '';
    public $items = '';

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['code', 'total', 'items'], 'string'],
        ];
    }
}
