<?php

namespace ereminmdev\yii2\cropimageupload;

use yii\web\AssetBundle;

class CropImageUploadAsset extends AssetBundle
{
    public $sourcePath = '@vendor/ereminmdev/yii2-cropimageupload/assets';

    public $js = [
        'cropImageUpload.js',
    ];

    public $css = [
        'cropImageUpload.css',
    ];

    public $depends = [
        'ereminmdev\yii2\cropimageupload\JCropAsset',
    ];
}
