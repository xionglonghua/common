<?php

namespace xionglonghua\common\consts;

class Round extends BaseConst
{
    public static $default = [
        null => ['val' => -100, 'desc' => '未知轮次'],
        'type' => ['val' => -1, 'desc' => '不知道咋说'],
        'category' => ['val' => -2, 'desc' => '不知道是啥'],
    ];

    const ANGEL = 1 << 1;
    const PRE_A = 1 << 2;
    const A = 1 << 3;
    const B = 1 << 4;
    const C = 1 << 5;
    const D = 1 << 6;
    const OTHER = 1 << 7;

    public static $rounds = [
        self::ANGEL => '天使轮',
        self::PRE_A => 'Pre-A轮',
        self::A => 'A轮',
        self::B => 'B轮',
        self::C => 'C轮',
        self::D => 'D轮',
        self::OTHER => 'D+轮',
    ];

    const PROJECT = 1;
    const EVENT = 2;
    public static $categories = [
        self::PROJECT => '项目',
        self::EVENT => '融资事件',
    ];
}
