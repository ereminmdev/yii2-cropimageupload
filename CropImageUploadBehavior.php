<?php

namespace ereminmdev\yii2\cropimageupload;

use Imagine\Image\Box;
use Imagine\Image\Point;
use mohorev\file\UploadImageBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
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
     * @param string $attribute
     * @param string|false $thumb
     * @param string $placeholderUrl
     * @return string
     */
    public function getImageUrl($attribute, $thumb = 'thumb', $placeholderUrl = '')
    {
        $thumb = in_array($thumb, array_keys($this->thumbs)) ? $thumb : false;

        $behavior = $this->findImageBehavior($attribute);
        if ($behavior !== null) {
            if ($thumb !== false) {
                return $behavior->getThumbUploadUrl($attribute, $thumb);
            } else {
                return $behavior->getUploadUrl($attribute);
            }
        } else {
            if ($thumb !== false) {
                return $this->getPlaceholderUrl($thumb);
            } else {
                return $placeholderUrl;
            }
        }
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
        $size = $image->getSize();

        $crop = json_decode($this->crop_value, true);
        $crop = ArrayHelper::merge(['x' => 0, 'y' => 0, 'x2' => $size->getWidth(), 'y2' => $size->getHeight()], (array)$crop);

        $image->crop(new Point($crop['x'], $crop['y']), new Box($crop['x2'] - $crop['x'], $crop['y2'] - $crop['y']))->save($save_path);
    }

    /**
     * @param string $filename
     * @return string
     */
    protected function getCropFileName($filename)
    {
        return uniqid() . '_' . $filename;
    }

    /**
     * @param string $attribute
     * @return self|null
     */
    public function findImageBehavior($attribute)
    {
        if ($this->attribute == $attribute) {
            return $this;
        } else {
            $owner = $this->owner;
            foreach ($owner->getBehaviors() as $behavior) {
                if (($behavior instanceof self) && ($behavior->attribute == $attribute)) {
                    return $behavior;
                }
            }
        }
        return null;
    }
}
