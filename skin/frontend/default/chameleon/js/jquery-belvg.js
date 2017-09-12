/**
 * BelVG
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.dylanqueen.com/
 
 * @category   dylanqueen
 * @package    Chameleon
 * @copyright  Copyright (c) 2010 - 2011 LLC. (http://www.dylanqueen.com)
 * @license    http://www.dylanqueen.com/
 * jquery
 * jquery-scrolltofixed-min
 *
 */
var lG_close='Close';
var LG_close_menu='Close';
var LG_have_login='You have login';
var LG_logout='Log me out';
var LG_login_err='Login failed,Wrong account or password.';
var LG_register_err='Register failed,Please retry.';
var LG_loading='Wiating...';
var debug = (typeof(belvg_debug) == "boolean")?belvg_debug:false;
var back_to_login=false;
//登录或注册切换的时候加返回按钮
jQuery.fn.only = function () {
	if(!this.hasClass('login-or-create-show-div')){
		this.addClass('login-or-create-show-div');
	}
	if(jQuery(this).children('.page-title') && jQuery(this).children('.page-title').has('.account-back').length==0){
		jQuery(this).children('.page-title').append(jQuery('<div class="account-back">Back</div>'));
	}
	return this;
}
//图标亮灯
function iconLight(selector){
	if(jQuery(selector) && jQuery(selector).length>0){
	if(!jQuery(selector).hasClass('light')){
	jQuery(selector).addClass('light');
	}
	}
}

function showLogin(){
	jQuery('.belvg').hide();
	jQuery('.main-footer').hide();
	jQuery('#back').show();
	jQuery('.nav-main > .account-login').only().show();
	jQuery('#to-login').parent('li').addClass('active');
}

function showPage(){
	jQuery('.belvg').show();
	jQuery('.main-footer').show();
	jQuery('.nav-main > .account-login').hide();
	jQuery('.nav-main > .account-create').hide();
	jQuery('#to-login').parent('li').removeClass('active');
}

function hideLogin(){
	jQuery('#back').hide();
	jQuery('.belvg').show();
	jQuery('.main-footer').show();
	jQuery('.nav-main > .account-login').hide();
	jQuery('#to-login').parent('li').removeClass('active');
}

function showRegister(){
	jQuery('#back').show();
	jQuery('.belvg').hide();
	jQuery('.main-footer').hide();
	jQuery('.nav-main > .account-create').only().show();
	jQuery('#to-login').parent('li').addClass('active');
}

function showCurrentRegister(){
	jQuery('.belvg').show();
	jQuery('.main-footer').show();
	jQuery('.nav-main > .account-create').only().show();
	jQuery('#to-login').parent('li').addClass('active');
}

function showCurrentLogin(){
	jQuery('.belvg').show();
	jQuery('.main-footer').show();
	jQuery('.nav-main > .account-login').only().show();
	jQuery('#to-login').parent('li').addClass('active');
}

function hideRegister(){
	jQuery('.belvg').show();
	jQuery('#back').hide();
	jQuery('.main-footer').show();
	jQuery('.nav-main > .account-create').hide();
	jQuery('#to-login').parent('li').removeClass('active');
}
//检查当前路径
function checkHide(flag){
	var current =  location.href;
	return current.indexOf(flag);
}
//检查ssl
function isSsl(){
	return (checkHide('https')==0);
}
//检查当前是不是登录了, httponly不可用
function checkNowLogin(){
	var u = Mage.Cookies.get('u_u_in');
	return (u=='1');
}
/**
 * 遮罩层，显示还是隐藏
 * 在提交登录等表单用
 * @param act
 */
function myOverLay(act){
	var _myOverLay = jQuery('#myOverLay');
	if(_myOverLay && _myOverLay.length>0){
		 if(act=='show'){
			 jQuery(_myOverLay).show();
		 }else{
			 jQuery(_myOverLay).hide();
		 }
	}else{
    var _myOverLay = jQuery('<div id="myOverLay">').css({
		'position':'absolute',
	'top':'0px',
	'left':'0px',
	'width':'100%',
	'height':'100%',
	'padding-top':'25%',
	'text-align':'center',
	'background-color':'black',
	'color':'#fff',
	'font-size':'16px',
	'font-weight':'bold',
	'opacity':'0.2',
	'z-index':9999,
	'display':'none;'
	}).html(LG_loading);
		jQuery('body').append(_myOverLay);
	}
}
/**
 * select box
 * 选择框加遮罩
 * 样式文件在css里
 */
(function($){
	$.fn.selectBox=function(){
		$(this).each(function(){
			if($(this).prop('tagName').toUpperCase() == 'SELECT'){
				var that = $(this);
				var selectBoxContainer = $('<div>',{
					width		: $(that).outerWidth()+10,
					className	: 'tzSelect',
					html		: '<div class="selectBox"></div>'
				});
				var dropDown = $('<ul>',{className:'dropDown'});
				var selectBox = selectBoxContainer.find('.selectBox');
				$(that).find('option').each(function(i){
					var _icon='';
					var _text='';
					var option = $(this);
					if(option.attr('selected') || i==0){
						selectBox.html(option.text());
					}
					if(option.data('skip')){
						return true;
					}
					if(option.data('icon'))_icon='<img src="'+option.data('icon')+'" />';
					if(option.data('html-text')){ _text='<span>'+option.data('html-text')+'</span>'; }else{
						_text = option.text();
					}
					var li = $('<li>',{
						html:_icon+_text
					});
					li.click(function(){
						selectBox.html(option.text());
						dropDown.trigger('hide');
						$(that).find('option').each(function(){ $(this).removeAttr('selected'); });
						option.attr('selected','selected');
						$(that).val(option.val());
						$(that).trigger('change');
						return false;
					});
					dropDown.append(li);
				});
				selectBoxContainer.append(dropDown.hide());
				$(that).hide().after(selectBoxContainer);
				dropDown.bind('show',function(){
					if(dropDown.is(':animated')){
						return false;
					}
					selectBox.addClass('expanded');
					dropDown.slideDown();
				}).bind('hide',function(){
					if(dropDown.is(':animated')){
						return false;
					}
					selectBox.removeClass('expanded');
					dropDown.slideUp();
				}).bind('toggle',function(){
					if(selectBox.hasClass('expanded')){
						dropDown.trigger('hide');
					}
					else dropDown.trigger('show');
				});
				selectBox.click(function(){
					dropDown.trigger('toggle');
					return false;
				});
				/*selectBox.hover(function(){
					dropDown.trigger('show');
					return false;
				});*/
				dropDown.on('mouseleave',function(){
					dropDown.trigger('hide');
					return false;
				});
				$(document).click(function(){
					dropDown.trigger('hide');
				});
			}
		});
	}
})(jQuery);
/**
 * Alert
 * Confirm
 */
(function ($) {
	$.MsgBox = {
		Alert: function (msg) {
			GenerateHtml("alert",msg);
			btnOk(); //alert只是弹出消息，因此没必要用到回调函数callback
			btnNo();
		},
		Confirm: function (msg, callback) {
			GenerateHtml("confirm",msg);
			btnOk(callback);
			btnNo();
		}
	}
	//生成Html
	var GenerateHtml = function (type,msg) {
		var _html = "";
		_html += '<div id="mb_box"></div><div id="mb_con">';
		_html += '<a id="mb_ico">x</a><div id="mb_msg">' + msg + '</div><div id="mb_btnbox">';

		if (type == "alert") {
			_html += '<input id="mb_btn_ok" type="button" value="OK" />';
		}
		if (type == "confirm") {
			_html += '<input id="mb_btn_ok" type="button" value="OK" />';
			_html += '<input id="mb_btn_no" type="button" value="Cancel" />';
		}
		_html += '</div></div>';

		//必须先将_html添加到body，再设置Css样式
		$("body").append(_html); GenerateCss();
	}

	//生成Css
	var GenerateCss = function () {
		$("#mb_box").css({ width: '100%', height: '100%', zIndex: '99999', position: 'fixed',
			filter: 'Alpha(opacity=60)', backgroundColor: 'black', top: '0', left: '0', opacity: '0.6'
		});
		$("#mb_con").css({ zIndex: '999999', 'max-width': '400px',width:'100%', position: 'fixed',
			backgroundColor: 'White', borderRadius: '5px'
		});
		$("#mb_msg").css({ padding: '20px', lineHeight: '20px',
			borderBottom: '1px dashed #DDD', fontSize: '13px'
		});
		$("#mb_ico").css({ display: 'block', position: 'absolute', right: '10px', top: '9px',
			border: '1px solid Gray', width: '18px', height: '18px', textAlign: 'center',
			lineHeight: '16px', cursor: 'pointer', borderRadius: '12px'
		});
		$("#mb_btnbox").css({ margin: '15px 0 10px 0', textAlign: 'center' });
		$("#mb_btn_ok,#mb_btn_no").css({backgroundColor: '#999','border-radius':'4px',color:'#fff',height:'28px','line-height':'28px','width':'85px', border: 'none' });
		$("#mb_btn_no").css({  marginLeft: '20px' });
		//右上角关闭按钮hover样式
		$("#mb_ico").hover(function () {
			$(this).css({ backgroundColor: '#FFF', color: '#000' });
		}, function () {
			$(this).css({ backgroundColor: '#DDD', color: '#000' });
		});
		var _width = document.documentElement.clientWidth; //屏幕宽
		var _height = document.documentElement.clientHeight; //屏幕高
		var boxWidth = $("#mb_con").width();
		var boxHeight = $("#mb_con").height();
		var _l = _width - boxWidth;var _t =_height - boxHeight;
		if(_l<0)_l=0;if(_t<0)_t=0;
		//让提示框居中
		$("#mb_con").css({ top: (_t) / 2 + "px", left: (_l) / 2 + "px" });
	}

	//确定按钮事件
	var btnOk = function (callback) {
		$("#mb_btn_ok").click(function () {
			$("#mb_box,#mb_con").remove();
			if (typeof (callback) == 'function') {
				callback();
			}
		});
	}
	//取消按钮事件
	var btnNo = function () {
		$("#mb_btn_no,#mb_ico").click(function () {
			$("#mb_box,#mb_con").remove();
		});
	}
})(jQuery);
/**
 * size-chart inch or cm
 * 单列表格显示尺码的inch或cm
 */
(function($){
	var currenctHide = '';
	String.prototype.trim = function () {
		var t = this.replace('&nbsp;','');
	     return t.replace(/^(\s|\u3000)+|(\s|\u3000|)+$/g, "");
	};
	$.fn.getInchCm=function(){
		var nLG_inch = '';
		var nLG_cm = '';
		$(this).find('tr').eq(1).children('td').each(function(){
			var val = $(this).html().trim();
			if(val && val.length>0){
				if(nLG_inch==''){ nLG_inch=val; }else if(nLG_cm==''){ nLG_cm=val;}
			}
			if(nLG_inch!='' && nLG_cm!='')return false;
		});
		return [nLG_inch,nLG_cm];
	};
	$.fn.setSizeChartTable=function(){
		var LG_inch='Inch';var LG_cm='Cm';
		var mlg_inchCm= $(this).getInchCm();
		var mlg_inch =mlg_inchCm[0]||LG_inch;
		var mlg_cm = mlg_inchCm[1]||LG_cm;
		slg_inch = mlg_inch.toLowerCase();
		slg_cm = mlg_cm.toLowerCase();
		var mhtml =  $('<div class="size-chart-units"><div id="size-chart-unit-'+slg_inch+'" onclick="resetTable(this)" class="selected">'+LG_inch+'</div><div id="size-chart-unit-'+slg_cm+'" onclick="resetTable(this)">'+LG_cm+'</div></div>');
		$(this).parent('div').prepend(mhtml);
		$(this).findTableColHide(mlg_cm);
	};
	$.fn.findTableColHide=function(colFlag) {
		if(currenctHide == colFlag)return;
		currenctHide = colFlag;
		colFlag = colFlag.toLowerCase().trim();
		$(this).find('td').each(function () {
			$(this).show();
		});
		var col = new Array();
		$(this).find('tr').each(function () {
			//var tr = $(this);
			//var curTh=false,curTr;
			$(this).children('td,th').each(function (i) {
				if($(this).attr('colspan')==2)$(this).removeAttr('colspan');
				if ($(this).text().trim() == colFlag || $.inArray(i, col) >= 0) {
					if($.inArray(i, col) < 0)col.push(i);
					$(this).hide();
				}
				//if($(this).text().trim() == colFlag && !curTh){
				//	curTr = tr;curTh=true;
				//}
			});
			//if(curTr && curTr.length>0)$(curTr).hide();
		});
	}
})(jQuery);
//点击切换显示方法
function resetTable(obj){
	var table = jQuery('.size-chart-inch');
	var next = jQuery(obj).next('div');
	if(!next||jQuery(obj).next('div').length==0){
		next = jQuery(obj).prev('div');
	}
	jQuery(obj).addClass('selected');
	jQuery(next).removeClass('selected');
	var colHideFlag =   jQuery(next).html().trim();
	jQuery(table).findTableColHide(colHideFlag);
}
// 初始化绑定表格
var initSizeChartTable=function(flag){
	if(!flag)flag='.size-chart-inch';
	var obj = jQuery(flag);
	if(obj && obj.length>0){
		jQuery(obj).setSizeChartTable();
	}
};
/**
 *  end
 */
/**
 * 色卡选中后给值给select
 */
var giveColorValue=function(color){
	var select = jQuery('select[title=color]');
	color = color.toLowerCase();
	if (select && select.length > 0) {
		jQuery(select).find('option').each(function () {
			var val = jQuery(this).attr('as');
			if (val == color) {
				jQuery(this).attr('selected', true);
			}else{
				jQuery(this).attr('selected', false);
			}
		});
	}
};
/**
 * 色卡选取后填入select，并着色
 * @param flag
 */
var bindColorChart=function(flag){
	if(!flag)flag='.zs_colorlink';
	jQuery(flag).each(function(){
		jQuery(this).on('click',function(){
			jQuery('.swatches-customoptions-list-item').each(function(){ jQuery(this).removeClass('lion'); });
			jQuery(this).parent('li').addClass('lion');
			giveColorValue(jQuery(this).attr('rel'));
		});
	});
};
/**
 * 检查浏览器
 */
var browser={
	versions:function(){
		var u = navigator.userAgent;
		u= u.toLowerCase();
		return {
			webKit: u.indexOf('applewebkit') > -1, //苹果、谷歌内核
			mobile: !!u.match(/applewebkit.*mobile.*/), //是否为移动终端
			ios: !!u.match(/\(i[^;]+;( U;)? cpu.+mac os x/), //ios终端
			iPhone: u.indexOf('iphone') > -1 , //是否为iPhone或者QQHD浏览器
			iPad: u.indexOf('ipad') > -1, //是否iPad
			Safari: (u.indexOf('safari') > -1 && u.indexOf('ios/')==-1)
		};
	}()
};
//不执行
var noSay = function(e){ e.preventDefault(); };
//绑定fancybox弹框后续的整理
var beforeBindFancyBox=function(flag,type){
	var contont='.fancybox-inner';
	var btm = '.fancybox-close-botton';
	var obj = jQuery(contont);
	var _type = jQuery(flag).attr('type');
	flag=null;
	if(type==2){
		//jQuery('.fancybox-close-botton').append(navigator.userAgent);
		jQuery('body').css('overflow','hidden');
		jQuery('.fancybox-overlay').hide();
		jQuery(btm).css({'z-index':'9090','position':'fixed','float':'left','padding-bottom':'0px'});
		if(newIScroll_inner){ newIScroll_inner=null; }
		var newIScroll_inner = new IScroll(contont,{mouseWheel: true,scrollbars:true,
			probeType:  3,
			bounce: true,
			keyBindings: true,
			invertWheelDirection: false,
			momentum: true,
			fadeScrollbars: false,
			interactiveScrollbars: true,
			resizeScrollbars: true,
			shrinkScrollbars: false,
			click: false,
			preventDefaultException: { tagName:/.*/ }
		});
		var isIpo=(browser.versions.iPhone && browser.versions.Safari);
		 var setOrientation=	function() {
					var orient = Math.abs(window.orientation) === 90 ? 'landscape' : 'portrait';
					var cl = document.body.className;
					cl = cl.replace(/portrait|landscape/, orient);
					document.body.className = cl;
		};
		window.addEventListener('load', setOrientation, false);
		window.addEventListener('orientationchange', setOrientation, false);
		
		var rf=function(){
			var Ha = jQuery(window).height();
			var Hb = jQuery(btm).height();
			if(isIpo){
				jQuery(obj).css({'overflow':'hidden',height:'91%',bottom:'0px','-webkit-overflow-scrolling': 'touch'});
				if(_type=='ajax'){
					jQuery(btm).css({'margin-bottom':'0px'});
					//jQuery(btm).css({'bottom':'0px'});
				}else{
					jQuery(btm).css({'margin-bottom':'69px'});
					//jQuery(btm).css({'bottom':'69px'});
				}
				jQuery(btm).bind('touchmove',noSay);
				jQuery(obj).height(Ha-Hb-10);
				window.scrollTo(0,1);
			}else{
				jQuery(obj).css({'overflow':'hidden',bottom:'0px','padding-bottom':'0px','-webkit-overflow-scrolling': 'touch'});
				jQuery(obj).height(Ha-Hb);
			}
			newIScroll_inner.refresh();
		};
		setTimeout(rf, 0);
		setTimeout(rf, 50);
		setTimeout(rf, 100);
		setTimeout(rf, 200);
		setTimeout(rf, 500);
		jQuery('.fancybox-close,.fancybox-close-botton').each(function(){
			 jQuery(this).on('click',function(e){
				 jQuery.fancybox.close(true);
			 });
		 });
		bindColorChart();
	}else{
		rf = null;
		newIScroll_inner=null;
		jQuery('body').css('overflow','auto');
		jQuery(btm).unbind('touchmove',noSay);
	}
};
/**
 * bind fancybox
 * <a title="Close" class="fancybox-item fancybox-close" href="javascript:;">'+lG_close+'</a>
 */
var bindFancybox=function(flag){
	if(!flag)flag='.can-open';
	jQuery(flag).fancybox({
		openEffect  : 'none',
		closeEffect : 'none',
		width		: '100%',
		height		: '100%',
		scrolling :'no',
		margin :0,
		padding:0,
		closeBtn:true,
		topRatio:0,
		leftRatio:0,
		autoHeight:false,
		autoSize :false,
		tpl:{closeBtn : '<a title="Close" class="fancybox-item-bottom fancybox-close-botton" href="javascript:;">'+lG_close+'</a>'},
		afterShow: function() {
			beforeBindFancyBox(this,2);
		},afterClose:function(){
			beforeBindFancyBox(this,-2);
		}
	});
};
/**
 * 客户端存储修复
 * window.localStorage
 */
(function(window, document) {
	"use strict";
	var userData, attr, attributes;
	if (!window.localStorage && (userData = document.body) && userData.addBehavior) {
		if (userData.addBehavior("#default#userdata")) {
			userData.load((attr = "localStorage"));
			attributes = userData.XMLDocument.documentElement.attributes;
			window.localStorage = {
				"length" : attributes.length,
				"key" : function(idx) { return (idx >= this.length) ? null : attributes[idx].name; },
				"getItem" : function(key) { return userData.getAttribute(key); },
				"setItem" : function(key, value) {
					userData.setAttribute(key, value);
					userData.save(attr);
					this.length += ((userData.getAttribute(key) === null) ? 1 : 0);
				},
				"removeItem" : function(key) {
					if (userData.getAttribute(key) !== null) {
						userData.removeAttribute(key);
						userData.save(attr);
						this.length = Math.max(0, this.length - 1);
					}
				},
				"clear" : function() {
					while (this.length) { userData.removeAttribute(attributes[--this.length].name); }
					userData.save(attr);
				}
			};
		}
	}
})(this, this.document);
/**
 * 客户端存储和自动加载
 * static cached,can not across domain.
 * usage:
 * $('div-id').localCached('http://www.google.com');
 */
(function($){
	$.fn.localCached=function(url,flush,callback,parant){
		var that = $(this);
		if(that && that.length>0){
			var id = $(that).attr('id');
			if(!id)id =$(that)[0].tagName+$(that).attr('class');
			if(flush)window.localStorage.clear();
			var data = window.localStorage.getItem(id);
			if(data && data.length>0){
				$(that).append(data);
				if(parant){ $(parant).append($(that)); }
				if(typeof callback == 'function'){
					callback();
				}
			}else{
				$.fn.localCached.getFormServer(url,that,id,parant,false,callback);
			}
		}
	};
	$.fn.localCached.getFormServer = function(url,target,id,parant,noCache,callback){
		if(!noCache){var noCache=false;}
		$.ajaxSetup({cache: true});
		$.get(url,function(data) {
			$(target).append(data);
			if(parant){ $(parant).append($(target)); }
			if(!noCache){
				window.localStorage.setItem(id,data);
			}
			if(typeof callback == 'function'){
				callback();
			}
		});
	};
	$.fn.autoload=function(param,callback){
		var href_id = $(this).attr('href');
			if(href_id && href_id.indexOf('#')===0){
				var div_id=href_id.substr(1);
				var $html = $('<div id="'+div_id+'"></div>').css({display:'none'});
				$html.localCached('/catalog/viewed/help/what/'+param,debug,callback,'body');
		}
	};
	$.fn.autoNewload=function(param,callback){
		var href_id = $(this).attr('href');
		if(href_id && href_id.indexOf('#')===0){
			var div_id=href_id.substr(1);
			var $html = $('<div id="'+div_id+'"></div>').css({display:'none'});
			$html.localCached.getFormServer('/catalog/viewed/help/what/'+param,$html,href_id,'body',true,callback);
		}
	};
})(jQuery);
/**
 *整站页面效果处理详细内容
 */
jQuery(function($){
	   if(!isLogind){ Mage.Cookies.set('u_u_in',0); }else{ Mage.Cookies.set('u_u_in',1); }
		//top - left menu
		var nav_menu_link = $('#nav-menu-link');
	   if(nav_menu_link && nav_menu_link.length>0){
		   $(nav_menu_link).on('click',function(){
			   if($(this).parent('li') && $(this).parent('li').hasClass('current')){
				   $(this).parent('li').removeClass('current');
			   }else{
				   $(this).parent('li').addClass('current');
			   }
		   });
		   var nav_menu_link_li = $(nav_menu_link).parent('li');
		   if(nav_menu_link_li && nav_menu_link_li.length>0){
			   $(nav_menu_link_li).on('click',function(){
				   if($(this).hasClass('current')){
					   $(this).removeClass('current');
				   }else{
					   $(this).addClass('current');
				   }
			   });
		   }
	   }

		var menuObj = $('nav#menu');
		if(menuObj && menuObj.length>0) {
			var initNavMenu = function () {
				$(menuObj).mmenu({
					"extensions": [
						"border-full",
						// "effect-menu-zoom",
					  //	"effect-listitems-zoom",
						null,
						"iconbar",
						"pageshadow",
						"theme-white",
						"fullscreen"
					],
					"offCanvas": {
						"position": "top"
					},
					//"autoHeight": true,
					//"counters": true,
					"navbars": [
						{
							"position": "top",
							"content": [
								"prev",
								"title",
								"close"
							]
						},
						{
							"position": "bottom",
							"content": [
								"<a id=\"mmenu-botton-close\" href=\"#mm-0\">"+LG_close_menu+"</a>",
							]
						}
					]
				});
				$(menuObj).on("opened.mm", function() {
					$('body').bind('touchmove',noSay);
				});
				$(menuObj).on('closed.mm', function () {
					$('body').unbind('touchmove',noSay);
				});
			}
			$(menuObj).localCached('/catalog/viewed/help/what/menu', debug, initNavMenu);
		}

		// top - left menu end
		var isLoginUrl =false;
		var isRegisterUrl =false;
		//login from
		if(checkHide('customer/account/login')<0) {
			$('.nav-main > .account-login').hide();
		}else{
			isLoginUrl =true;
		}

		if(checkHide('customer/account/create')<0){
			$('.account-create').hide();
			$('#button-register').removeAttr('onclick');
			$('#register-back-link').removeAttr('onclick');
			$('#register-back-link').removeAttr('href');
		}else{
			$('#button-register').removeAttr('href');
			isRegisterUrl = true;
		}


		$('#button-register').on('click',function(){
			if(checkNowLogin()){
				hideLogin();
				hideRegister();
				alert(LG_have_login);
			}else{
				hideLogin();
				showRegister();
				back_to_login=true;
			}
			//openFancyBox('.account-create');
			return false;
		});

		$('.account-back').live('click',function(){ 
			if(isLoginUrl || isRegisterUrl){ window.history.back(); return; }
			if(back_to_login){
				hideRegister();
				showLogin();
				back_to_login=false;
			}else{
				showPage();
			}
		});


		if(!isLogind ){ //&& !isSsl()
		$('#to-login').on('click',function(e){
			if(checkNowLogin()){ return true; }
			if(!isLoginUrl) {
				if ($(this).parent('li').hasClass('active')) {
					hideLogin();
					if (!isRegisterUrl)hideRegister();
				} else {
					showLogin();
				}
			}
			e.preventDefault();
			return false;
			//window.location.href =location.protocol+'//'+ location.host + '/customer/account';
		});}


		if($('#register-back-link')){
		$('#register-back-link').on('click',function(){
			hideRegister();
			showLogin();
			//openFancyBox('.account-login');
			return false;
		});
		}
		if($('#back')){
		$('#back').on('click',function(){
			if($('.belvg').find('.account-login').length>0){
				hideRegister();
				showLogin();
			}else if($('.belvg').find('.account-create').length>0){
				hideLogin();
				showRegister();
			}else{
				hideRegister();
				hideLogin();
			}
		});
		}



		//search
		$(".header-top .click-search").click(function() {
			if (!$(this).hasClass("current")) {
				$(this).addClass("current");
				$(".main-header .search-block").show();
			} else {
				$(this).removeClass("current");
				$(".main-header .search-block").hide();
			}
		});

		//product list mode
		var clist = $('.category-products ul.c-list');
		// init

		if(clist.length>0){
			var listMode = Mage.Cookies.get('listMode');
			if(listMode=='list'){
				$(clist).removeClass('grid').addClass('list');
			}else{
				$(clist).removeClass('list').addClass('grid');
			}
		}

		$('#setgrid').on('click',function(){
			$(clist).removeClass('list').addClass('grid');
			$(this).addClass('active');
			$('#setlist').removeClass('active');
			Mage.Cookies.set('listMode','grid');
		});
		$('#setlist').on('click',function(){
			$(clist).removeClass('grid').addClass('list');
			Mage.Cookies.set('listMode','list');
			$(this).addClass('active');
			$('#setgrid').removeClass('active');
		});


		//helper color
		var helper_color = $('#helper-color');
		if(helper_color && helper_color.length>0){
			$(helper_color).localCached('/catalog/viewed/help/what/color',debug,bindColorChart);
		}

		//helper size
		var helper_size = $('#helper-size');
		if(helper_size && helper_size.length>0){
			$(helper_size).localCached('/catalog/viewed/help/what/size',debug,initSizeChartTable);
		}

		//back
		var backBtn = $('.breadcrumbs li.back,.login-back');
		if(backBtn && backBtn.length>0){
			$(backBtn).on('click',function(){
				window.history.back();
			});
		}

		$('.breadcrumbs li').click(function(){
			$(this).siblings().each(function(){
				if($(this).children('.sub') && $(this).children('.sub').length>0)
					$(this).children('.sub').hide();
			});
			if($(this).children('.sub') && $(this).children('.sub').length>0)
			$(this).children('.sub').toggle();
		});

		//bottom-links
		var bottomLink = $('.bottom-helper-links');
		if(bottomLink && bottomLink.length>0){
			$(bottomLink).localCached('/catalog/viewed/help/what/bottomlink',debug);
		}

		//load helper exchange-refund
		$('.select-exchange').autoload('exchange');
		//load helper faq
	    $('.select-faq ').autoload('faq');

		//load review
		var review_callback=function(){
			jQuery('.star').rating();
		 };
		$('#review-my').autoNewload('review/id/'+$('input[name=product]').val(),review_callback);


		//toolbar fixed
		var toolbar = $('.toolbar .pager');
		if(toolbar && toolbar.length>0){
			toolbar = toolbar[0];
			$(toolbar).css({'background-color':'#fff','padding-top':'5px'}).scrollToFixed();
		}

	//go Top and history fixed

	   var gotoObj = $('.actGotop');
		var backtop = $('.backtop');

	 if(gotoObj && gotoObj.length>0){
		//if history not empty
		var _history_link=$('.history-footer a:first');
		if(_history_link && _history_link.length>0){
			var s = $(_history_link).html();
			s= s.match(/\d+/ig);
			if(s>0){ $('#view-history').addClass('has'); }
		}else{
			$('#view-history').hide();
		}

		$(window).scroll(function() {
			if($(window).scrollTop() >= 100){
				$(gotoObj).fadeIn(300);
			}else{
				$(gotoObj).fadeOut(300);
			}
		});
	 }
	if(backtop && backtop.length>0) {
		$(backtop).click(function () {
			$('html,body').animate({scrollTop: '0px'}, 800);
		});
	}
		//init window goto top
	   //	$(backtop).trigger('click');

	//load history
	$('#view-history').autoNewload('history');

	// .fav-atc addTo wishlist
	var wishlists =  $('.fav-atc');
	if(wishlists && wishlists.length>0){
		$(wishlists).each(function(){
		   $(this).on('click',function(){
			     if(checkNowLogin()){
					 var _url= $(this).attr('href');
					 if(_url){
					 var s = $(this).html();
					 var n =  s.match(/\d+(\.\d+)?/g);
					 n = parseInt(n);
					 if(!isNaN(n))
					 $(this).html(s.replace(n,n+1));
						 var pos = _url.indexOf('product');
						 if(pos>0){
							 var param = _url.substr(pos);
							 var _url = '/catalog/viewed/help/what/wishlist/'+param;
							 $.get(_url, function ($data){
								 //console.log($data);
							 });
						 }
					 $(this).removeAttr('href');
					 $(this).unbind('click');
					 $(this).addClass('added');
					 }
				 }else{
					 //openFancyBox('.account-login');
					 showLogin();
				 }
			   return false;
		   });

		});
	}
	// use bind form submit
	var bindFormSubmit=function(formSelector,callback,act){
		var _form = $(formSelector);
		if(_form && _form.length>0){
			$(_form).submit(function(event){
				if(!_form.validator){
					var validator  = new Validation($(_form).attr('id'));
					var _r = validator.validate();
					if(!_r)return false;
				}
				myOverLay('show');
				var _url = '/catalog/viewed/help/what/'+act;
				$.post(_url,$(_form).serialize(),function(data){
					myOverLay('hide');
					if(data && data!=1){
						$.MsgBox.Alert(data);
					}else if(data==1){
						if(typeof callback=='function'){ callback(data); }
					}
				});
				event.preventDefault();
				return false;
			});
		}
	};

	// post login
	var hideIfLogin=function(){
		if(checkNowLogin()){
		hideLogin();iconLight('.account-icon .i2');
		var _out_link=$('.login-reg a:first');
		if(_out_link && _out_link.length>0)$(_out_link).attr('href','/customer/account/logout').html(LG_logout);
		if(isLoginUrl)window.history.back();
	}};
	bindFormSubmit('form#login-form',hideIfLogin,'in');
	if(checkNowLogin()){iconLight('.account-icon .i2');}
	 /**/
	
	//post register
	var hideIfRegister=function(){ if(checkNowLogin()){ hideRegister(); iconLight('.account-icon .i2');
		var _out_link=$('.login-reg a:first');
		if(_out_link && _out_link.length>0)$(_out_link).attr('href','/customer/account/logout').html(LG_logout);
		if(isRegisterUrl)window.location.href='/customer/account';
	}};
	bindFormSubmit('form#form-validate',hideIfRegister,'nin');
	 /**/



	var _top_cart_icon = $('.cart-icon .i4');
	if(_top_cart_icon && _top_cart_icon.length>0){
		var s = $(_top_cart_icon).html();
		s= s.match(/\d+/ig);
		if(s>0){ iconLight(_top_cart_icon); }
	}

	//product view description
	var _archiver_item=$('.archiver_item:first');
	if(_archiver_item && _archiver_item.length>0){
		$(_archiver_item).hide();
		$(".productdes").on('click',function(){
			$(_archiver_item).slideToggle();
		});
	}

	//cart changes
	var _cart_form = $('#shopping-cart-form');
	if(_cart_form && _cart_form.length>0){
		$('select[id^="cart-item-qty"]').each(function() {
			$(this).bind('change',function(){
				$(_cart_form).submit();
			});
		});
	}

	//helper-center page
	var _details_helper_a = $(".details-title-two:last");
	if(_details_helper_a && _details_helper_a.length>0){
		$(_details_helper_a).addClass("last");
	}
	var _content_helper_b=$(".help-content-box:last");
	if(_content_helper_b && _content_helper_b.length>0){
		$(_content_helper_b).addClass("last");
	}
	var _product_helper_c=$(".product-help-box .details-title-two");
	if(_product_helper_c && _product_helper_c.length>0){
	$(".product-help-box .details-title-two").click(function() {
		if($(this).next(".help-content-box"))
		$(this).next(".help-content-box").slideToggle();
	});
	}

	$('select.select-overlay').selectBox();
	//bind all fancybox
	bindFancybox();
	
	var _swiper_container = $('.swiper-container');
	
	if(_swiper_container && _swiper_container.length>0){
		var _pagination = $(_swiper_container).next('.pagination');
		_swiper_container = '.swiper-container';
		if(_pagination && _pagination.length>0){ _pagination='.pagination';}else{ _pagination=null; }
		var mySwiper = new Swiper(_swiper_container,{
			pagination: _pagination,
			speed:750,
			loop:true,
			grabCursor: true,
			initialSlide:0,
			loopedSlides:0,
			slidesPerView:1,
			//loopAdditionalSlides:0,
			paginationClickable: true
		});
		if($(_swiper_container+' .arrow-left')){
			$(_swiper_container+' .arrow-left').on('click', function(e){
				mySwiper.swipePrev();
				e.preventDefault();
			  });
		}
		if($(_swiper_container+' .arrow-right')){
			$(_swiper_container+' .arrow-right').on('click', function(e){
				mySwiper.swipeNext();
				e.preventDefault();
			  });
		}
	}

	return true;

});