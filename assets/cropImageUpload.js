'use strict';

(function ($) {

    // jquery plugin for jcrop (http://jcrop.org)
    $.fn.cropImageUpload = function (options) {
        var defaults = {
            cropInputId: '',
            cropValue: '',
            isCropPrev: true,

            wrapperTag: 'div',
            wrapperAttrs: {
                class: 'crop-image-upload-container'
            },
            wrapperCSS: {},

            imageTag: 'img',
            imageAttrs: {
                class: 'img-responsive'
            },
            imageCSS: {},

            clientOptions: []
        };

        var settings = $.extend(true, {}, defaults, options);

        return this.each(function () {
            var realWidth, realHeight;

            var $input = $(this);
            var $cropInput = (!settings.isCropPrev && (settings.cropInputId !== '')) ? $('#' + settings.cropInputId) : $input.prev();

            var $wrapper = $('<' + settings.wrapperTag + '/>').attr(settings.wrapperAttrs).css(settings.wrapperCSS);
            $wrapper.insertAfter($input);

            // fire when crop coordinates changed
            var updateCropCoordinates = function (coordinates) {
                $cropInput.val(JSON.stringify(coordinates));
            };

            // fire when new image select in input
            var changeImage = function (src) {
                var $image = $('<' + settings.imageTag + '/>')
                    .attr('src', src)
                    .one('load', function () {
                        $image.appendTo($wrapper.empty());

                        realWidth = $image.width();
                        realHeight = $image.height();

                        $image.attr(settings.imageAttrs).css(settings.imageCSS);

                        $image.css({
                            width: (Math.min(realWidth, $wrapper.width()) - 1) + 'px'
                        });

                        var clientOptions = settings.clientOptions;
                        clientOptions.boxWidth = $image.outerWidth();
                        clientOptions.boxHeight = $image.outerHeight();
                        clientOptions.setSelect = $.extend({}, clientOptions.setSelect, [0, 0, realWidth, realHeight]);

                        $image.Jcrop(clientOptions);
                        $image.Jcrop('api').container.on('cropend', function (event, selection, coordinates) {
                            updateCropCoordinates(coordinates);
                        });
                    });
            };

            $input.on('change', function () {
                var file = this.files[0];
                var reader = new FileReader();

                reader.onloadend = function () {
                    changeImage(reader.result);
                };

                if (file) {
                    reader.readAsDataURL(file);
                }
            });
        });
    };

}(jQuery));
