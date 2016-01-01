var Info = {
	initInfo: function(){
		if(type == "zhijie"){
    	  Info.zjhy(Info.zjhyInfoCallback,true);
    	}else if(type == "jianjie"){
    	  Info.zjhy(Info.zjhyInfoCallback,false);
    	}
	},
    initIndex: function(){
        Info.zjhy(Info.zjhyIndexCallback,true);
    },
    // 查出直接会员
    zjhy: function(callback,flag) {
        // alert('zjhy');
        $.ajax({
            url: '/index.php?s=/addon/Shop/User/zjhy/id/'+openid+'.html',
            dataType: 'json',
            type: "GET",
            success: function(rp) {
              if (rp.state == 1) {
                  zjhy = rp.num;
                  // 没有直接会员直接退出
                  if(zjhy == 0)
                    return;
                  var zj_page_sum = Math.ceil(zjhy / page_count);

                  callback(flag,zj_page_sum,zjhy);
              } else {
                  alert('获取直接会员数量失败');
              }
            }
        });
    },
    zjhyInfoCallback:function(flag,sum){
          Info.hyPage(Info.hyPageInfoCallback,openid,1,flag,sum);
    },
    zjhyIndexCallback:function(flag,sum,count){
        $('#zjhy').text(count);
        Info.hyPage(Info.hyPageIndexCallback,openid,1,flag,sum);
    },
    
    hyPage: function(callback,openid,page,flag,max){
        // alert('hyPage');
        $.ajax({
            url: '/index.php?s=/addon/Shop/User/hyPage.html',
            dataType: 'json',
            type: "POST",
            data:{
                id:openid,
                page:page
            },
            success: function(rp) {
                if (rp.state == 1) {
                    var arr = rp.openid;
                    if(arr){
                        str = arr.join();
                        callback(str,flag);
                    }
                    if(page < max)
                      Info.hyPage(callback,openid,page+1,flag,max);
                } else {
                    alert('获取会员列表失败');
                }
            }
        });
    },
    hyPageInfoCallback:function(str,flag){
        if(!flag){
          Info.hyCount(Info.hyCountInfoCallback,str);
        }else{
            Info.Page(Info.PageInfoCallback,str,flag);
        }
    },
    hyPageIndexCallback:function(str,flag){
       Info.hyxd(Info.hyxdIndexCallback,str,flag);
       if(flag){
          Info.hyCount(Info.hyCountIndexCallback,str);
        }
    },

    hyCount:function(callback,openid){
        // alert('hyCount');
        $.ajax({
            url: '/index.php?s=/addon/Shop/User/zjhy.html',
            data:{
                id:openid
            },
            dataType: 'json',
            type: "POST",
            success: function(rp) {
                if (rp.state == 1) {
                    jj_num = rp.num;
                    if(jj_num == 0)
                      return ;
                    var jj_page_sum = Math.ceil(jj_num / page_count);
                    callback(openid,jj_page_sum,rp.num);
                } else {
                    alert('获取会员数量失败');
                }
            }
        });
    },
    hyCountInfoCallback:function(openid,sum){
        Info.hyPage(Info.hyPageInfoCallback,openid,1,true,sum);//获得间接会员的openid
    },
    hyCountIndexCallback:function(openid,sum,count){
        jjhy += count;
        $('#jjhy').text(jjhy);
        Info.hyPage(Info.hyPageIndexCallback,openid,1,false,sum);//获得间接会员的openid
    },
    Page: function(callback,openid,flag){
        $.ajax({
            url: '/index.php?s=/addon/Shop/User/Page.html',
            dataType: 'json',
            type: "POST",
            data:{
                id:openid,
                // page:page
            },
            success: function(rp) {
                if (rp.state == 1) {
                    var arr = rp.info;
                    for (var i = arr.length - 1; i >= 0; i--) {
                      callback(arr[i]);
                    }
                } else {
                    alert('获取会员列表失败');
                }
            }
        });
    },

    PageInfoCallback:function(obj){
      var html = "<div class='user_list' style='display:block; line-height:30px;margin-bottom:10px;'><img src='"+obj['headimgurl']+"' style='height:30px;'><span style='margin-right:20px;'>"+obj['nickname']+"</span></div>";
      var old = $('#container').html();
      $('#container').html(old + html);
    },
    // flag为true表示为直接会员，false表示间接会员
    hyxd: function(callback,openid,flag){
        // alert('hyxd');
        $.ajax({
            url:'/index.php?s=/addon/Shop/User/orderCount',
            dataType: 'json',
            type: "POST",
            data:{
                id:openid
            },
            success: function(rp) {
                if (rp.state == 1) {
                    if(flag){
                        zjhyxd += rp.count;
                        $('#zjhyxd').text(zjhyxd);
                    }else{
                        jjhyxd += rp.count;
                        $('#jjhyxd').text(jjhyxd);
                    }
                    callback(flag,openid,rp.count);
                } else {
                    alert('获取会员下单数量失败');
                }
            }
        })
    },
    hyxdIndexCallback:function(){
        // 空函数防止js报错
    },
};