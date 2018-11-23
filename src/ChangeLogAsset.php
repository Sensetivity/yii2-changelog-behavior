<?php

namespace Sensetivity\ChangeLog;


use yii\web\AssetBundle;

class ChangeLogAsset extends AssetBundle
{
    public $sourcePath = '@vendor/sensetivity/yii2-changelog-behavior/src/assets';

    public $css = [
        'css/style.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\jui\JuiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
}