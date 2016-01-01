var Count = {
	init:function(){
	  var openids = branch_openids.split(",");
    var max =  Math.ceil(openids.length / page_count);
    for (var i = 0; i < max; i++) {
      var slice = openids.slice(i*page_count,(i+1)*page_count);
      var openid = slice.join(",");
      Info.hyCount(Count.hyCountCallback,openid);
      Info.hyxd(Count.hyxdCallback,openid,true);
    }
	},
  hyPageCallback:function(str,flag){
    Info.hyxd(Count.hyxdCallback,str,flag);
    Info.hyCount(Count.hyCountCallback,str);
  },
  hyCountCallback:function(openid,sum,count){
    Info.hyPage(Count.hyPageCallback,openid,1,true,sum);//获得间接会员的openid
  },
  hyxdCallback:function(flag,openid,count){
    order_sum += count;
    $("#orderCount").text(order_sum);

    var max = Math.ceil(count / page_count);
    Count.orderPage(Count.orderPageCallback,openid,1,max);
  },
  orderPage:function(callback,openid,page,max){
    $.ajax({
        url:'/index.php?s=/addon/Branch/Branch/orderPage',
        dataType: 'json',
        type: "POST",
        data:{
          id:openid,
          page:page
        },
        success: function(rp) {
          if (rp.state == 1) {
            if(rp.fee)
              callback(rp.fee);
            if(page < max)
              Count.orderPage(callback,openid,page+1,max);
          } else {
            alert('计算营业额失败，请刷新该页面');
          }
        }
    });
  },
  orderPageCallback:function(fee){
    fee_sum += new Number(fee);
    $("#trunover").text((fee_sum/100).toFixed(2));
  },
}