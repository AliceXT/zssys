var Order = {
	cancel:function(id){
		$.ajax({
			url	: '/index.php?s=/addon/Shop/Shop/cancleOrder.html',
			data: {id:id},
			dataType	: 'json',
			type: 'POST',
			success:function(rp){
				if(rp.code > 0){
					alert('该订单已经成功取消');
					$(".order_"+id).remove();
				}else{
					alert(rp.info);
					window.location.reload();
				}
			}
		});
	},
	jiesuan: function(id){
		$.ajax({
			url		: '/index.php?s=/addon/Shop/Order/jiesuan.html',
			data 	: {id:id},
			type 	: 'POST',
			dataType: 'json',
			success : function(rp){
				if (typeof WeixinJSBridge == "undefined"){
				   if( document.addEventListener ){
				       document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
				   }else if (document.attachEvent){
				       document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
				       document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
				   }
				}else{
				   onBridgeReady(rp);
				}
			}
		});
	},
	affirm : function(id){
		$.ajax({
			url 	: '/index.php?s=/addon/Shop/Order/affirm.html',
			data 	: {id:id},
			dataType: 'json',
			type 	: 'POST',
			success : function(rp){
				if(rp.code = 1){
					window.location.reload();
				}
			}

		});
	},
	returnGoods: function(id){	//跳转到退货详情页面
		window.location.href = '/index.php?s=/addon/Shop/Order/returnGoods/id/'+id+'.html';
	},
	returnGD : function(id){	//填写退货原因
		var reason = document.getElementById('return_reason').value;
		$.ajax({
			url : '/index.php?s=/addon/Shop/Order/returnGoods.html',
			data : {id:id, reason:reason},
			dataType : 'json',
			type 	: 'POST',
			success : function(rp){
				window.location.href = rp.url;
			}
		})
	}

};