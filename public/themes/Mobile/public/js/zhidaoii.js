$(document).ready(function() {
        //悬浮显示下拉列表
        $(".am-dropdown").hover(function() {
            $(this).children("ul").stop().fadeTo(1, 1);
        }, function() {
            $(this).children("ul").stop().fadeOut(200);
        });
        $(".am-form-select").hover(function() {
            $(".am-selected-content").stop().fadeTo(1, 1);
        }, function() {
            $(".am-selected-content").stop().fadeOut(200);
        });
        // 评论数量
        var sc=$('#comment-list ul li').length;
        $('.detail-comments .am-badge-success').text(sc);
    })
    // 返回顶部按钮
backTop = function(btnId) {
    var btn = document.getElementById(btnId);
    var d = document.documentElement;
    var b = document.body;
    window.onscroll = set;
    btn.onclick = function() {
        btn.style.display = "none";
        window.onscroll = null;
        this.timer = setInterval(function() {
            d.scrollTop -= Math.ceil((d.scrollTop + b.scrollTop) * 0.1);
            b.scrollTop -= Math.ceil((d.scrollTop + b.scrollTop) * 0.1);
            if ((d.scrollTop + b.scrollTop) == 0) clearInterval(btn.timer, window.onscroll = set);
        }, 10);
    };

    function set() {
        btn.style.display = (d.scrollTop + b.scrollTop > 100) ? 'block' : "none"
    }
};
backTop('gotop');

// 平滑滚动到锚点
function zdscroll(id, offset) {
    $('html, body').animate({ scrollTop: $(id).offset().top - offset }, 10);
}

//url改变后头部导航样式
var navurl = location.href.split(/\//)[3];

function addnavclass(artr) {
    $("#home-mainnav li").removeClass("am-active").each(function() {
        if ($(this).attr("name") == artr) {
            $(this).addClass("am-active");
        }
    });
}
switch (navurl) {
    //首页
    case "":
        addnavclass("home");
        break;
        //企业数据库
    case "c":
        addnavclass("c");
        break;
        //发现
    case "news":
        addnavclass("news");
        break;
        //客户管理
    case "manage":
        addnavclass('manage');
        break;
}
//退出登录
function clearCookie() {

    $.cookie("goblet-session-id", null, {
        path: "/"
    });
    $.cookie("goblet-session-id_signed", null, {
        path: "/"
    });
    location.href = "/";
}

function login() {
    url = window.location.href;
    url = url.substr(7, url.length);
    index = url.indexOf("/")
    if (index >= 0) {
        url = url.substr(url.indexOf("/"), url.length);
    } else {
        url = "/"
    }
    if (url == "/user/new") {
        $("#loginButten").attr('href', '/user?fromUrl=/')
    } else {
        $("#loginButten").attr('href', '/user?fromUrl=' + url)
    }
}
// 后台取消删除
function cancel() {
    $('#delete_things').hide();
    $('.am-dimmer').hide();
}
// 标签搜索
// 首页标签搜索
$('#searchtype li').click(function() {
        $('#searchtype li').removeClass('active');
        $(this).addClass('active');
        var name = $(this).children('a').attr('name');
        if (name == "tags") {
            $('#search-navform #submit-form').show();
            $('#search-navform #sub-form').hide();
            $('.search-form-container .am-input-group-secondary').hide();

        } else if (name == "subtag") {
            $('#search-navform #submit-form').hide();
            $('#search-navform #sub-form').show();
            $('.search-form-container .am-input-group-secondary').hide();
        } else {
            $('.search-form-container #sub-form').hide();
            $('#search-navform #submit-form').hide();
            $('.search-form-container .am-input-group-secondary').show();
        }
    })
    // 其他页面头部搜索
$('#search-navform select').change(function() {
    var text = $('select option:selected').val();
    if (text == "tags") {
        $('#search-navform form').hide();
        $('#search-navform #submit-form').show();
        $('.am-topbar-right select').css('margin-right', '-19px');
    } else if (text == "subtag") {
        $('#search-navform form').hide();
        $('#search-navform #sub-form').show();
        $('.am-topbar-right select').css('margin-right', '-19px');
    } else {
        $('#search-navform form').hide();
        $('#search-form').show();
        $('.am-topbar-right select').css('margin-right', '-18px');
    }
})
$('#submit-form .tag-button').click(function() {
    var name = $('select option:selected').val();
    if (name == "tags") {
        var con = $('#tagname').val();
        window.location.href = "/c?tag=" + con;
    }
})
// 评论框相关
$('.reply').click(function(){
    $('.comment-reply-form').fadeOut();
    $(this).parent().siblings('#reply-content').fadeIn();
})
$('.cancel_comment').click(function(){
    $(this).closest('#reply-content').fadeOut();
})
// 评论公司或研报
function commentreply(){
    var commentform=$('form#comment');
    var ctype=$('#ctype').val();
    if($('#id_comment').val()==""){
        $('.msg').show();
    }else{
        $.ajax({
        type: 'POST',
        url: "/comment/comment.json?ctype="+ctype,
        data: commentform.serialize(),
        success: function(response) {
            location.reload();
        },
        error: function(response) {
            alert(response.responseText);
        }
       });
    }
}
// 反馈意见
function commentfankui(){
    var commentform=$('form#comment');
    if($('#id_comment').val()==""){
        $('.msg').show();
    }else{
        $.ajax({
        type: 'POST',
        url: "/comment/comment.json?ctype=f",
        data: commentform.serialize(),
        success: function(response) {
            $('.msg').hide();
            $('#msg').show();
        },
        error: function(response) {
            alert(response.responseText);
        }
       });
    }
}
// 回复其他评论
function reply(){
    var commentform=$('form#reply-content');
    var company_id=$('#company_id').val();
    var ctype=$('#ctype').val();
    if($('.replied_to').val()==""){
        $('#msg').show();
    }else{
        $.ajax({
        type: 'POST',
        url: "/comment/comment.json?ctype="+ctype+"&toid="+company_id,
        data: commentform.serialize(),
        success: function(response) {
            location.reload();
        },
        error: function(response) {
            alert(response.responseText);
        }
       });
    }
}