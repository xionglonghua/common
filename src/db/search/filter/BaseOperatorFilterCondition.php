<?php

namespace xionglonghua\common\db\search\filter;

use xionglonghua\common\db\search\SearchModel;
use yii\base\Model;

/**
 * OneOperatorFilterCondition : 一元运算符构造
 * = / != / > / < / >= / <=
 */
abstract class BaseOperatorFilterCondition extends Model implements FilterConditionInterface
{
    // 判断是否是有效值，int型的用is_null 其他的用empty
    public static function checkEmpty($value, $valueType)
    {
        return (in_array($valueType, [SearchModel::SEARCH_INT, SearchModel::SEARCH_INT_ARRAY])) ? (is_null($value) || !is_numeric($value)) : empty($value);
    }
}
