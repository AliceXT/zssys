/**
adaptVideoPlay.js for http://cli.im/
(c) 2014- foukenma
@use:
	//全局参数配置，也可以在调用DOM对象中通过data-*方式进行个性化的参数配置
	//必须指定一个uid
	//eg: <a href="#" class="test" data-uid="e5ebb9bb1526daad2c02d9df5f0cc2d1_e" data-width="100%" data-height="100%">Image of video shot here.</a>
	//可以是任何tag,只要有uid,
	//调用 $('.test').adaptVideoPlay("close")恢复原状
	var settings = {
			width:'100%',
			height:'100%',
			autoplay:'autoplay',
			controls:'controls',
			loop:'loop',
			preload:'preload',
			bgcolor:'#ffffff',
			allowfullscreen:true,
			allowscriptaccess:'always',
			wmode:'transparent',
			flashvars:'',
			flashpath:'http://player.asdtv.com/videos/',
			videopath:'http://v.asdtv.com/uc/video/getMp4?vid='
	};
	$('.test').adaptVideoPlay();
	或
	$('.test').adaptVideoPlay(settings);
*/
;(function(root,factory){
	if(typeof module !== 'undefined' && module.exports){//CMD
		module.exports = factory(root.jQuery||root.Zepto);
	}else if(typeof define === 'function' && define.amd){//AMD
		define(['zepto'],factory);
	}else{
		factory(root.jQuery||root.Zepto);
	};
})(this,function($){
	"use strict";
	var AdaptVideoPlay = function(params){
		this.params = params;
		this.isIOS = AdaptVideoPlay.utils.isIOS();
		this.useVideo = (this.isIOS &&  !this.params.useYouku) || (this.params.useVideo && !this.params.useFlash && !this.params.useYouku);
		this.source = this.createSource();
		this.view = this.createView();
	};
	AdaptVideoPlay.prototype = {
		createSource:function(){
			try{
				if(this.useVideo){
					return this.params.videopath+this.params.uid;
				}else if(this.params.useYouku){
					return this.params.youkupath+this.params.uid;
				}else{
					return this.params.flashpath+this.params.uid+".swf";
				}
			}catch(e){
				throw new Error(e + " id or path not defiend.");
			};
		},
		createView:function(){
			try{
				if(this.useVideo){
					return this._createVideo();
				}else if(this.params.useYouku){
					return this._createYouku();
				}else{
					return this._createSWF();
				}
			}catch(e){
				throw new Error(e);
			}
		},
		play:function(){
			if(this.useVideo){
				var _video = this.view.get(0);
				if(_video.autoplay && _video.paused){
					_video.play();
				}
			}
		},
		pause:function(){
			if(this.useVideo){
				this.view.get(0).pause();
			}
		},
		destroy:function(){
			this.view.detach();
		},
		_createVideo:function(){
			var _html = '<video{id/}{name/}{class/} src="'+this.source+'" width="'+this.params.width+'" height="'+this.params.height+'" '+this.params.loop+' '+this.params.controls+' '+this.params.preload+' '+this.params.autoplay+'>';
			_html = this._translate(_html);
			return $(_html);
		},
		_createSWF:function(){
			var _html = [
				'<object{id/}{name/}{class/} classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="'+this.params.width+'" height="'+this.params.height+'" style="z-index: 1001; position: relative;">',
				'<param name="movie" value="'+this.source+'">',
				'<param name="bgcolor" value="'+this.params.bgcolor+'">',
				'<param name="allowfullscreen" value="'+this.params.allowfullscreen+'">',
				'<param name="allowscriptaccess" value="'+this.params.allowscriptaccess+'">',
				'<param name="wmode" value="'+this.params.wmode+'">',
				'<param name="flashvars" value="'+this.params.flashvars+'">',
				'<embed{id/}{name/}{class/} type="application/x-shockwave-flash" src="'+this.source+'" width="'+this.params.width+'" height="'+this.params.height+'" bgcolor="'+this.params.bgcolor+'" allowfullscreen="'+this.params.allowfullscreen+'" allowscriptaccess="'+this.params.allowscriptaccess+'" wmode="'+this.params.wmode+'" flashvars="'+this.params.flashvars+'">',
				'</object>'
			].join('');
			_html = this._translate(_html);
			return $(_html);
		},
		_createYouku:function(){
			var _html='<iframe{id/}{name/}{class/} src="'+this.source+'" width="'+this.params.width+'" height="'+this.params.height+'" frameborder="0" '+(this.params.allowfullscreen?"allowfullscreen":"")+'></iframe>';
			_html = this._translate(_html);
			return $(_html)
		},
		_translate:function(html){
			var that = this;
			return html.replace(/{id\/}/g,function(){
				return that.params.id?' id="'+that.params.id+'"':'';
			}).replace(/{name\/}/g,function(){
				return that.params.name?' name="'+that.params.name+'"':'';
			}).replace(/{class\/}/g,function(){
				return that.params.cls?' class="'+that.params.cls+'"':'';
			});
		}
	};
	AdaptVideoPlay.utils = {
		isIOS:function(){
			var ua = navigator.userAgent;
			var ipad = ua.match(/(iPad).*OS\s([\d_]+)/),ipod = ua.match(/(iPod)(.*OS\s([\d_]+))?/),iphone = !ipad && ua.match(/(iPhone\sOS)\s([\d_]+)/);
			if(ipad || ipod || iphone){
				return true;
			};
			return false;
		}
	};
	$.fn.adaptVideoPlay = function(settings){
		if(typeof settings === 'string'){
			switch(settings){
				case "close":
					this.each(function(i){
						$(this).trigger("recover");
					});
					break;
				default:
					break;
			}
		};
		var _default = {
			id:'',
			name:'',
			cls:'',
			uid:'',
			width:'100%',
			height:'',
			autoplay:'autoplay',
			controls:'controls',
			loop:'loop',
			preload:'',
			bgcolor:'#ffffff',
			allowfullscreen:true,
			allowscriptaccess:'always',
			wmode:'transparent',
			flashvars:'',
			useVideo:true,
			useFlash:false,
			useYouku:false,
			flashpath:'http://player.asdtv.com/videos/',
			videopath:'http://v.asdtv.com/uc/video/getMp4?vid=',
			youkupath:'http://player.youku.com/embed/'
		};
		var _settings = $.extend({},_default,settings||{});
		var that = this;
		this.each(function(i){
			var _params = $.extend({},_settings,$(this).data() || {});
			var _avp;
			var $this = $(this);
			var _interactivable = true;
			if(_params.width.indexOf('%')>-1){
				$this.parent().css('box-sizing')!='border-box'?$this.parent().css('box-sizing','border-box'):'';
			};
			if(_params.useYouku){
				_interactivable = false;
				_avp  = _avp ?_avp : new AdaptVideoPlay(_params);
				$this.hide();
				$this.before(_avp.view);
			};
			$this.bind("click touchend tap",function(e){
				e.stopImmediatePropagation();
				e.stopPropagation();
				e.preventDefault();
				if(!_interactivable){
					return false;
				};
				var _params = $.extend(_settings,$this.data() || {});
				_avp  = _avp ?_avp : new AdaptVideoPlay(_params);
				$.each(that,function(k,v){
					if($this.get(0) == v){
						return true;
					};
					$(v).trigger('recover');
				})
				$this.hide();
				$this.before(_avp.view);
				_avp.play();
			});
			$this.bind("recover",function(){
				if(!_interactivable){
					return false;
				};
				if(_avp){
					_avp.pause();
					_avp.destroy();
				}
				$this.show();
			});
		});
	};
});
