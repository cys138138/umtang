void 0===window.pageTemplate&&(window.pageTemplate={}),window.pageTemplate.umtPageNum="<style>.umt-page-num{text-align:right;padding:20px 0;user-select:none}.umt-page-num>.prev-page:after{border-width:5px 6px 5px 0;border-color:transparent #909090 transparent transparent}.umt-page-num>.next-page:after{border-width:5px 0 5px 6px;border-color:transparent transparent transparent #909090}.umt-page-num>.next-page:after,.umt-page-num>.prev-page:after{content:\"\";display:inline-block;border-style:solid}.umt-page-num>.page-num{display:inline-block;text-align:right;min-width:30px}.umt-page-num>.page-num+.page-num{text-align:left}.umt-page-num>input{width:50px}.umt-page-num>a,.umt-page-num>input{display:inline-block;line-height:30px;margin-left:5px;padding:0 10px;border:1px solid #e7e7ea;border-radius:2px}.umt-page-num>a.hide,.umt-page-num>input.hide{visibility:hidden}@media screen and (max-width:640px){.umt-page-num{padding:10px;border-top:1px solid #eee;background-color:#fff}}</style><div class=\"umt-page-num hide\"><a href=javascript: class=\"J-prev-page prev-page\"></a> <span class=\"J-current page-num\">1</span> / <span class=\"J-total page-num\">1</span> <a href=javascript: class=\"J-next-page next-page\"></a> <input type=number min=1 placeholder=页码 title=页码> <a href=javascript: class=J-jump-page>跳转</a></div>";window.UmtPageNum={EVENT_PAGE_CHANGE:"event_change",build:function(t){var e=window.pageTemplate.umtPageNum,n=$(e),i=n.find(".J-current"),u=n.find(".J-total"),a=function(t,e){var a=u.text();null==t&&(t=parseInt(i.text())+e),t<1||a<t||(UmtPageNum.update(n,t,a),n.triggerHandler(UmtPageNum.EVENT_PAGE_CHANGE,t))};return n.find(".J-prev-page").click(function(){a(null,-1)}),n.find(".J-next-page").click(function(){a(null,1)}),n.find(".J-jump-page").click(function(){var t,e=n.find("input"),i=parseInt(e.val());return 0==new RegExp("\\d+").test(i)?t="请输入正确页码":(i<-1||u.text()<i)&&(t="页码超出界限"),t?(UNotice.show(t,-1),void e.focus()):void a(i)}),n},update:function(t,e,n){null==n?n=t.find(".J-total").text():t.find(".J-total").text(n),t.find(".J-current").text(e),t.find(".J-prev-page").toggleClass("hide",1==e),t.find(".J-next-page").toggleClass("hide",e==n),t.toggleClass("hide",n<=1)},getCurrentPage:function(t){return t.find(".J-current").text()}};