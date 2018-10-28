<?php

namespace xionglonghua\common\helpers;

use Yii;
use yii\base\InvalidConfigException;

class Url extends \yii\helpers\Url
{
    /**
     * @param string|array $route  同\yii\helpers\Url::to()的参数
     * @param string       $app    符合以太规范的appId，等价于二级域名
     * @param bool         $scheme 是否包含host，如果配置了@var $app且不等于当前的appId则强制包含
     * @param bool         $prefix 是否包含mis的前缀
     *
     * @return string 返回Url/uri
     *
     * @throws \yii\base\InvalidConfigException
     *
     * 我们仅允许\yii\console\Application不配置UrlManager来调用这个方法
     * 如果没有合理的配置，我们会给予默认配置返回一个尽可能正确的结果
     * 如果本身配置正确，不会覆盖。
     * 非console程序调用脚本出异常会被throw。
     *
     * @see https://git.ethercap.com/ethercap/yii2-common/merge_requests/56
     *
     * @author Yang Guoshuai
     */
    public static function toUrl($route = '', $app = '', $scheme = false, $prefix = false)
    {
        $now = Yii::$app->id;
        if ($app && $app !== $now) {
            $scheme = true;
        }

        try {
            $url = Url::to($route, $scheme);
        } catch (InvalidConfigException $e) {
            if (!Yii::$app instanceof \yii\console\Application) {
                throw $e;
            }
            $host = YII_DEBUG ? "http://$now.dev.ethercap.com" : "https://$now.ethercap.com";
            Yii::configure(Yii::$app->urlManager, [
                'enablePrettyUrl' => true,
                'hostInfo' => $host,
                'scriptUrl' => $host,
            ]);
            $url = Url::to($route, $scheme);
        }
        if ($app == $now) {
            return $url;
        }
        $url = str_replace("://${now}.ethercap.com", "://${app}.ethercap.com", $url);
        $url = str_replace("${now}.dev.ethercap.com", "${app}.dev.ethercap.com", $url);
        $url = str_replace("://${now}.test.ethercap.com", "://${app}.test.ethercap.com", $url);
        if ($prefix) {
            $prefix = self::toUrl(['/'], 'mis', true, false);
            $url = $prefix.'#'.$url;
        }
        return $url;
    }
}
