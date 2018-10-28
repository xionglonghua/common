<?php

namespace xionglonghua\common\forms;

use xionglonghua\common\assets\DiffAsset;
use yii\base\Model;

/**
 * DiffFieldsForm 将old 和 new的diff求出并展示html
 * old / new 要求是数组形式
 * NOTE: 使用时候必须引入对应的diff.css, 建议直接register DiffAsset
 * @see DiffAsset
 */
class DiffFieldsForm extends Model
{
    public $old = [];
    public $new = [];

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['old', 'new', ], 'required'],
            [['old', 'new', ], 'formatData'],
        ];
    }

    public function getDiffHtml()
    {
        $diff = new \Diff($this->old, $this->new);
        return $diff->render(new \Diff_Renderer_Html_Inline());
    }

    public function formatData($attribute, $params, $validator)
    {
        $arr = $this->$attribute;
        if (!is_array($arr)) {
            $arr = [$arr];
        }
        ksort($arr);
        $arr = array_map(
            function ($v, $k) {
                is_array($v) && $v = json_encode($v);
                return $k . ':' . $v;
            },
            $arr,
            array_keys($arr)
        );
        foreach ($arr as $i => $line) {
            $arr[$i] = rtrim($line, "\r\n");
        }
        $this->$attribute = $arr;
    }
}
