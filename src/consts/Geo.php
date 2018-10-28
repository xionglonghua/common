<?php

namespace xionglonghua\common\consts;

class Geo extends BaseConst
{
    public static $default = [
        null => ['val' => '未知', 'desc' => '未知城市'],
    ];

    public static $cities = [
        null => '无',
        0 => '北京',
        1 => '上海',
        2 => '广州',
        3 => '深圳',
        4 => '杭州',
        5 => '成都',
        6 => '武汉',
        7 => '南京',
        8 => '厦门',
        999 => '其他',
    ];
}
