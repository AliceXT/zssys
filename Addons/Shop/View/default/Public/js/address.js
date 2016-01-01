function isWeiXin(){
    var ua = window.navigator.userAgent.toLowerCase();
    if(ua.match(/MicroMessenger/i) == 'micromessenger'){
        return true;
    }else{
        return false;
    }
}
var Addr = {
	address : function(){
	var redirect_url = window.location.href;
	$.ajax({
			url	:'/index.php?s=/addon/Shop/Order/getAddrParam.html',
			type: 'post',
			dataType : 'json',
			data : {
				url: redirect_url
			},
			success  : function(rp){
				   Addr.getAddr(rp);
			},
			error: function (){
				alert('没有返回');
			}
		});
	},
	getAddr : function (rp){
		if(rp.error != 1){
			WeixinJSBridge.invoke('editAddress',{
				'appId' : rp.appId,
				'scope' : 'jsapi_address',
				'signType' : 'sha1',
				'addrSign' : rp.addrSign,
				'timeStamp' : rp.timeStamp,
				'nonceStr' : rp.nonceStr,
			},function(res){
				if(res.err_msg != 'edit_address:ok'){
					// alert('获取地址失败，请手动输入地址');
					if(res.err_msg == 'edit_address:cancel'){
						alert('您已取消分享您的收货地址');
					}else if(res.err_msg == 'edit_address:fail'){
						alert('获取地址失败');
					}else{
						alert('获取地址遇到错误，请手动输入');
					}
				}else{
					$('#username').val(res.userName);
					$('#phone').val(res.telNumber);
					var address = res.proviceFirstStageName;
					if(res.proviceFirstStageName != res.addressCitySecondStageName)
						address += res.addressCitySecondStageName;
					if(res.addressCitySecondStageName != res.addressCountiesThirdStageName)
						address += res.addressCountiesThirdStageName;
					address += res.addressDetailInfo;
					$('#address').val(address);
				}
				$('#user_detail').attr('style','');
			})
		}else{
			alert('您的浏览器无法获取正确的地址');
			$('#user_detail').attr('style','');
		}
	},
	addressReg:function(){
		var username = $("#username").val();
        var phone = $.trim($("#phone").val());
        var address = $("#address").val();

        if (username == "") {
            alert("请输入联系人");
            return false;
        }

        if (phone == "") {
            alert("请输入联系电话");
            return false;
        }

        if (address == "") {
            alert("请输入详细地址");
            return false;
        }

        // 验证手机号码
        var telReg = !!phone.match(/^((0\d{2,3}-\d{7,8})|(1[356784]\d{9}))$/);
        if (telReg == false) {
            alert("请输入正确的联系电话");
            return false;
        }
        // 验证地址
        var adrReg = !!address.match(/(([\u4e00-\u9fa5]{2,8}省[\u4e00-\u9fa5]{2,14}市)|(重慶市|香港特別行政區|澳門特別行政區|新疆維吾爾自治區|內蒙古自治區|廣西壯族自治區|寧夏回族自治區|西藏自治區|上海市|北京市|重庆市|天津市|香港特别行政区|澳门特别行政区|新疆维吾尔自治区|内蒙古自治区|广西壮族自治区|宁夏回族自治区|西藏自治区))[\u4e00-\u9fa5]{1,14}(市|县|区|镇|縣|區|鎮)[\w\u4e00-\u9fa5]{1,40}/);
        if (adrReg == false) {
            alert("请输入正确的收货地址");
            return false;
        }

        return true;
	},
	init:function(){
		$('#addressbtn').click(function(){
        if(isWeiXin()){
            Addr.address();
        }else{
            $('#user_detail').attr('style','');
            $('#addressbtn').attr('style','display:none');
        }
        });
	}
}
