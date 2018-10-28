<?php

namespace xionglonghua\common\assets;

use yii\web\AssetBundle;

class SearchWidgetAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/src';
    public $css = [
    ];

    // jquery-search.js => http://g.alicdn.com/opensearch/opensearch-console/0.16.0/scripts/jquery-ui-1.10.2.js
    public $js = [
        'js/jquery-search.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];

    public function init()
    {
        parent::init();
    }
}
