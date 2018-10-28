<?php

namespace xionglonghua\common\behaviors;

use yii\helpers\ArrayHelper;

/**
 * ```php
 * use yii\common\behaviors\AttrBehavior;
 *
 * public function behaviors()
 * {
 *     return [
 *           [
 *               'class' => AttrBehavior::class,
 *               'attrKey' => 'attr',
 *               'properties' => [
 *                   'attr1',
 *                   'attr2',
 *               ],
 *           ],
 *     ];
 * }
 * ```
 */
class AttrBehavior extends \yii\base\Behavior
{
    public $properties = null;
    public $attrKey = 'attr';

    public function extractAttr($model = null)
    {
        if (is_array($model)) {
            $isArr = true;
        } elseif (is_object($model)) {
            $isArr = false;
        } else {
            return false;
        }
        foreach ($model as $key => $value) {
            if ($this->owner->canGetProperty($key) && $this->owner->$key !== null) {
                $isArr ? $model[$key] = $this->owner->$key : $model->$key = $this->owner->$key;
            }
        }
        return true;
    }

    public function loadAttr($model = null)
    {
        if ($model && ArrayHelper::isTraversable($model)) {
            foreach ($model as $key => $value) {
                if ($this->owner->canSetProperty($key)) {
                    $this->owner->$key = $value;
                }
            }
            return $this->owner;
        }
        return false;
    }

    public function setAttr($key, $value)
    {
        $attrKey = $this->attrKey;
        $data = json_decode($this->owner->$attrKey, true);
        if (empty($data)) {
            $data = [];
        }
        $data[$key] = $value;
        $this->owner->$attrKey = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function getAttr($key, $defaultVal = null)
    {
        $attrKey = $this->attrKey;
        $data = json_decode($this->owner->$attrKey, true);
        return ArrayHelper::getValue($data, $key, $defaultVal);
    }

    public function clearAttr($key = null)
    {
        $attrKey = $this->owner->attrKey;
        if (is_null($key)) {
            // delete all
            $this->$attrKey = '';
        } else {
            // delete an item
            $data = json_decode($this->$attrKey, true);
            if (empty($data)) {
                $data = [];
            }
            unset($data[$key]);
            $this->$attrKey = json_encode($data);
        }
    }

    public function canGetProperty($name, $checkVars = true)
    {
        return $this->properties === null || (is_array($this->properties) && in_array($name, $this->properties));
    }

    public function canSetProperty($name, $checkVars = true)
    {
        return $this->properties === null || (is_array($this->properties) && in_array($name, $this->properties));
    }

    public function __get($name)
    {
        return $this->getAttr($name, null);
    }

    public function __set($name, $value)
    {
        return $this->setAttr($name, $value);
    }
}
