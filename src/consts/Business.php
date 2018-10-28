<?php

namespace xionglonghua\common\consts;

class Business extends BaseConst
{
    const REFERRAL_INCAPITAL = 'incapital';
    const REFERRAL_INV_TALENT = 'inv_talent';
    const REFERRAL_FA_TALENT = 'fa_talent';
    const REFERRAL_APLUS_COMMUNITY = 'aplus_community';
    const REFERRAL_POSITIVE_CHANNEL = 'positive_channel';
    const REFERRAL_INCUBATOR = 'incubator';
    const REFERRAL_BUSINESS_COOP = 'business_coop';
    const REFERRAL_LEADS = 'leads';
    const REFERRAL_AFTER_FUNDING = 'after_funding';
    const REFERRAL_EDC = 'edc';
    const REFERRAL_ACTIVITY = 'activity';
    const REFERRAL_CC = 'cc';
    const REFERRAL_TUI_PLATFORM = 'tui_platform';

    public static $referrals = [
        self::REFERRAL_INV_TALENT => '星探-投资人',
        self::REFERRAL_FA_TALENT => '星探-FA',
        self::REFERRAL_INCAPITAL => '内部顾问',
        self::REFERRAL_APLUS_COMMUNITY => 'A+社',
        self::REFERRAL_POSITIVE_CHANNEL => '主动渠道',
        self::REFERRAL_INCUBATOR => '孵化器',
        self::REFERRAL_BUSINESS_COOP => '商务合作',
        self::REFERRAL_LEADS => 'leads',
        self::REFERRAL_AFTER_FUNDING => '基金投后',
        self::REFERRAL_EDC => 'EDC',
        self::REFERRAL_ACTIVITY => '活动',
        self::REFERRAL_TUI_PLATFORM => '推荐平台',
    ];
}
