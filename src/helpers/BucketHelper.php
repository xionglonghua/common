<?php

namespace xionglonghua\common\helpers;

use Yii;
use yii\helpers\Html;

class BucketHelper
{
    public static function uploadUrl($component, $bucket, $object, $filekey = 'file_data')
    {
        $oss = Yii::$app->$component;
        if ($oss instanceof \xionglonghua\common\helpers\InnerHelper) {
            $url = "/$bucket/upload?filekey=$filekey&object=$object";
            $ret = $oss->setPost()->setHeader('X-FILE-OPTION', 'TOKEN')->httpExec($url, []);
            if (!$ret['code']) {
                return $ret['data']['url'];
            }
            throw new \Exception('error x-file-option token', 1);
        } elseif ($oss instanceof \xionglonghua\curl\CurlHttp) {
            $oss->setAction("/$bucket/upload?filekey=$filekey&object=$object");
            return $oss->getUrl();
        } else {
            throw new \Exception('这个类不是oss组件', 1);
        }
        return $url;
    }

    public static function downloadUrl($component, $url, $rename = '', $watermark = '', $counterToken = '')
    {
        $oss = Yii::$app->$component;
        $path = parse_url($url, PHP_URL_PATH);
        if ($oss instanceof \xionglonghua\common\helpers\InnerHelper) {
            $rename && $oss->setHeader('X-FILE-RENAME', str_replace('+', '%2B', $rename));
            $watermark && $oss->setHeader('X-FILE-WM', $watermark);
            $counterToken && $oss->setHeader('X-FILE-COUNTER-TOKEN', $counterToken);
            $ret = $oss->setGet()->setHeader('X-FILE-OPTION', 'TOKEN')->httpExec($path, []);
            $url = ArrayHelper::getValue($ret, 'data.url', $url);
        } elseif ($oss instanceof \xionglonghua\curl\CurlHttp) {
            $url = $oss->setAction($path)->url;
        } else {
            throw new \Exception('这个类不是oss组件', 1);
        }
        return $url;
    }

    public static function a($name, $url, $class = ['class' => 'btn btn-default'], $component = 'in_oss', $rename = '', $watermark = '', $counterToken = '')
    {
        return Html::a($name, self::downloadUrl($component, $url, $rename, $watermark, $counterToken), $class);
    }
}
