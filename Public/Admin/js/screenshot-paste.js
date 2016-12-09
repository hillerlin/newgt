(function ($) {

    $.fn.screenshotPaste = function (options) {
        var me = this;

        if (typeof options == 'string') {
            var method = $.fn.screenshotPaste.methods[options];

            if (method) {
                return method();
            } else {
                return;
            }
        }

        var defaults = {
            uploadUrl: '',
            delUrl: '',
            imgContainer: '', //预览图片的容器
            imgHeight: 100       //预览图片的默认高度
        };

        options = $.extend(defaults, options);

        var imgReader = function (item) {
            var file = item.getAsFile();
            var reader = new FileReader();

            reader.readAsDataURL(file);
            reader.onload = function (e) {
                var xhr = new XMLHttpRequest(),
                fd = new FormData();
                xhr.open('POST', options.uploadUrl, true);
//                xhr.responseType = 'json';
//                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
//                xhr.setRequestHeader("Content-Type","multipart/form-data");
                xhr.onload = function ()
                {
                    var img = new Image();

                    $(img).css({height: options.imgHeight});
                    var src = JSON.parse(xhr.responseText);
                    var i = $(options.imgContainer + '> li').length + 1;
                    debugger;
                    //document.getElementById("img_puth").value = img.src;
                    var newImg = '<li id="1"><input type="hidden" name="voucher['+ i +'][path]" value="' + src + '" /><div class="thumb-list-pics"><a href="javascript:void(0);"><img src="' + src + '" alt=""/></a></div>\n\
                    <a href="/Admin/FinanceFlow/remove" data-data=\'' + src + '\' class="del" title="删除">X</a></li>';
                    $(document).find(options.imgContainer).prepend(newImg);
                };

                // this.result得到图片的base64 (可以用作即时显示)
                fd.append('file', e.target.result);
//                that.innerHTML = '<img src="'+this.result+'" alt=""/>';
                xhr.send(fd);
            };
        };
        //事件注册
        $(me).on('paste', function (e) {
            var clipboardData = e.originalEvent.clipboardData;
            var items, item, types;
            
            if (clipboardData) {
                items = clipboardData.items;

                if (!items) {
                    return;
                }

                item = items[0];
                types = clipboardData.types || [];

                for (var i = 0; i < types.length; i++) {
                    if (types[i] === 'Files') {
                        item = items[i];
                        break;
                    }
                }

                if (item && item.kind === 'file' && item.type.match(/^image\//i)) {
                    imgReader(item);
                }
            }
        });
        $(options.imgContainer).on('click', '.del', function(e) {
            me = $(this);
            var file_path = $(this).attr('data-data');
            $.getJSON(options.delUrl, {'file_path': file_path}, function (json) {
                if (json.statusCode === 200) {
                    var li = me.parent();
//                    $(this).parent().remove();
                    li.remove();
                } else {
                    alert('删除失败');
                }
            });
            return false;
        })

        $.fn.screenshotPaste.methods = {
            getImgData: function () {
                var src = $(document).find(options.imgContainer).find('img').attr('src');

                if (src == undefined) {
                    src = '';
                }

                return src;
            }
        };
    };
})(jQuery);