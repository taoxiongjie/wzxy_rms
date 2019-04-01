/**
 * Created by zhidaoii-17 on 2017/2/24.
 */
//url改变后头部导航样式
var navurl = location.href.split(/\//)[3];

function addnavclass(artr) {
    if (artr == "home") {
        $('.home').hide();
        $('.home-blue').show();
    } else if (artr == "c") {
        $('.company').hide();
        $('.company-blue').show();
    } else if (artr == "news") {
        $('.news').hide();
        $('.news-blue').show();
    } else {
        $('.user').hide();
        $('.user-blue').show();
    }
    $("#footernav ul li").removeClass("active").each(function() {
        if ($(this).attr("name") == artr) {
            $(this).addClass("active");
        }
    });
}
if (navurl.indexOf('user?fromUrl=') != -1 || navurl.indexOf('user?errorMsg=') != -1) {
    addnavclass('user');
}
if(navurl.indexOf('usermsg') != -1){
    addnavclass('user');
} else if (navurl.indexOf('c?') != -1) {
    addnavclass('c');
} else if (navurl.indexOf('userpass') != -1 || navurl.indexOf('userfocus') != -1) {
    addnavclass('user');
} else {
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
        case "information":
            addnavclass("news");
            break;
            //用户中心
        case "user":
            addnavclass('user');
            break;
        case "userdetail":
            addnavclass('user');
            break;
        case "introduce":
            addnavclass('user');
            break;
    }
}
$(document).ready(function() {
        //判断是否为APP请求
        if (navigator.userAgent.indexOf("zhidaoii_webview") > 0) {
            $('#footernav').remove();
        }
        // 评论数量
        var sc=$('#comment-list ul li').length;
        $('.am-badge-success').text(sc);
    })
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
// 首页标签搜索切换
$('#searchtype li').click(function() {
        $('#searchtype li').removeClass('active');
        $(this).addClass('active');
        var name = $(this).children('a').attr('name');
        if (name == "tags") {
            $('#search-form').hide();
            $('#sub-form').hide();
            $('#submit-form').show();

        }else if (name == "subtag") {
            $('#search-form').hide();
            $('#sub-form').show();
            $('#submit-form').hide();
        } else {
            $('#submit-form').hide();
            $('#sub-form').hide();
            $('#search-form').show();
        }
    })
    // 其他页面头部搜索
$('.head-search .am-form-select select').change(function() {
    var text = $('select option:selected').val();
    if (text == "tags") {
        $('#search-form').hide();
        $('#sub-form').hide();
        $('#submit-form').show();
    }else if (text == "subtag") {
        $('#search-form').hide();
        $('#sub-form').show();
        $('#submit-form').hide();
    } else {
        $('#submit-form').hide();
        $('#sub-form').hide();
        $('#search-form').show();
    }
})
$('#submit-form .tag-button').click(function() {
    var con = $('#tagname').val();
    window.location.href = "/c?tag=" + con;
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