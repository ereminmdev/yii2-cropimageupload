$.fn.cropImageUpload = function (method) {
    var data = $(this).data('ciu');
    if (data) {
        if (typeof data[method] == 'function') {
            data[method].apply(this, Array.prototype.slice.call(arguments, 1));
        }
        return $(this);
    }

    var ciu = function (_obj, config) {
        _obj.on('change', function () {
            var file = this.files[0];
            var fr = new FileReader();
            fr.onload = function () {
                _obj.cropImageUpload('image', fr.result);
            };
            fr.readAsDataURL(file);
            return false;
        });

        var _crop_store = config.crop_id ? $('#' + config.crop_id) : (config.is_crop_prev ? _obj.prev() : null);
        var _image_store = $('<div>', {'class': 'crop-image-upload-container'}).insertAfter(_obj);

        if (config.crop_class) {
            _image_store.addClass(config.crop_class);
        }

        this.image = function (src, first_time) {
            _image_store.html('<img>');
            $('img', _image_store).attr('src', (config.url && first_time) ? config.url + '/' + src : src);
            if (first_time) $('img', _image_store).hide();

            if (config.crop_value || !first_time) {
                var $cropImage = $('img', _image_store);
                var cropImageW = $cropImage.width();
                var cropImageH = $cropImage.height();

                var crop_value = '';
                if (config.crop_value) {
                    crop_value = config.crop_value;
                }
                if (crop_value == '') {
                    crop_value = ('0-0-100-100');
                }

                function percent(val, main) {
                    return Number((val * 100 / main).toFixed(2));
                }

                var storeCropParams = function (c) {
                    if (_crop_store) {
                        _crop_store.val(percent(c.x, cropImageW) + '-' + percent(c.y, cropImageH) + '-' + percent(c.x2, cropImageW) + '-' + percent(c.y2, cropImageH));
                    }
                };

                var cropParams = config.clientOptions ? config.clientOptions : {};

                if (config.ratio) {
                    cropParams.aspectRatio = config.ratio;
                }

                var pp = crop_value.split('-');
                pp[0] = pp[0] * cropImageW / 100;
                pp[1] = pp[1] * cropImageH / 100;
                pp[2] = pp[2] * cropImageW / 100;
                pp[3] = pp[3] * cropImageH / 100;
                cropParams.setSelect = pp;

                $cropImage.Jcrop(cropParams);
                $cropImage.Jcrop('api').container.on('cropmove', function (e, s, c) {
                    storeCropParams(c);
                });
            }
        };

        if (_obj.is('[value]') && _obj.attr('value') != '') {
            this.image(_obj.attr('value'), true);
            //hack for Yii to send old file name for validation
            if (config.crop_id && _obj.prev().attr('name') == _obj.attr('name')) {
                _obj.prev().val(_obj.attr('value'));
            }
        }
    };

    $(this).data('ciu', new ciu($(this), method));

    return $(this);
};
