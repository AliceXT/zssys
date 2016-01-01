var Order = {
	init:function(){
		if(type == "zhijie"){
			Info.zjhy(Order.zjhyCallback,true);
		}else if(type == "jianjie"){
			Info.zjhy(Order.zjhyCallback,false);
		}
	},
	zjhyCallback:function(flag,sum){
		Info.hyPage(Order.hyPageCallback,openid,1,flag,sum);
	},
	hyPageCallback:function(str,flag){
		if(flag){
			Info.hyxd(Order.hyxdCallback,str);
		}else{
			Info.hyCount(Order.hyCountCallback,str);
		}
	},
	hyxdCallback:function(flag,openid,count){
		var sum = Math.ceil(count / page_count);
		if(count != 0){
			Order.OrderPage(Order.OrderPageCallback,openid,1,sum);
		}
	},
	hyCountCallback:function(openid,sum){
		Info.hyPage(Order.hyPageCallback,openid,1,true,sum);
	},
	OrderPage:function(callback,openid,page,max){
		$.ajax({
            url: '/index.php?s=/addon/Shop/Order/Page.html',
            dataType: 'json',
            type: "POST",
            data:{
                id:openid,
                page:page,
                type:type
            },
            success: function(rp) {
                if (rp.state == 1) {
                    var arr = rp.info;
                    if(arr == null)
                    	return ;
                    for (var i = arr.length - 1; i >= 0; i--) {
                      // Info.addLine(arr[i]);
                      callback(arr[i]);
                    }
                    if(page < max)
                      Info.Page(callback,openid,page+1,flag,max);
                } else {
                    alert('获取会员列表失败');
                }
            }
        });
	},
	OrderPageCallback:function(order){
		var deduct = order.deduct*100;
		total = total + deduct;
		$("#total").text("合计提成("+total/100+"元)");
		var html = '<dl><dd>'+order.id+'</dd><dt><a href="/index.php?s=/addon/Shop/Shop/detail/id/';
		var html = html + order.pid + '.html">'+order.title+'</a></dt><dd>'+order.total_fee+'元</dd>';
		var html = html + '<dd><u>'+order.deduct+'元</u></dd>';
       	var html = html + '<dd>'+order.owner+'</dd><dd><p>'+order.timestamp+'</p></dd></dl>';
		var old = container.html();
		container.html(old + html);
	},
}