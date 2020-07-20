/* extends */
Date.prototype.format = function (fmt) {
    var o = {
        "M+": this.getMonth() + 1,
        "d+": this.getDate(),
        "h+": this.getHours(),
        "m+": this.getMinutes(),
        "s+": this.getSeconds(),
        "q+": Math.floor((this.getMonth() + 3) / 3),
        "S": this.getMilliseconds()
    }
    if (/(y+)/.test(fmt))
        fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt))
            fmt = fmt.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
    return fmt;
}
$.fn.extend({
    rotate: function (angle, click, whence) {
        var p = this.get(0);
        if (!whence) {
            p.angle = ((p.angle == undefined ? 0 : p.angle) + angle) % 360;
        } else {
            p.angle = angle;
        }
        if (p.angle >= 0) {
            var rotation = Math.PI * p.angle / 180;
        } else {
            var rotation = Math.PI * (360 + p.angle) / 180;
        }
        var costheta = Math.cos(rotation);
        var sintheta = Math.sin(rotation);
        var canvas = document.createElement('canvas');
        if (!p.oImage) {
            canvas.oImage = new Image();
            canvas.oImage.src = p.src;
            canvas.oImage.onload = function () {
                onload();
            };
        } else {
            canvas.oImage = p.oImage;
            onload();
        }
        function onload() {
            canvas.style.width = canvas.width = Math.abs(costheta * canvas.oImage.width) + Math.abs(sintheta * canvas.oImage.height);
            canvas.style.height = canvas.height = Math.abs(costheta * canvas.oImage.height) + Math.abs(sintheta * canvas.oImage.width);
            var context = canvas.getContext('2d');
            context.save();
            if (rotation <= Math.PI / 2) {
                context.translate(sintheta * canvas.oImage.height, 0);
            } else if (rotation <= Math.PI) {
                context.translate(canvas.width, -costheta * canvas.oImage.height);
            } else if (rotation <= 1.5 * Math.PI) {
                context.translate(-costheta * canvas.oImage.width, canvas.height);
            } else {
                context.translate(0, -sintheta * canvas.oImage.width);
            }
            context.rotate(rotation);
            context.drawImage(canvas.oImage, 0, 0, canvas.oImage.width, canvas.oImage.height);
            context.restore();
            canvas.className = 'maxImg';
            canvas.angle = p.angle;
            if (click) {
                canvas.onclick = click;
            }
            p.parentNode.replaceChild(canvas, p);
        }
    },
    autoHeight: function () {
        this.each(function () {
            var that = $(this);
            if (!that.attr('_initAdjustHeight')) {
                that.attr('_initAdjustHeight', that.outerHeight());
            }
            _adjustH(this).on('input', function () {
                _adjustH(this);
            });
        });
        /**
         * 重置高度
         * @param {Object} elem
         */
        function _adjustH(elem) {
            var $obj = $(elem);
            return $obj.css({ height: $obj.attr('_initAdjustHeight') }).height(elem.scrollHeight);
        }
    },
    drawImage: function (click) {
        this.rotate(0, click);
    },
    rotateRight: function (angle, click) {
        this.rotate(angle == undefined ? 90 : angle, click);
    },
    rotateLeft: function (angle, click) {
        this.rotate(angle == undefined ? -90 : -angle, click);
    },
    ctrlSubmit: function (fn, thisObj) {
        var obj = thisObj || this;
        var stat = false;
        return this.each(function () {
            $(this).keyup(function (event) {
                if (event.keyCode == 17) {
                    stat = true;
                    setTimeout(function () {
                        stat = false;
                    }, 300);
                }
                if (event.keyCode == 13 && (stat || event.ctrlKey)) {
                    fn.call(obj, event);
                }
            });
        });
    }
});
$.cookie = function (key, value, options) {
    var pluses = /\+/g;
    function decode(s) {
        return this.raw ? s : decodeURIComponent(s);
    }
    function stringifyCookieValue(value) {
        return encode(this.json ? JSON.stringify(value) : String(value));
    }
    function parseCookieValue(s) {
        if (s.indexOf('"') === 0) {
            s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
        }
        try {
            s = decodeURIComponent(s.replace(pluses, ' '));
            return this.json ? JSON.parse(s) : s;
        } catch (e) { }
    }
    function read(s, converter) {
        var value = this.raw ? s : parseCookieValue(s);
        return $.isFunction(converter) ? converter(value) : value;
    }
    this.defaults = {};
    if (value !== undefined && !$.isFunction(value)) {
        options = $.extend({}, this.defaults, options);
        if (typeof options.expires === 'number') {
            var days = options.expires, t = options.expires = new Date();
            t.setTime(+t + days * 864e+5);
        }
        return (document.cookie = [
            encode(key), '=', stringifyCookieValue(value),
            options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
            options.path ? '; path=' + options.path : '',
            options.domain ? '; domain=' + options.domain : '',
            options.secure ? '; secure' : ''
        ].join(''));
    }
    var result = key ? undefined : {};
    var cookies = document.cookie ? document.cookie.split('; ') : [];
    for (var i = 0, l = cookies.length; i < l; i++) {
        var parts = cookies[i].split('=');
        var name = decode(parts.shift());
        var cookie = parts.join('=');
        if (key && key === name) {
            result = read(cookie, value);
            break;
        }
        if (!key && (cookie = read(cookie)) !== undefined) {
            result[name] = cookie;
        }
    }
    return result;
};
/* 功能函数 */
(function () {
    var wbbs = {
        /* 文章图片 */
        picctrl: function () {
            $('.post-image-col:not(".init")').addClass('init').click(function () {
                var that = $(this);
                that.parent().hide();
                var artZoomBox = that.parent().next();
                var src = that.find('img').attr('src');
                var img = $('<img class="maxImg" src="' + src + '">');
                img.drawImage(function () {
                    $(this).parent().prev().find('.hideImg').click();
                });
                artZoomBox.find('.maxImgRow').html(img);
                artZoomBox.show();
                $('.viewImg').attr('href', src);
                $('.hideImg').unbind('click').click(function () {
                    $(this).parent().parent().hide().prev().show();
                });
                $('.imgLeft').unbind('click').click(function () {
                    var img = $(this).parent().next().find('.maxImg');
                    img.rotateLeft(90, function () {
                        $(this).parent().prev().find('.hideImg').click();
                    });
                });
                $('.imgRight').unbind('click').click(function () {
                    var img = $(this).parent().next().find('.maxImg');
                    img.rotateRight(90, function () {
                        $(this).parent().prev().find('.hideImg').click();
                    });
                });
            });
        },
        /* HTML获取 */
        htmlget: function (url, dom, fuc, error) {
            $.ajax({
                url: url,
                type: 'POST',
                success: function (res) {
                    if (res.code == 1) {
                        dom.append(res.data);
                        // 加载更多
                        wbbs.bindLoadList(dom);
                        // 绑定图片事件
                        wbbs.picctrl();
                        // 完成回调
                        fuc && fuc();
                    } else {
                        error && error();
                    }
                },
                error: function () {
                    error && error();
                }
            });
        },
        /* 绑定加载更多 */
        bindLoadList: function () {
            $(".load-list").click(function () {
                var that = $(this);
                var href = that.data("href");
                if (!href) {
                    return;
                }
                var btn = that.find('.btn');
                var text = btn.html();
                btn.text('加载中...').css('pointer-events', 'none');
                wbbs.htmlget(href, $(that.data('dom')), function () {
                    that.remove();
                }, function () {
                    btn.html(text).css('pointer-events', '');
                });
                return false;
            });
        },
        /* 打开链接 */
        openUrl: function (url) {
            if ($("#openUrl").length === 0) {
                $("body").append('<a target="_blank" id="openUrl"></a>');
            }
            var a = $("#openUrl");
            a.attr("href", url);
            a[0].click();
        },
        /* 评论回复 */
        reply: function (coid) {
            var response = $(".comment-respond"),
                input = $("#comment-parent"),
                form = response.find("form"),
                textarea = response.find("textarea");
            if (input.length == 0) {
                form.append('<input type="hidden" name="parent" id="comment-parent">')
            }
            $("#comment-parent").attr("value", coid);
            if ($("#comment-form-place-holder").length == 0) {
                response.before('<div id="comment-form-place-holder"></div>')
            }
            $("#comment-" + coid).append(response);
            $("#cancel-comment-reply-link").show();
            if (textarea.length != 0 && textarea.attr("name") == "text") {
                textarea.focus().val('');
            }
            $('.comment-title').text('向 ' + $("#comment-" + coid).find('.comment-author').eq(0).text() + ' 进行回复');
            return false;
        },
        cancelReply: function () {
            var response = $(".comment-respond");
            $("#cancel-comment-reply-link").hide();
            response.insertBefore("#comment-form-place-holder");
            $('.comment-title').text("发表留言");
            response.find("textarea").val('');
            return false;
        },
        loadReply: function (dom) {
            var that = $(dom);
            var text = that.html();
            that.text('加载中...').css('pointer-events', 'none');
            var url = that.data('href');
            var comment_children = that.parents('.comment-body').find('.comment-children');
            if (comment_children.length == 0) {
                comment_children = $('<div class="comment-list comment-children"></div>').hide();
                that.parent().parents('.comment-body').append(comment_children);
            }
            wbbs.htmlget(url, comment_children, function () {
                comment_children.show();
                that.parent().remove();
            }, function () {
                that.html(text).css('pointer-events', '');
            });
        }
    };
    window.wbbs = wbbs;
})();

$('#mobile-nav').click(function () {
    $('.nav-list').toggleClass('nav-show');
});
