<?php

namespace xionglonghua\common\assets;

use yii\web\AssetBundle;

class DiffAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/src';
    public $css = [
        'css/diff.css',
    ];

    public $js = [
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];

    public function init()
    {
        parent::init();
    }
}
