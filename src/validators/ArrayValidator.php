<?php

namespace xionglonghua\common\validators;

use yii\validators\Validator;

class ArrayValidator extends Validator
{
    /**
     * @var int min
     */
    public $min;
    public $max;
    public $elements;
    public $keys;
    public $keyIn;

    public $skipOnEmpty = false;
    public $enableClientValidation = false;

    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;
        if ($this->keyIn !== null && is_array($this->keyIn)) {
            !isset($this->keyIn[$value]) && $this->addError($model, $attribute, '{attribute} 不在选定的范围之内。');
            return;
        }
        if (empty($model->$attribute)) {
            $model->$attribute = [];
        }
        $value = $model->$attribute;
        if (!is_array($value)) {
            $this->addError($model, $attribute, '{attribute} 必须是空或者数组。');
            return;
        }
        $length = count($value);
        if ($this->min !== null && $length < $this->min) {
            $this->addError($model, $attribute, '{attribute} 的个数至少是{min}个。', ['min' => $this->min]);
        }
        if ($this->max !== null && $length > $this->max) {
            $this->addError($model, $attribute, '{attribute} 的个数至多是{max}个。', ['max' => $this->max]);
        }
        if ($this->elements !== null && is_array($this->elements)) {
            $diff = array_diff($value, $this->elements);
            $diff && $this->addError($model, $attribute, '{attribute} 不能包含这些值：{diffs}。', ['diffs' => implode(',', $diff)]);
        }
        if ($this->keys !== null && is_array($this->keys)) {
            $diff = array_diff($value, array_keys($this->keys));
            $diff && $this->addError($model, $attribute, '{attribute} 不能包含这些值：{diffs}。', ['diffs' => implode(',', $diff)]);
        }
    }
}
