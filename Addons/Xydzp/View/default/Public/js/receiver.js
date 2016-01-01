  function HideDiv() {
    $("#dail2").hide();
    $("#lean_overlay").fadeOut(200);
  }
  function ShowDiv() {

    /*加入遮罩层到Body*/
    var _3 = $("<div id='lean_overlay'>&nbsp;</div>");
    $("body").append(_3);             
    /*最上层DIV*/
    $("#dail2").css(
    {
     "display": "block",
     "position": "fixed",
     "opacity": 1,
     "z-index": 4  
   });
    /*遮罩层 透明到0.1*/
    $("#lean_overlay").fadeTo(200, 0.6);            
    //$("#lean_overlay").click(HideDiv);
    $("#lean_overlay").show();
  }
function getReceiverInfo(){
  if(posturl !="" && jp_id > 0){ ShowDiv();}
  if(jp_id<=0)
    alert('请先点击未领取按钮');
  $("#btn_submit").bind("click",function(){           
    var receiver = $('#truename').val();
    var iphone = $('#mobile').val();
    var address = $('#address').val();
    if(receiver!=undefined && receiver==""){
      $.Dialog.fail("请填写收件人名称！");//成功调用 提示一秒后自动关闭
      return false;
    }
    if(mobile!=undefined && mobile==""){
      $.Dialog.fail("请填写联系电话！");//成功调用 提示一秒后自动关闭
      return false;
    }

    if(address!=undefined && address==""){
      $.Dialog.fail("请填写收货地址！");//成功调用 提示一秒后自动关闭
      return false;
    }
  
    $.ajax({
      type: 'POST',
      url: posturl,
      data:{id:jp_id,receiver:receiver,iphone:iphone,address:address},
      dataType: 'json',
      cache: false,
      error: function() {},
      success: function(json) {  
        alert(json.errmsg);     
        location.reload();
      }
    });
  });
}      

function modifyReceiverInfo(id){
  if(modifyurl !=""){ ShowDiv();}
  $.ajax({
      type: 'POST',
      url: '/index.php?s=/addon/Xydzp/Share/getLog',
      data:{id:id},
      dataType: 'json',
      cache: false,
      error: function() {},
      success: function(json) {  
        $('#truename').val(json.receiver);
        $('#mobile').val(json.iphone);
        $('#address').val(json.address);

      }
    });
  $("#btn_submit").bind("click",function(){           
    var receiver = $('#truename').val();
    var iphone = $('#mobile').val();
    var address = $('#address').val();
    if(receiver!=undefined && receiver==""){
      $.Dialog.fail("请填写收件人名称！");//成功调用 提示一秒后自动关闭
      return false;
    }
    if(mobile!=undefined && mobile==""){
      $.Dialog.fail("请填写联系电话！");//成功调用 提示一秒后自动关闭
      return false;
    }

    if(address!=undefined && address==""){
      $.Dialog.fail("请填写收货地址！");//成功调用 提示一秒后自动关闭
      return false;
    }
  
    $.ajax({
      type: 'POST',
      url: modifyurl,
      data:{id:id,receiver:receiver,iphone:iphone,address:address},
      dataType: 'json',
      cache: false,
      error: function() {},
      success: function(json) {  
        alert(json.errmsg);     
        location.reload();
      }
    });
  });
}      
// 按领取按钮时提示分享
function alertShare(id,description){
  alert('请按右上角三个点按钮分享中奖信息给你的朋友即可获得奖励');
  jp_id = id;
  share_desc = "我在大转盘抽奖活动中，抽中了『"+description.replace(/<[^>]+>/g,"")+"』，更多精美礼品等你来";
  // alert("id="+id+",description="+description+",jp_id="+jp_id+",share_desc="+share_desc);
  weixin();
} 
