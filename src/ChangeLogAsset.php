<?php

namespace Sensetivity\ChangeLog;


use yii\web\AssetBundle;

class ChangeLogAsset extends AssetBundle
{
    public $basePath = '@webroot';

    public $baseUrl = '@web';

    public $css = [
        'css/style.css',
    ];


    public $depends = [
        'yii\web\YiiAsset',
        'yii\jui\JuiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];

    public function init()
    {
        $this->sourcePath  = (__DIR__ . '/assets');
        parent::init();
    }
}
