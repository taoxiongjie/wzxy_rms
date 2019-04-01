//榜单列表滚动懒加载
var loadstatus = true;
$(window).scroll(function() {
    var scrollTop = $(this).scrollTop();
    var scrollHeight = $(document).height();
    var windowHeight = $(this).height();
    var allshow = ($("#zdloadall").css("display") == "none");
    if (scrollTop + windowHeight == scrollHeight) {
        if (loadstatus && allshow) {
            loadstatus = false;
            zdlazyload("#subjectlist", '/information/LoadSub.json');
        }
    }
});
var loadtext = '<div class="am_news_load" id="zdlazyloading"><span><i class="am-icon-spinner am-icon-spin"></i> 更多信息</span></div>';
var subjectPageNo = 1;

function zdlazyload(id, thisurl) {
    var url = window.location.href;
    $(id).append(loadtext);
    if (url.indexOf('?tag=') != -1) {
    	url=url.split('SubjectList?');
    	url[1]=decodeURI(url[1]);
        url ="?"+url[1]+"&times=" + subjectPageNo
    } else {
        url = "?times=" + subjectPageNo
    }
    $.ajax({
        url: thisurl + url,
        success: function(result) {
            if (result == "") {
                $("#zdloadall").show();
                $("#zdlazyloading").remove();
            } else {
                $("#zdloadall").hide();
                $("#zdlazyloading").remove();
                var n = result.subs.length;
                for(var i = 0; i < n; i++) {
                    result.subs[i].Created=result.subs[i].Created.split('T');
                    $(id).append('<li class="am-g am-list-item-desced am-list-item-thumbed am-list-item-thumb-left am_list_li"><div class="am-u-sm-3 am-list-thumb am_list_thumb"><a href="/information/Subject?id='
                        + result.subs[i].Id + '"><img width="165" height="auto" class="am_news_list_img" src="http://shuyu-zhidaoii.oss-cn-beijing.aliyuncs.com/'
                        +result.subs[i].FeaturedPath+'"></a></div><div class=" am-u-sm-9 am-list-main am_list_main"><h3 class="am-list-item-hd am_list_title"><a href="/information/Subject?id='
                        +result.subs[i].Id+'" title="'+result.subs[i].Title+'">'+result.subs[i].Title+'</a></h3><div class="am_list_author"><span class="name">'
                        +result.subs[i].Editor+'</span><span class="am_news_time">&nbsp;•&nbsp;<time>'+result.subs[i].Created[0]+'</time><span></div><div class="am-list-item-text am_list_item_text"><div class="abstract">'
                        + result.subs[i].Description + '</div></div></div></li>');
                }
                subjectPageNo++;
            }
            showmes();
        },
        complete: function() {
            loadstatus = true;
        }
    });
}

function showmes() {
    if ($("#subjectlist li").length > 0) {
        $("#zdnone").hide();
    } else {
        $("#zdnone").show();
        $("#zdloadall").hide();
    }
}