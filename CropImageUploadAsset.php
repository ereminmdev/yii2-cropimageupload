<?php

namespace ereminmdev\yii2\cropimageupload;

use yii\web\AssetBundle;

class CropImageUploadAsset extends AssetBundle
{
    public $sourcePath = '@vendor/ereminmdev/yii2-cropimageupload/assets';

    public $js = [
        YII_DEBUG ? 'jcrop/js/Jcrop.js' : 'jcrop/js/Jcrop.min.js',
        'cropImageUpload.js',
    ];

    public $css = [
        YII_DEBUG ? 'jcrop/css/Jcrop.css' : 'jcrop/css/Jcrop.min.css',
        'cropImageUpload.css',
    ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\web\JqueryAsset',
    ];
}
