function show_qr() {
   qr_wrap = document.getElementById("qr_wrap");
   if(qr_wrap.style.display=='none'){
        qr_wrap.style.display='block';
    }else{
        qr_wrap.style.display='none';
    }

}
function show_wx() {
    qr_wrap = document.getElementById("wx_wrap");
    if(qr_wrap.style.display=='none'){
        qr_wrap.style.display='block';
    }else{
        qr_wrap.style.display='none';
    }

}
function show_face(){
  $('#mcover_vard_face').show();
  $('.vcard_biz_face_wrap,.vcard_biz_career').hide();
}
function show_info(){
  $('#mcover_vard_face').hide();
  $('.vcard_biz_face_wrap,.vcard_biz_career').show();
}
$(function() {
$(".halffold_text_sl").each(function(i){
    var divH = $(this).height();
    var $p = $("p", $(this)).eq(0);
    while ($p.outerHeight() > divH) {
    $p.text($p.text().replace(/(s)*([a-zA-Z0-9]+|W)(...)?$/, "..."));
    };
});
  var text = $('.desc');
  var tree = $('.tree_box');

var resize_vcard_biz_face=function(){
  var vcard_face=$('#vcard_biz_face_img');
  var vcard_face_src=vcard_face.attr('src');
  var imgs= new Image();
  imgs.src=vcard_face_src;
  imgs.onload=function(){
    if(imgs.width>imgs.height){
      var vcard_face_w=70;
      var vcard_face_h=imgs.height*70/imgs.width;
      var vPosition='0'+ (vcard_face_w-vcard_face_h)/2+'px';
    }else if(imgs.width<imgs.height){
      var vcard_face_h=70;
      var vcard_face_w=imgs.width*70/imgs.height;
      var vPosition = (vcard_face_h-vcard_face_w)/2 + 'px' + ' 0';

    }else {
            var bgPosition = '0 0';
            var vcard_face_w = vcard_face_h = 70;
    }
    $('#vcard_biz_face_img_show').css({
            "background-image" : "url("+vcard_face_src+")",
            "background-size" : vcard_face_h+'px '+vcard_face_h+'px',
            "background-position" : vPosition,
            "background-repeat" : 'no-repeat', 
            "width":vcard_face_w+'px',
            "height":vcard_face_h+'px',
            "border-radius":'60px'

    });

  }
}
$(function(){
var vcard_biz_bottom=$('#vcard_biz_bottom');
if(vcard_biz_bottom){
  $('#cli_content').css('padding-bottom','40px');
}

  var h=document.documentElement.clientHeight;
  $("#tools").css("display","none");
  $(window).scroll(function(){
        var windowScrollTop=$(window).scrollTop();
        var oTools=$("#tools");
            if(windowScrollTop>100)
            {
                oTools.fadeIn();
                }else{
               oTools.fadeOut();
            }
        });
  $(".scroll_top").css("bottom",0.15*h+"px");
  $(".scroll_top").click(function(){
    window.scrollTo(0,0);
  });
resize_vcard_biz_face();
});
function show_content(obj){
  $(obj).next().slideToggle('fast').css('overflow','initial');
  $(obj).toggleClass('show_content');
  $(obj).toggleClass('hider');
  $(obj).toggleClass('shower');
  if (typeof myscroll!='undefined') {
    myscroll._resize();
  }
}
function show2_content(obj){
  $(obj).parent().hide();
  $(obj).parent().next().slideToggle('fast').css('overflow','initial');
  if (typeof myscroll!='undefined') {
    myscroll._resize();
  }
}
function show3_content(obj){
  $(obj).parent().hide();
  $(obj).parent().prev().slideToggle('fast').css('overflow','initial');;
  $("html,body").animate({scrollTop:$(obj).parent().prev().offset().top}, 500);
  if (typeof myscroll!='undefined') {
    myscroll._resize();
  }
} 
function show4_content(obj){
  $(obj).hide();
  $(obj).parent().next().slideToggle('fast');
  if (typeof myscroll!='undefined') {
    myscroll._resize();
  }
}
function show5_content(obj){
  $(obj).parent().hide();
  $(obj).prev().find(".reg_show_div1").show();
  $("html,body").animate({scrollTop:$(obj).parent().parent().offset().top}, 500);
  if (typeof myscroll!='undefined') {
    myscroll._resize();
  }

}
function show6_content(obj){
  $(obj).next().find(".tree_box_content").slideToggle('fast').css('overflow','initial');
  $(obj).toggleClass('hider');
  $(obj).toggleClass('shower');
TouchSlide({ 
          slideCell:"#slideBox",
          mainCell:".bd ul", 
          effect:"leftLoop", 
          autoPage:true ,//自动分页
          autoPlay:true//自动播放 
        });
var w_w=$(".slideBox .bd ul li").width();
  $(".slideBox .bd li .tit").css("width",w_w+"px");
var bd_w = $(".slideBox .bd").width();
var hd_w = $(".slideBox .hd").width();
  $(".slideBox .hd").css({"bottom":"27px","left":(bd_w-hd_w)/2+"px"});
}
  window.show_content=show_content;
  window.show2_content=show2_content;
  window.show3_content=show3_content;
  window.show4_content=show4_content;
  window.show5_content=show5_content; 
  window.show6_content=show6_content;


/*音频播放*/
  $('.audio_play_box').each(function(i) {
    var aud = $(this).find(".aud");
    $(this).find('.play').bind('click', function(evt) {
      /*填入音频地址*/
      if (aud.attr('src') == undefined) {
        aud.attr('src', aud.attr('data_src')).removeAttr('data_src');
      }
      if (aud[0].paused) {
        $('.audio_play_box').each(function() {
          $(this).find('audio')[0].pause();
          $(this).find(".audio").removeClass('audio_stop_btn').addClass('audio_play_btn');
        });
        evt.preventDefault();
        aud[0].play();
        $(this).find(".audio").removeClass('audio_play_btn').addClass('audio_stop_btn');
      } else if (aud[0].paused == false) {
        evt.preventDefault();
        aud[0].pause();
        $(this).find(".audio").removeClass('audio_stop_btn').addClass('audio_play_btn');
      }

    });
    /*添加监听，在播放结束时*/
    aud[0].addEventListener('ended', function(evt) {
      $(".audio").removeClass('audio_stop_btn').addClass('audio_play_btn');
    });
  });
  
  function stopBubble(e){  
        // 如果传入了事件对象，那么就是非ie浏览器  
        if(e&&e.stopPropagation){  
            //因此它支持W3C的stopPropagation()方法  
            e.stopPropagation();  
        }else{  
            //否则我们使用ie的方法来取消事件冒泡  
            window.event.cancelBubble = true;  
        }  
    }
  
  /*防伪溯源提交和结果返回*/
  function afatra_submit(id, input_len) {
    var a_id = id;
    var input_name_str = 'afatra_input_' + id + '_';
    var param = new Array();
    for (var i = 0; i < input_len; i++) {
      var input = document.getElementById(input_name_str + i);
      param.push(input.name + '=' + input.value);
    }
    var param_str = param.join('&');

    $.post('/Afatra/query/id/' + a_id, param_str, function(response) {
      var resp = response;
      var resp_txt = '';
      if (resp.status != '' && resp.status !== undefined) {
        resp_txt = response.info;
      } else {
        r_len = resp.length;
        for (i = 0; i < r_len; i++) {
          resp_txt += '<p>' + resp[i].name + '：<span class="afatra_res_val">' + resp[i].vals + '</span></p>';
        }
      }
      var w_w = $(window).width();
      var w_h = $(window).height();
      var dialog_width = w_w - 30;
      var dialog_height = w_h - 40;
      var dialog_top = (w_h - dialog_height) / 2;
      var dialog_left = (w_w - dialog_width) / 2;

      document.getElementById("content_tree").innerHTML += '<div id="afatra_dialog" style="height:' + dialog_height + 'px;width:' + dialog_width + 'px;top:' + dialog_top + 'px;left:' + dialog_left + 'px"><div id="afatra_dialog_title" style="height: 32px; box-shadow: 0 2px 2px 0 rgba(0, 0, 0, 0.1),0 1px 0 0 rgba(0, 0, 0, 0.1); font-size: 20px; text-align: center; background: #EBEBEB; padding: 10px; ">查询结果</div><div id="afatra_dialog_close"><a style="display: block;" href="javascript:forclose();"></a></div><div class="dialog_content">' + resp_txt + '</div><a href="javascript:forclose();" class="btn-orange btn" style="padding: 8px; display: block; width: 50%; margin: 0 auto; text-align: center;">关闭</a></div>';
    })
  }
});

function set_descr() {
 var descr_w = $(".desc_title  span").width();
 //alert(descr_w);
 var innertext2_w = $(".desc_title ").width(); 
 //alert(innertext2_w);
 if(descr_w>(innertext2_w-12))
  $(".desc_title ").css("text-align","left");
 else
  $(".desc_title ").css("text-align","center");
}
set_descr();

$(window).load(function(){
  var w_w = $(window).width();
  //alert(w_w);
  $(".halffold_img").each(function(){
    var n = $(this);
    var halffold_img = n.find(".halffold_img_img");
    var halffold_img_h =halffold_img.height();
    var halffold_img_w =halffold_img.width();
  //  alert(halffold_img_h);
  //  alert(halffold_img_w);

     var h = w_w*halffold_img_h/halffold_img_w;
     var mt_w = -(h-100)/2;
     halffold_img.css({"width":"100%","margin-top":mt_w});

   // alert("调试中");
  })
});


function set_halffold_text() {
  $(".halffold_text").each(function(){
     var m = $(this);
     var halffold_text_sl =m.find(".halffold_text_sl").text();
     str = halffold_text_sl.replace(/&lt;[^>].*?&gt;/g,"");  
     m.find(".halffold_text_sl").html(str);
  })
}
set_halffold_text();
function no_header(){
 var halffold =$('.tree_box:first').find('.halffold');
 var tree_box_content = $('.tree_box:first').find('.tree_box_content');
 var tree_box_title =$('.tree_box:first').find('.tree_box_title');
 var no_title =$('.tree_box:first').find('.no_title');

 if(halffold)
 {
  halffold.css('margin-top','0px');
  tree_box_content.css('margin-top','0px'); 
 }
 if (tree_box_title)
 {
  tree_box_title.css('margin-top','0px'); 
 }
 if(no_title)
 {
  no_title.css('margin-top','0px'); 
 }
} 
no_header();








/*轮播脚本*/
$(function() {
TouchSlide({ 
          slideCell:"#slideBox",
          titCell:".hd ul", //开启自动分页 autoPage:true ，此时设置 titCell 为导航元素包裹层
          mainCell:".bd ul", 
          effect:"leftLoop", 
          autoPage:true ,//自动分页
          autoPlay:true//自动播放 
        });
var w_w=$(".slideBox .bd ul li").width();
  $(".slideBox .bd li .tit").css("width",w_w+"px");
var bd_w = $(".slideBox .bd").width();
var hd_w = $(".slideBox .hd").width();
  $(".slideBox .hd").css({"bottom":"27px","left":(bd_w-hd_w)/2+"px"});
});



$(function(){
        var w_w = $(window).width();
        var dm_span4_count = $('.dm_span4').length;
        if(dm_span4_count >6) {
            var dm_row = 3;
        } else if (dm_span4_count > 3) {
            var dm_row = 2;
        } else {
            var dm_row = 1;
        }
        var grid_h = w_w / 3 * dm_row;
        var dm_span4_w = (w_w-3)/3;
        $(".dm_contenter").css({"height":grid_h+"px"}).show();
        $(".dm_content").css({"height":grid_h+"px","width":w_w+"px"}).show();
        $(".dm_span4").css({"width":dm_span4_w+"px","height":dm_span4_w+"px"}).show();
        $(".dm_span4_div1").css({"height":0.6*dm_span4_w+"px"}).show();
        $(".dm_span4_div2").css({"height":0.2*dm_span4_w+"px"}).show();
});