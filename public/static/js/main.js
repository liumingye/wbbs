$('#mobile-nav').click(function () {
    $('.nav-list').toggleClass('nav-show');
});

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
            fuc && fuc();
        }
    });
}

function picctrl(){
    $('.post-image-col:not(".init")').click(function(){
        var that = $(this);
        that.parent().hide();
        var artZoomBox = that.parent().next();
        artZoomBox.find('.maxImgLink img').attr('src',that.find('img').attr('src'));
        artZoomBox.show();
        
    });
}
