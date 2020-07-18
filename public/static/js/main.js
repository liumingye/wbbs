$('#mobile-nav').click(function () {
    $('.nav-list').toggleClass('nav-show');
});
/* 获取HTML */
function htmlget(url, dom, fuc, error) {
    $.ajax({
        url: url,
        type: 'POST',
        success: function (res) {
            if (res.code == 1) {
                dom.append(res.data);
                // 加载更多
                bindLoadList(dom);
                // 绑定图片事件
                picctrl();
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
};
function bindLoadList() {
    $(".load-list").click(function () {
        var that = $(this);
        var dom = $(that.data('dom'));
        var text = that.text();
        that.text('加载中...').css('pointer-events', 'none');
        var href = that.attr("href");
        htmlget(href, dom, function () {
            that.remove();
        }, function () {
            that.text(text).css('pointer-events', '');
        });
        return false;
    });
};
/* 打开链接 */
function openUrl(url) {
    if ($("#openUrl").length === 0) {
        $("body").append('<a target="_blank" id="openUrl"></a>');
    }
    var a = $("#openUrl");
    a.attr("href", url);
    a[0].click();
};
/* 文章图片 */
function picctrl() {
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
}
