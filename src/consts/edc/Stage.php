<?php

namespace xionglonghua\common\consts\edc;

use xionglonghua\common\consts\BaseConst;

class Stage extends BaseConst
{
    public static $default = [
        null => ['val' => 0, 'desc' => '未知轮次'],
    ];

    const STAGE_INIT = 1;
    const STAGE_NO = 10;
    const STAGE_SEED = 15;
    const STAGE_ANGEL = 20;
    const STAGE_PRE = 30;
    const STAGE_A = 40;
    const STAGE_A_PLUS = 50;
    const STAGE_PRE_B = 55;
    const STAGE_B = 60;
    const STAGE_B_PLUS = 65;
    const STAGE_C = 70;
    const STAGE_C_PLUS = 71;
    const STAGE_D = 75;
    const STAGE_D_PLUS = 76;
    const STAGE_E = 80;
    const STAGE_F = 85;
    const STAGE_G = 90;
    const STAGE_PRE_IPO = 95;
    const STAGE_IPO = 100;
    const STAGE_IPO_IN_MARKET = 105;
    const STAGE_BUY = 110;
    const STAGE_RM = 111;
    const STAGE_SPO = 112;
    const STAGE_STOCK_RIGHT_TRANSFER = 113;
    const STAGE_DAI = 115;
    const STAGE_SRT = 117;
    const STAGE_NEW_THIRD_MARKET = 120;
    const STAGE_STRATEGY = 130;
    const STAGE_PRIVATISATION = 150;
    const STAGE_PUBLIC_FINANCING = 151;
    const STAGE_DEBT_FINANCING = 152;
    const STAGE_OTHER = 153;
    const STAGE_PF = 170;
    const STAGE_DF = 190;
    const STAGE_EDC_OTHER = 300;

    public static $stages = [
        self::STAGE_INIT => '未知',
        self::STAGE_NO => '未融资',
        self::STAGE_SEED => '种子轮',
        self::STAGE_ANGEL => '天使轮',
        self::STAGE_PRE => 'Pre-A轮',
        self::STAGE_A => 'A轮',
        self::STAGE_A_PLUS => 'A+轮',
        self::STAGE_PRE_B => 'Pre-B轮',
        self::STAGE_B => 'B轮',
        self::STAGE_B_PLUS => 'B+轮',
        self::STAGE_C => 'C轮',
        self::STAGE_C_PLUS => 'C+轮',
        self::STAGE_D => 'D轮',
        self::STAGE_D_PLUS => 'D+轮',
        self::STAGE_E => 'E轮',
        self::STAGE_F => 'F轮',
        self::STAGE_G => 'G轮',
        self::STAGE_PRE_IPO => 'Pre-IPO',
        self::STAGE_IPO => 'IPO上市',
        self::STAGE_IPO_IN_MARKET => 'IPO上市',
        self::STAGE_BUY => '并购',
        self::STAGE_RM => '借壳上市',
        self::STAGE_SPO => '定向增发',
        self::STAGE_STOCK_RIGHT_TRANSFER => '股权转让',
        self::STAGE_DAI => '定向增发',
        self::STAGE_SRT => '股权转让',
        self::STAGE_NEW_THIRD_MARKET => '新三板',
        self::STAGE_STRATEGY => '战略融资',
        self::STAGE_PRIVATISATION => '私有化',
        self::STAGE_PUBLIC_FINANCING => '众筹融资',
        self::STAGE_DEBT_FINANCING => '债权融资',
        self::STAGE_OTHER => '其他',
        self::STAGE_EDC_OTHER => '其他',
    ];
}
