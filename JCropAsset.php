<?php

namespace ereminmdev\yii2\cropimageupload;

use yii\web\AssetBundle;


class JCropAsset extends AssetBundle
{
    public $sourcePath = '@vendor/ereminmdev/yii2-cropimageupload/assets/jcrop';

    public $js = [
        YII_DEBUG ? 'js/Jcrop.js' : 'js/Jcrop.min.js',
    ];

    public $css = [
        YII_DEBUG ? 'css/Jcrop.css' : 'css/Jcrop.min.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
    ];
}
