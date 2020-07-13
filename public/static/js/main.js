$('#mobile-nav').click(function () {
    $('.nav-list').toggleClass('nav-show');
});
/* 获取HTML */
function htmlget(url, dom, fuc) {
    $.ajax({
        url: url,
        type: 'GET',
        success: function (res) {
            dom.append(res);
            // 加载更多
            $(".load-list").click(function () {
                var href = $(this).attr("href");
                var tmp = $(".load-list").css('opacity', 0);
                htmlget(href, dom, function () {
                    tmp.remove();
                });
                return false;
            });
            // 绑定图片事件
            picctrl();
            // 完成回调
            fuc && fuc();
        }
    });
}
/* 打开链接 */
function openUrl(url) {
    if ($("#openUrl").length === 0) {
        $("body").append('<a target="_blank" id="openUrl"></a>');
    }
    var a = $("#openUrl");
    a.attr("href", url);
    a[0].click();
};
/* 将translate内的角度转为数值 */
function getmatrix(deg) {
    try {
        var deg = deg.split('(')[1].split(')')[0].split(',');
        var aa = Math.round(180 * Math.asin(deg[0]) / Math.PI);
        var bb = Math.round(180 * Math.acos(deg[1]) / Math.PI);
        var cc = Math.round(180 * Math.asin(deg[2]) / Math.PI);
        var dd = Math.round(180 * Math.acos(deg[3]) / Math.PI);
        var deg = 0;
        if (aa == bb || -aa == bb) {
            deg = dd;
        } else if (-aa + bb == 180) {
            deg = 180 + cc;
        } else if (aa + bb == 180) {
            deg = 360 - cc || 360 - dd;
        }
        return deg >= 360 ? 0 : deg;
    } catch (e) {
        return 0;
    }
}
/* 文章图片 */
function picctrl() {
    $('.post-image-col:not(".init")').addClass('init').click(function () {
        var that = $(this);
        that.parent().hide();
        var artZoomBox = that.parent().next();
        var img = $('<img class="maxImg" src="' + that.find('img').attr('src') + '">');
        img.drawImage(function () {
            $(this).parent().prev().find('.hideImg').click();
        });
        artZoomBox.find('.maxImgRow').html(img);
        artZoomBox.show();

        $('.hideImg').unbind('click').click(function () {
            $(this).parent().parent().hide().prev().show();
        });
        $('.viewImg').unbind('click').click(function () {
            openUrl($(this).parent().next().find('img').attr('src'));
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
}
