# yii2-cropimageupload

Image upload for Yii framework.

This widget depend on:
- https://github.com/mohorev/yii2-upload-behavior
- http://jcrop.org

## Install

``composer require ereminmdev/yii-cropimageupload``

## Use

```
public function behaviors()
{
    return [
        ...
        'avatar' => [
            'class' => CropImageUploadBehavior::className(),
            'attribute' => 'avatar',
            'scenarios' => ['create', 'update'],
            'placeholder' => '@app/modules/user/assets/images/avatar.jpg',
            'path' => '@webroot/upload/avatar/{id}',
            'url' => '@web/upload/avatar/{id}',
            'thumbs' => [
                'thumb' => ['width' => 42, 'height' => 42, 'mode' => ManipulatorInterface::THUMBNAIL_OUTBOUND],
                'preview' => ['width' => 200, 'height' => 200, 'mode' => ManipulatorInterface::THUMBNAIL_OUTBOUND],
            ],
            'ratio' => 1,
            'crop_field' => '',
            'cropped_field' => 'avatar',
        ],
    ];
}
```

View file:

```php
<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
    <?= $form->field($model, 'photo')->widget(CropImageUploadWidget::className()) ?>
    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
    </div>
<?php ActiveForm::end(); ?>
```
