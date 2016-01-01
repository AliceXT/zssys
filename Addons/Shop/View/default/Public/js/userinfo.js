var User = {
	edit 	: function(){
		var nickname = document.getElementById('nickname').value;
		var phone = document.getElementById('phone').value;
		var address = document.getElementById('address').value;
		$.ajax({
			url : '/index.php?s=/addon/Shop/User/editinfo.html',
			data: {nickname:nickname, phone:phone, address:address},
			dataType: 'json',
			type: "POST",
			success : function(rp){
				window.location.href = rp.url;
			}
		});
	},
	leapToEdit : function(is_dis){
		window.location.href = '/index.php?s=/addon/Shop/User/editinfo/is_dis/'+is_dis+'.html';
	},
	getCashRep : function(){
		var fee = parseInt(document.getElementById('fee').value);
		var remain = parseInt(document.getElementById('remain_money').value);
		if(!fee){
			alert('请输入提现金额');
			document.getElementById('fee').focus();
			return false;
		}else if( fee > remain){
			alert('取现金额不可超出余额!');
			document.getElementById('fee').focus();
			return false;
		}
		else if( fee < 1)
		{
			alert('取现金额最少1元!');
			document.getElementById('fee').focus();
			return false;
		}
		else if( fee > 200 )
		{
			alert('取现金额最大200元!');
			document.getElementById('fee').focus();
			return false;
		}else{
			var btn = document.getElementById('getCashRep')
			btn.disabled = true;
			// btn.attr("style","background: -webkit-gradient(linear, 0 0, 0 100%, from(#c0c0c0), to(#4c4c4c));");
			$.ajax({
				url 	: '/index.php?s=/addon/Shop/User/getCasherReport.html',
				data 	: {fee:fee},
				dataType: 'json',
				type 	: 'POST',
				success : function (rp){
					alert(rp.info);
	
					if(rp.code > 0){
						window.location.href = rp.url;
					}
				}
	
			});
		}
	},
};
