var Partner = {
	init:function(){
		var openids = branch_openids.split(",");
    	var max =  Math.ceil(openids.length / page_count);
    	for (var i = 0; i < max; i++) {
    	  var slice = openids.slice(i*page_count,(i+1)*page_count);
    	  var openid = slice.join(",");
    	  Info.Page(Partner.PageCallback,openid,true);
    	}
	},
	PageCallback:function(obj){
	  	var html = "<div class='user_list' style='display:block; line-height:30px;margin-bottom:10px;'><img src='"+obj['headimgurl']+"' style='height:30px;'><span style='margin-right:20px;'>"+obj['nickname']+"</span></div>";
      	var old = $('#container').html();
      	$('#container').html(old + html);
	}
}