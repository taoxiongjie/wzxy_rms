$(document).ready(function () {
    escape2Html(str);
    // 判断图片说明
    var n=$('#main-body p').length;
    for(var m=0;m<n;m++){
        var text=$('#main-body p').eq(m);
        if(text.html().indexOf('img')!=-1){
            $(text).css({
                'color':'#a5a1a1',
                'font-size':'13px',
                'text-align':'center',
                'text-indent':'0'
            })
        }
    }
})
// 字符转义
function escape2Html(str,str1) {
    var str=$('#abstract').html();
    str=str.replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&amp;nbsp;/g,"").replace(/&amp;gt;/g,'>');
    $('#abstract').html(str);
    var str1=$('#main-body').html();
    str1=str1.replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&amp;nbsp;/g,"").replace(/&amp;gt;/g,'>');
    $('#main-body').html(str1);
}