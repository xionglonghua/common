<?php

namespace xionglonghua\common\db\search\filter;

/**
 * search过滤器
 */
interface FilterConditionInterface
{
    /**
     * 从条件中构造filter语句
     *
     * @return string
     */
    public function build();
}
