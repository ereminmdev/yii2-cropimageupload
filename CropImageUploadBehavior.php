<?php

namespace ereminmdev\yii2\cropimageupload;

use Imagine\Image\Box;
use Imagine\Image\Point;
use mongosoft\file\UploadImageBehavior;
use yii\db\ActiveRecord;
use yii\imagine\Image;
use yii\web\UploadedFile;

class CropImageUploadBehavior extends UploadImageBehavior
{
    /**
     * @var string attribute that stores crop value
     * if empty, crop value is got from attribute field
     */
    public $crop_field;
    /**
     * @var string attribute that stores cropped image name
     */
    public $cropped_field;
    /**
     * @var string crop ratio (needed width / needed height)
     */
    public $ratio;
    /**
     * @var array the thumbnail profiles
     * - `width`
     * - `height`
     * - `quality`
     */
    public $thumbs = [];

    protected $crop_value;
    protected $crop_changed;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->cropped_field = $this->cropped_field !== null ? $this->cropped_field : $this->attribute;
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        /** @var ActiveRecord $model */
        $model = $this->owner;

        if (empty($this->crop_field)) {
            $this->crop_value = $model->getAttribute($this->attribute);
            $this->crop_changed = !empty($this->crop_value);
        } else {
            $this->crop_value = $model->getAttribute($this->crop_field);
            $this->crop_changed = $model->isAttributeChanged($this->crop_field);
        }

        parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function beforeSave()
    {
        parent::beforeSave();

        /** @var ActiveRecord $model */
        $model = $this->owner;

        $this->cropped_field = $model->getAttribute($this->attribute) instanceof UploadedFile ? $this->cropped_field : '';

        if ($this->crop_changed && !empty($this->cropped_field)) {
            $this->delete($this->cropped_field, true);

            $name = $model->getAttribute($this->attribute);

            if (empty($name)) {
                $model->setAttribute($this->attribute, $model->getOldAttribute($this->attribute));
            }

            $model->setAttribute($this->cropped_field, $this->getCropFileName($model->getAttribute($this->attribute)));
        }
    }

    /**
     * @inheritdoc
     */
    public function afterUpload()
    {
        if ($this->crop_changed) {
            $this->createCrop();
        }

        parent::afterUpload();
    }

    /**
     * Crop uploaded image
     */
    protected function createCrop()
    {
        $path = $this->getUploadPath($this->attribute);
        $save_path = empty($this->cropped_field) ? $path : $this->getUploadPath($this->cropped_field);

        // Fix error "PHP GD Allowed memory size exhausted".
        ini_set('memory_limit', '512M');

        $image = Image::getImagine()->open($path);
        $crop = explode('-', $this->crop_value);

        $size = $image->getSize();
        foreach ($crop as $ind => $cr) {
            $crop[$ind] = round($crop[$ind] * ($ind % 2 == 0 ? $size->getWidth() : $size->getHeight()) / 100);
        }

        $image->crop(new Point($crop[0], $crop[1]), new Box($crop[2] - $crop[0], $crop[3] - $crop[1]))->save($save_path);
    }

    /**
     * @param string $filename
     * @return string
     */
    protected function getCropFileName($filename)
    {
        return uniqid() . '_' . $filename;
    }
}
