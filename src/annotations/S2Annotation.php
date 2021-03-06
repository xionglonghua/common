<?php

namespace xionglonghua\common\annotations;

use xionglonghua\common\base\Annotation;

/**
 * @Annotation
 */
class S2Annotation extends Annotation
{
    public $ajax = true;
    public $multiple = false;
    public $allowClear = true;
    public $url;
    public $name;
    public $initValueText;
    public $placeholder;
    public $hint;
    public $staticValue;
}
