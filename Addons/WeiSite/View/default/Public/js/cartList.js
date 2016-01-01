var Cart = {
	setCartEmpty : function(){
		$.ajax({
			url 	: '/index.php?s=/addon/WeiSite/Product/setCartEmpty.html',
			type 	: 'post',
			dataType: 'json',
			success : function(rp){
				// $('.list').remove();
				window.location.href = '/index.php?s=/addon/WeiSite/Product/cartList.html';
				alert(rp.info);
			}
		});

	},
	removeGoods : function(id){
		$.ajax({
			url		: '/index.php?s=/addon/WeiSite/Product/removeGoods.html',
			type 	: 'post',
			dataType: 'json',
			data 	: {id : id},
			success : function(rp){
				window.location.href = '/index.php?s=/addon/WeiSite/Product/cartList.html';
			}
		});
	}
};