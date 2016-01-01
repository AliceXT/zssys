var Spread = {
	init:function(str){
		if(str == "#zjcontainer"){
			container = $(str);
			// 直接会员
			Info.zjhy(Spread.zjhyCallback,true);
		}else{
			container = $(str);
			// 直接会员
			Info.zjhy(Spread.zjhyCallback,false);
		}
		
	},
	zjhyCallback:function(flag,sum,count){
		if(container.attr("id") == "zjcontainer")
      $("#firbtn").text("一级下线("+count+")");
      Info.hyPage(Spread.hyPageCallback,openid,1,flag,sum);
          
    },
    hyPageCallback:function(str,flag){
        if(!flag){
          Info.hyCount(Spread.hyCountCallback,str);
        }else{
            Info.Page(Spread.PageCallback,str,flag);
        }
    },
    hyCountCallback:function(openid,sum,count){
    	jjhy = jjhy + count;
    	$("#secbtn").text("二级下线("+jjhy+")");
        Info.hyPage(Spread.hyPageCallback,openid,1,true,sum);//获得间接会员的openid
    },
    PageCallback:function(obj){
      var html = '<dl><dt><img src="';
      var html = html + obj['headimgurl']+'"></dt><dd>';
      var html = html + obj['nickname']+'</dd><dd>';
      var html = html + '<a href="/index.php?s=/addon/Shop/User/more/id/'+obj['openid']+'.html">'+obj['openid']+'</a></dd></dl>';
      var old = container.html();
      container.html(old + html);
    },
}