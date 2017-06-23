<?php

namespace ereminmdev\yii2\cropimageupload;

use Yii;
use yii\helpers\Html;
use yii\widgets\InputWidget;

/**
 * CropImageUploadWidget renders a jCrop plugin for image crop.
 * @see http://jcrop.org
 * inspired on https://github.com/karpoff/yii2-crop-image-upload
 */
class CropImageUploadWidget extends InputWidget
{
    /**
     * @var array the options for the jCrop plugin.
     * Please refer to the jCrop Web page for possible options.
     * @see http://jcrop.org/doc/options
     */
    public $clientOptions = [];
    /**
     * @var string crop ratio
     * format is width:height where width and height are both floats
     * if empty and has model, will be got from CropImageUploadBehavior
     */
    public $ratio;
    /**
     * @var string attribute name storing crop value or crop value itself if no model
     * if empty and has model, will be got from CropImageUploadBehavior
     * crop value has topLeftX-topLeftY-width-height format where all variables are float
     * all coordinates are in percents of corresponded image dimension
     */
    public $crop_field;
    /**
     * @var string crop value
     * if empty and has model, will be got from $crop_field of model
     * crop value has topLeftX-topLeftY-width-height format where all variables are float
     * all coordinates are in percents of corresponded image dimension
     */
    public $crop_value;
    /**
     * @var string url where uploaded files are stored
     * if empty and has model, will be got from CropImageUploadBehavior
     */
    public $url;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $form = $this->field->form;
        if (!isset($form->options['enctype'])) {
            $form->options['enctype'] = 'multipart/form-data';
        }

        $this->options['accept'] = 'image/*';
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($this->hasModel()) {
            echo Html::activeFileInput($this->model, $this->attribute, $this->options);

            if (!$this->ratio || !$this->crop_field || !$this->url) {
                foreach ($this->model->getBehaviors() as $beh) {
                    if (!empty($beh->attribute) && $beh->attribute == $this->attribute) {
                        if ($beh instanceof CropImageUploadBehavior) {
                            if (!$this->ratio && $beh->ratio) {
                                $this->ratio = $beh->ratio;
                            }
                            if (!$this->crop_field && $beh->crop_field) {
                                $this->crop_field = $beh->crop_field;
                            }
                            if (!$this->url && $beh->url) {
                                $this->url = $beh->url;
                            }
                            break;
                        }
                    }
                }
            }

            if (!$this->crop_value && $this->crop_field) {
                $this->crop_value = $this->model->{$this->crop_field};
            }
        } else {
            echo Html::fileInput($this->name, $this->value, $this->options);
        }

        $crop_id = false;

        if ($this->crop_field) {
            if ($this->hasModel()) {
                $crop_id = Html::getInputId($this->model, $this->crop_field);
                echo Html::activeHiddenInput($this->model, $this->crop_field, ['value' => $this->crop_value]);
            } else {
                $crop_id = $this->options['id'] . '_' . $this->crop_field;
                echo Html::hiddenInput($this->crop_field, $this->crop_value);
            }
        }

        if ($this->url) {
            $this->url = Yii::getAlias($this->url);
        }

        if ($this->ratio && !isset($this->clientOptions['aspectRatio'])) {
            $this->clientOptions['aspectRatio'] = $this->ratio;
        }

        $jsOptions = [
            'cropValue' => $this->crop_value,
            'cropInputId' => $crop_id,
            'ratio' => $this->ratio,
            'url' => $this->url,
            'clientOptions' => $this->clientOptions,
            'isCropPrev' => ($crop_id || !$this->hasModel()) ? false : true,
        ];

        $this->registerPlugin($jsOptions);
    }

    /**
     * @param array $options
     */
    protected function registerPlugin($options)
    {
        $view = $this->getView();
        CropImageUploadAsset::register($view);

        $id = $this->options['id'];

        $view->registerJs("jQuery('#{$id}').cropImageUpload(" . json_encode($options) . ");");
    }
}
