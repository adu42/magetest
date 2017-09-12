// 需要jQuery支持
// 2014-4-20 by 杜兵 114458573@qq.com
//标题标识,根据不同站翻译
var _unitInch='polegada';
var _unitCm='cm';
var _customSizeOptionFlag='tamanho sob medida'; //自定义size标识
var _jacketOptionFlag='com jaqueta'; //衣服带夹克标识

//翻译en
function _cnstra(key){
    var lan = {"bust":"Busto","waist":"Cintura","hips":"Quadris","hollow to floor":"Do pescoço ao Chão","shoulder (inch)":"Ombro(polegada)","armseye (inch)":"Cava(polegada)","armhole (inch)":"Cava (polegada)","arm length (inch)":"Comprimento do braço (polegada)","height":"Altura"};
    key=key.toLowerCase();
	key=jQuery.trim(key);
	return (lan[key]!=undefined)?lan[key]:key;
}

//下面不需要翻译
var _inch='inch';
var _cm='cm';
var _sizeLable='size';//尺码标题
var _sizeLables=["hips","hollow to floor","height","bust","waist","measurement unit"];
var _sizeUnitLable='measurement unit';
var _jacketLable='jacket';//夹克标题
var _jacketLablesStartWith=["shoulder","armseye","arm length"];
//元素标识，固定
var _sizeUnitFlag='#sizeUnit'; //尺码单位元素标识
var _mainProductOptionsContainer='.product-options'; //主商品options容器
var _optionsContainer='.divShowSizeVals';//尺码展示容器
var _optionsPopContainer='.product-details-options'; //相关商品options容器
var _productTypeContainer='.related-product-type'; //商品类别容器
var _sizeTypeGuide='guide'; //向导图片标识
var _sizeHeight='height';
var _relatedCheckboxFlag='input.related-checkbox'; //相关产品的选中域
var _relatedOptionsSubmitLable='  ok  ';
var _relatedOptionsCancelLable='cancel';
var _optionTypeGuide={1:{"image":"/js/jquery/pt/images/guide_weddingdress.png","flag":"waist","bigImage":"/js/jquery/pt/images/68-large.png"},2:{"image":"/js/jquery/pt/images/guide_jacket.png","flag":"shoulder","bigImage":"/js/jquery/pt/images/guide-jacket-large.png"}};
var _mainProductOptionsContainerWidth=450;
var _relatedProductOptionsContainerWidth=400;


jQuery(document).ready(function(){
    //绑定size事件
	resetSliderMoveWidth();
	setInit();
    setRelatedOptionsInit();
	initRalatedProductsOldTotalPrice()
});


function getSelectText(selectObj){
   return getLowerString(jQuery(selectObj).find('option:selected').text());
}

function getSelectValue(selectObj){
   return getLowerString(jQuery(selectObj).find('option:selected').val());
}

function getLabel(lableObj){
    var _lable=jQuery(lableObj).attr('for');
	if(_lable!=null){
    return getLowerString(_lable);
	}else{
	return '';
	}
}

function getStartWord(str,sper){
    var _str=str.split(sper);
	return jQuery.trim(_str[0]);
}

function getSelectLabel(selectObj){
   var _lable='';
   if(jQuery(selectObj).attr('type')=='radio'){
        _lable=jQuery(selectObj).parent().parent('dd').prev('dt').children('label').html();
   }else{
        _lable=jQuery(selectObj).parent().parent('dd').prev('dt').children('label').attr('for');
   } 
   return getLowerString(_lable);
}


function getDtSelectLabel(dtObj){
   var _lable=jQuery(dtObj).children('label').attr('for');
   return getLowerString(_lable);
}


function getLowerString(str){
	if(str!=undefined){
    return jQuery.trim(str.toLowerCase().replace('<em>*</em>',''));
	}else{
	return '';
	}
}

function isSizeSelectedCustomSize(obj){
	var isok=false;
	if(jQuery(obj).find('select').length>0){
		jQuery(obj).find('select').each(function(){
			 if(getSelectText(jQuery(this))==_customSizeOptionFlag){
				isok = true;
			 	return false;
			 }
		});
	}
    return isok;
}

function _isMainProductOptions(obj){
	var _findFlag=jQuery(obj).parents('div'+_mainProductOptionsContainer);
	if(_findFlag.length>0){
		return true;		
	}
	return false;
}


function _isRelatedProductOptions(obj){
	var _findFlag=jQuery(obj).parents('div'+_optionsPopContainer);
	if(_findFlag.length>0){
		return true;		
	}
	return false;
}

function CustomJacket(obj){
     var selectValue=getSelectText(obj);
	 if(selectValue.startWith(_jacketOptionFlag)){
		 showhideSizeInputs(obj,'show');
		 setJecketValueDefault(obj,'');
	 }else{
	    showhideSizeInputs(obj,'hide');
		setJecketValueDefault(obj);
		setJecketValueDefault(obj,'0');
	 }
}

function CustomSize(obj){
		var unit=getSizeUnit();
		if(unit==undefined)unit=_inch;
		var size=getSelectText(obj);
		if(size.startWith(_customSizeOptionFlag)){ 
			hideDivShowSizeVals(obj); //隐藏赋值层
			showhideSizeInputs(obj,'show'); //显示各表单域
			setCustomSizeValueDefault(obj);			
		}else if(getSelectValue(obj)!=''){
			size = getStartWord(size,' ');
			setCustomSizeVals(obj,size,unit);//赋值给表单域
			showhideSizeInputs(obj,'hide'); //隐藏各表单域
			setDivShowSizeVals(obj,size,unit);//显示赋值层			
		}else{
		       showhideSizeInputs(obj,'hide'); //隐藏各表单域
		       hideDivShowSizeVals(obj); //隐藏赋值层
		}
}
function setCustomSizeValueDefault(obj){
	jQuery(obj).parents('dd:eq(0)').find('.input-box-'+_sizeLable+'-guide').find('input:text').each(function() {
		jQuery(this).val('');
    });	
}
function setJecketValueDefault(obj,v){
	jQuery(obj).parents('dd:eq(0)').find('.input-box-'+_jacketLable+'-guide').find('input:text').each(function() {
        jQuery(this).val(v); 
    });	
}

function ucFirst(string) {
    return string.substring(0, 1).toUpperCase() + string.substring(1).toLowerCase();
}


String.prototype.startWith=function(str){     
  var reg=new RegExp("^"+str);     
  return reg.test(this);        
} 

function mvSelectInputDtDdToGuide(dtObj){
    var _objdt=jQuery(dtObj).clone();
    var _objdd=jQuery(dtObj).next('dd').clone();
	jQuery(dtObj).next('dd').remove();
    jQuery(dtObj).remove();
    return jQuery(_objdt).get(0).outerHTML+jQuery(_objdd).get(0).outerHTML;
}

function createSizeGuideFromSelect(obj,_lable){
    var _guide='<div class="input-box-'+_lable+'-guide" style="display:none;"><dl>';
	var _guideImage='';
    jQuery(obj).parents('dd:eq(0)').siblings('dt').each(function(){
                     var _label_as=getDtSelectLabel(jQuery(this));
					 if(_guideImage==""){
					 	_guideImage=getGuideDivByLabelFlag(_label_as);
					 }
                     if(jQuery.inArray(_label_as,_sizeLables)>-1){
                        _guide += mvSelectInputDtDdToGuide(jQuery(this));
                     }
    });
	_guide+='</dl>';
	_guide+=_guideImage;
    _guide+='</div>';
	if(jQuery(obj).find('.input-box-'+_lable+'-guide').length==0){
    appendToGuide(obj,_guide);
	if(_guideImage!=''){
		bindGuideImageClick(obj);
	}
	}
	//appendToGuide(obj,_guideImage);
    //return _guide;
}

function createJacketGuideFromLabel(obj,_lable){
    var _guide='<div class="input-box-'+_lable+'-guide" style="display:none;"><dl>';
	var _guideImage='';
    jQuery(obj).parents('dd:eq(0)').siblings('dt').each(function(){
             var _label_as=getDtSelectLabel(jQuery(this));
             _label_as=getStartWord(_label_as,' (');
			 if(_guideImage==""){
				_guideImage=getGuideDivByLabelFlag(_label_as);
			 }
             if(jQuery.inArray(_label_as,_jacketLablesStartWith)>-1){
                _guide += mvSelectInputDtDdToGuide(jQuery(this));
             }
    });
	_guide+='</dl>';
	_guide+=_guideImage;
    _guide+='</div>';
	if(jQuery(obj).find('.input-box-'+_lable+'-guide').length==0){
    appendToGuide(obj,_guide);
	if(_guideImage!=''){
		bindGuideImageClick(obj);
	}
	}
    //appendToGuide(obj,_guideImage);
}
//
function appendToGuide(obj,_guide){
    jQuery(obj).parents('dd:eq(0)').append(_guide);
}


//jQuery("select").each(function(){
//设置默认值
function setInit(){ //select obj
    _resetAllFormEv();
    jQuery("select").each(function(){
            var _label=getSelectLabel(jQuery(this));
            if(_label==_sizeLable){
				jQuery(this).parent('div.input-box').addClass('has-guide');
                createSizeGuideFromSelect(jQuery(this),_label); //转移属性到单独层
                jQuery(this).bind('change',function(){  //绑定事件
					CustomSize(jQuery(this));
				});
               // showhideSizeInputs(jQuery(this),'hide'); //默认隐藏单独层
			    resetMainUnitFromSizeUnit();
	            hideDivShowSizeVals(jQuery(this)); //隐藏赋值层
            }else if(_label==_jacketLable){
				jQuery(this).parent('div.input-box').addClass('has-guide');
                createJacketGuideFromLabel(jQuery(this),_label);  //转移属性到单独层
                jQuery(this).bind('change',function(){  //绑定事件
					CustomJacket(jQuery(this));
				});
                showhideSizeInputs(jQuery(this),'hide');
            }
        });
}

function _resetAllFormEv(){
    jQuery('label').each(function(){
		var _label=getLabel(jQuery(this));
		if(_label==_sizeUnitLable && _isMainProductOptions(jQuery(this))){
			  var _inputRadio=jQuery(this).parents('dt:eq(0)').next('dd').find('input:first');
			  _inputRadio.prop('checked',true);
			  setSizeUnit(_inputRadio);
			  jQuery(this).parents('dt:eq(0)').next('dd').find('input[type=radio]').each(function(){
					//if(jQuery(this).parent().next().children().html()!=null){
						jQuery(this).removeAttr('onclick');
						jQuery(this).attr('onclick','setSizeUnit(this)');
						//jQuery(this).bind('click',function(){ 
						//alert('=======');
						//setSizeUnit(this); 
						//});
					//}
					//if(jQuery(this).parent().prev().children().html()!=null){
					//	jQuery(this).bind('click',function(){ setSizeUnit(jQuery(this)); });
					//}
			  });
		}else{
		  var _input = jQuery(this).parent().next('dd').find('input');
			resetFormEv(_input);
			var _input_select = jQuery(this).parent().next('dd').find('select');
			resetFormEv(_input_select);
		}
		});
}

function resetSliderMoveWidth(){
			var w1=0;
			var w2=0;
			if(jQuery('ol.slider-move').find('li.item_and').length>0){
			 w1 = jQuery('ol.slider-move').find('li.item_and').length * jQuery('ol.slider-move').find('li.item_and').width();
			}
			if(jQuery('ol.slider-move').find('li.item').length>0){
			w2 = jQuery('ol.slider-move').find('li.item').length * (jQuery('ol.slider-move').find('li.item').width()+10);
			}
			jQuery('ol.slider-move').width(w1 + w2);
}

function resetFormEv(obj){
	if(obj.length>0 && obj.attr('type')!='radio' && obj.attr('type')!='checkbox'){
		jQuery(obj).each(
			function(){
				jQuery(this).val('');
			}	
		);
	}
}

function setValueFromLable(obj,value){ //labelObj
    if(jQuery(obj).parent().next('dd').find('input:eq(0)').length>0){
         if(jQuery(obj).parent().next('dd').find('input:eq(0)').attr('type')=='radio'){
            if(value==1){
                jQuery(obj).parent().next('dd').find('input:first').prop('checked',true);
            }else{
                jQuery(obj).parent().next('dd').find('input:last').prop('checked',true);
            }
            return ;
        }
        jQuery(obj).parent().next('dd').find('input:eq(0)').val(value);
    }else if(jQuery(obj).parent().next('dd').find('select:eq(0)').length>0){
        jQuery(obj).parent().next('dd').find('select:eq(0)').val(value);
    }else if(jQuery(obj).parent().next('dd').find('textarea:eq(0)').length>0){
        jQuery(obj).parent().next('dd').find('textarea:eq(0)').val(value);
    }
}



//给子尺码赋值，但是不做显示操作
function setCustomSizeVals(obj,size,unit){
	var values=getSizeVals(size,unit);
    var _obj=jQuery(obj).parents('div:eq(0)').next('div.input-box-'+_sizeLable+'-guide,div.input-box-'+_jacketLable+'-guide');
    if(_obj.length>0){
        jQuery(_obj).find('label').each(function(){
            var _lable=getLabel(jQuery(this));
            if(jQuery.inArray(_lable,_sizeLables)>-1){
                if(_lable==_sizeHeight){
                    setValueFromLable(jQuery(this),0);
                }else if(_lable==_sizeUnitLable){
                    if(unit==_inch){
                        setValueFromLable(jQuery(this),1);
                    }else{
                        setValueFromLable(jQuery(this),2);
                    }
                }else{
                    _lable=ucFirst(_lable);
                    if(values[_lable]!=undefined){
                        setValueFromLable(jQuery(this),values[_lable]);
                    }
                }
            }
        });
    }	
}
//赋值给显示层
function setDivShowSizeVals(obj,size,unit){
    if(jQuery(obj).parents('dl:eq(0)').find(_optionsContainer)==null){
        return ;
    }
	if(_isMainProductOptions(obj)){ //只有主
	var valuesObj=getSizeVals(size,unit);
    if(valuesObj!=''&&valuesObj!=undefined){
		var _unit=(unit==_inch)?_cm:_inch;
		var _valuesObj=getSizeVals(size,_unit);
		var htmlTxt='';
		var noElm=false;
        //显示inch
        var leftInch='';
        var leftBegin='<div id="sizeUnitTitleLeft"><div class="sizeUnitTitle">&hearts; '+_unitInch+':</div><div class="sizeUnitContents">';
        //显示cm
        var rightCm='';
        var rightBegin='<div id="sizeUnitTitleRight"><div class="sizeUnitTitle">&hearts; '+_unitCm+':</div><div class="sizeUnitContents">';
        var endDiv='</div></div>';
        for(var key in valuesObj){
            leftInch+='<div><font style="font-weight:bold">'+_cnstra(key)+':</font>'+valuesObj[key]+'</div>';
            if(_valuesObj[key]!=undefined){
			   rightCm+='<div><font style="font-weight:bold">'+_cnstra(key)+':</font>'+_valuesObj[key]+'</div>';
			}
            noElm=true;
        }
        if(noElm){
            if(unit==_inch){
                htmlTxt=leftBegin+leftInch+endDiv+rightBegin+rightCm+endDiv;
            }else{
                htmlTxt=leftBegin+rightCm+endDiv+rightBegin+leftInch+endDiv;
            }
            jQuery(obj).parents('dl:eq(0)').find(_optionsContainer).html(htmlTxt).show();
        }
	}}
}
//隐藏赋值层
function hideDivShowSizeVals(obj){
    if(jQuery(obj).parents('dl:eq(0)').find(_optionsContainer).length>0){
	   jQuery(obj).parents('dl:eq(0)').find(_optionsContainer).hide();
    }
}

//选择单位
function setSizeUnit(obj){
	var val=jQuery(obj).next('span').children('label').html();
	val=jQuery.trim(val);
	jQuery(_sizeUnitFlag).val(val);
	resetUnitFromSizeUnit(obj);
	resetRalatedUnitFromSizeUnit();
}
//获取单位，必须有隐藏域sizeUnit
function getSizeUnit(){
	var u=jQuery(_sizeUnitFlag).val();
	if(u==undefined||u==''){
		u=_unitInch;
	}else{
		u=u.toLowerCase();
	}
	if(u==_unitInch){
		u=_inch;
	}else{
		u=_cm;
	}
	return jQuery.trim(u);
}
//单位换算inch to cm
function inchTocm(v){
	return (2.54*v).toFixed(2);
}
//单位换算cm to inch
function cmToinch(v){
    return (0.3937*v).toFixed(2);
}

function resetMainUnitFromSizeUnit(){
	var unit=getSizeUnit();
	if(jQuery(_mainProductOptionsContainer).length>0){
		jQuery(_mainProductOptionsContainer).each(function(){
			 jQuery(this).find('label').each(function() {
				var _label=getLabel(jQuery(this));
				if(_label==_sizeUnitLable){
					if(unit==_inch){
						setRalatedUnitFromLable(jQuery(this),1);
                    }else{
						setRalatedUnitFromLable(jQuery(this),2);
                    }
					resetUnitFromSizeUnit(jQuery(this));
					return false; 
				}
            });
		});
	}
}

function setRalatedUnitFromLable(obj,num){ //labelObj
	if(num==1){
		jQuery(obj).parent().next('dd').find('input:first').prop('checked',true);
		jQuery(obj).parent().next('dd').find('input:last').prop('checked',false);
	}else if(num==2){
		jQuery(obj).parent().next('dd').find('input:last').prop('checked',true);
		jQuery(obj).parent().next('dd').find('input:first').prop('checked',false);
	}
}

function resetRalatedUnitFromSizeUnit(){
	var unit=getSizeUnit();
	if(jQuery(_optionsPopContainer).length>0){
		jQuery(_optionsPopContainer).each(function(){
			 jQuery(this).find('label').each(function() {
				var _label=getLabel(jQuery(this));
				if(_label==_sizeUnitLable){
					if(unit==_inch){
						setRalatedUnitReadOnlyFromLable(jQuery(this),1);
                    }else{
						setRalatedUnitReadOnlyFromLable(jQuery(this),2);
                    }
					resetUnitFromSizeUnit(jQuery(this));
					return false; 
				}
            });
		});
	}
}


function setRalatedUnitReadOnlyFromLable(obj,num){ //labelObj
	if(num==1){
		jQuery(obj).parent().next('dd').find('input:first').attr('disabled',false);
		jQuery(obj).parent().next('dd').find('input:last').attr('disabled',true);
		jQuery(obj).parent().next('dd').find('input:first').prop('checked',true);
		jQuery(obj).parent().next('dd').find('input:last').prop('checked',false);
	}else if(num==2){
		jQuery(obj).parent().next('dd').find('input:first').attr('disabled',true);
		jQuery(obj).parent().next('dd').find('input:last').attr('disabled',false);
		jQuery(obj).parent().next('dd').find('input:last').prop('checked',true);
		jQuery(obj).parent().next('dd').find('input:first').prop('checked',false);
	}
}

function resetUnitFromSizeUnit(obj){
  var unit=getSizeUnit();
  var _unit=(unit==_inch)?_unitCm:_unitInch;
  var _dunit=(unit==_inch)?_unitInch:_unitCm;
	var _inputbox =jQuery(obj).parents('dd:eq(0)').siblings('dt').each(function(){
        var _labelObj=jQuery(this).find('label:eq(0)');
		var label=getLabel(_labelObj);
		if(jQuery.inArray(label,_sizeLables)>-1 && label!=_sizeUnitLable){
			var _inputbox =jQuery(this).next('dd').children('.input-box');
				if(_inputbox!=null){
					var _newDiv_inputbox =jQuery(_inputbox).html();
					if(_newDiv_inputbox!=null){
					if(_newDiv_inputbox.indexOf(_unit)!=-1){
						_newDiv_inputbox=_newDiv_inputbox.replace(_unit,_dunit);
					}else if(_newDiv_inputbox.indexOf(_dunit)==-1){
						_newDiv_inputbox=_newDiv_inputbox+' '+_dunit;
					}
					jQuery(_inputbox).html(_newDiv_inputbox);
				}}
		}
    });
}


//显示各小尺码表单域 act = ‘show’ 显示 ，‘hide’ 隐藏
function showhideSizeInputs(obj,act){
	var _unitInput;
	var unit=getSizeUnit();
	var _unit=(unit==_inch)?_cm:_inch;
    if(jQuery(obj).parents('div:eq(0)').next('div')){
        if(act=='show'){
            jQuery(obj).parents('div:eq(0)').next('div').show();
        }else{
            jQuery(obj).parents('div:eq(0)').next('div').hide();
        }
    }
}

function clearStyleBug(refer){
	if(jQuery('.slider-wrapper').length>0 ){
		 if(!refer && jQuery('.slider-wrapper').css('position')=='relative'){
		 	jQuery('.slider-wrapper').css('position','');
		 }else if(refer){
		 	jQuery('.slider-wrapper').css('position','relative');
		 }
	} 
}

function setContainerWidth(obj,isAuto){
	//if(_isMainProductOptions(obj)){
	//	jQuery(obj).parents('div'+_mainProductOptionsContainer).css({'width':_mainProductOptionsContainerWidth+'px'});
	//}else 
	if(_isRelatedProductOptions(obj)){
		if(isAuto){
		jQuery(obj).parents('div'+_optionsPopContainer).css({'width':'auto'});
		}else{
		jQuery(obj).parents('div'+_optionsPopContainer).css({'width':_relatedProductOptionsContainerWidth+'px'});
		}
	}
}



//设置选择相关产品事件
function setRelatedOptionsInit(){
    if(jQuery(_relatedCheckboxFlag).length>0){
        jQuery(_relatedCheckboxFlag).each(function(){
             setCurrentOptionsDivSubmitBotton(jQuery(this)); //加个按钮，点击关闭
             jQuery(this).click(function(){
                 var _currentOptionsDiv=getCurrentOptionsDiv(jQuery(this));
				 	clearStyleBug(false); //清除上级层的影响
                    var x=jQuery(this).position();
					var h=jQuery(_currentOptionsDiv).height();
					if(jQuery(_currentOptionsDiv).find('.image-guide').length>0 && isSizeSelectedCustomSize(jQuery(_currentOptionsDiv))){
						jQuery(_currentOptionsDiv).css({'width':_relatedProductOptionsContainerWidth+'px'});
						jQuery(_currentOptionsDiv).find('.image-guide span').click(function(){
							showBigImageGuide();
						});
					}else{
						jQuery(_currentOptionsDiv).css({'width':'auto'});
					}
                    jQuery(_currentOptionsDiv).css({'height':'auto'});
                    jQuery(_currentOptionsDiv).css("position","absolute");
                    jQuery(_currentOptionsDiv).css("left",x.left);
                    jQuery(_currentOptionsDiv).css("top",x.top-h+20);
                    jQuery(_currentOptionsDiv).css("z-index",99);
					jQuery(_currentOptionsDiv).css("padding",'10px');
					jQuery(_currentOptionsDiv).css("border",'1px solid #E7E7E7');
					jQuery(_currentOptionsDiv).css("background-color",'#FFFFFF');
                    jQuery(_currentOptionsDiv).show();				 
             });
        });
    }
	initHideAllRalatedOptionPopContainer();
}

function initHideAllRalatedOptionPopContainer(){
	jQuery(_optionsPopContainer).hide();
}

function getCurrentOptionsDiv(checkboxObj){
    return jQuery(checkboxObj).parents('li:eq(0)').find(_optionsPopContainer);
}

function setCurrentOptionsDivSubmitBotton(obj){
    var btnHtml='<div class="product-details-options-submit">'
                +'<button class="button btn-options-submit" title="submit" type="button"><span><span>'+_relatedOptionsSubmitLable+'</span></span></button>'
                +'&nbsp;&nbsp;'
				+'<button class="button btn-options-cancel" title="cancel" type="button"><span><span>'+_relatedOptionsCancelLable+'</span></span></button>'
                +'</div>';
	if(jQuery(obj).parents('li:eq(0)').find(_optionsPopContainer).find('.product-details-options-submit').length==0){	
    	jQuery(obj).parents('li:eq(0)').find(_optionsPopContainer).append(btnHtml);
		jQuery('.btn-options-submit').click(function(){
			 if(validateCurrentOptions(jQuery(this))){
				setLiInputRelatedCheckbox(jQuery(this),true);
				calculateRalatedProductsPrice(jQuery(this));
				jQuery(this).parents(_optionsPopContainer).hide();
			 }
		});
		jQuery('.btn-options-cancel').click(function(){
			 setLiInputRelatedCheckbox(jQuery(this),false);
			 calculateRalatedProductsPrice(jQuery(this));
			 jQuery(this).parents(_optionsPopContainer).hide();
		});
	}
}

function setLiInputRelatedCheckbox(obj,isChecked){
	var _objLi=jQuery(obj).parents('li:eq(0)').find(_relatedCheckboxFlag).get(0);
	if(isChecked){
		jQuery(_objLi).attr('checked',true);
	}else{
		jQuery(_objLi).attr('checked',false);
	}
}


function getPriceSpan(obj,oldPrice){
	if(oldPrice){
		if(jQuery(obj).find('p.old-price').find('span.price').length>0){
	  		return jQuery(obj).find('p.old-price').find('span.price');
		}else if(jQuery(obj).find('p.special-price').find('span.price').length>0){
			return jQuery(obj).find('p.special-price').find('span.price');
		}else if(jQuery(obj).find('div.price-box').find('span.price').length>0){
			return jQuery(obj).find('div.price-box').find('span.price');
		}
	}else{
	    if(jQuery(obj).find('p.special-price').find('span.price').length>0){	
	  		return jQuery(obj).find('p.special-price').find('span.price');
		}else if(jQuery(obj).find('div.price-box').find('span.price').length>0){
			return jQuery(obj).find('div.price-box').find('span.price');
		}
	}
	return false;
}

function getPriceSpanFromLi(obj,oldPrice){
	if(oldPrice){
		if(jQuery(obj).find('p.old-price').find('span.price').length>0){
			return getPriceFromString(jQuery(obj).find('p.old-price').find('span.price').html());
		}else if(jQuery(obj).find('p.special-price').find('span.price').length>0){
			return getPriceFromString(jQuery(obj).find('p.special-price').find('span.price').html());
		}else if(jQuery(obj).find('div.price-box').find('span.price').length>0){
			return getPriceFromString(jQuery(obj).find('div.price-box').find('span.price').html());	
		}else{
			return 0.00;
		}
	}else{
		if(jQuery(obj).find('p.special-price').find('span.price').length>0){
			return getPriceFromString(jQuery(obj).find('p.special-price').find('span.price').html());
		}else if(jQuery(obj).find('div.price-box').find('span.price').length>0){
			return getPriceFromString(jQuery(obj).find('div.price-box').find('span.price').html());
		}else{
			return 0.00;
		}
	}
}

function setPriceToSpan(obj,price){
	var _priceSpan=getPriceSpan(obj,false);
	var _priceTxt=jQuery.trim(jQuery(_priceSpan).html());
	var _oldPriceNumber=getPriceFromString(_priceTxt);
	_priceTxt=_priceTxt.replace(_oldPriceNumber,price);
	jQuery(_priceSpan).html(_priceTxt);
}

//原价总价会记，在全局第一的加载的时候初始化
function initRalatedProductsOldTotalPrice(){
	var _totalOldPrice=getPriceSpanFromLi(jQuery('div.price-box').get(0),true);
	resetSymbolTotal();
	if(jQuery(_relatedCheckboxFlag)){
 		var _ckboxObj = jQuery(_relatedCheckboxFlag).get(0);
		// _totalOldPrice+=getPriceSpanFromLi(jQuery(_ckboxObj).parents('li:eq(0)'),true);
		 initRalatedProductsPrice(jQuery(_ckboxObj).parents('li:eq(0)'));
		jQuery(_ckboxObj).parents('li:eq(0)').siblings('li.item').each(function() {
          //   _totalOldPrice+=getPriceSpanFromLi(jQuery(this),true);
			 initRalatedProductsPrice(jQuery(this));
        });
	}
	jQuery('span#symbol_total').attr('title',_totalOldPrice);
}

//click checkbox 的时候，初始化价格
function initRalatedProductsPrice(obj){
	var _priceObj = getPriceSpan(obj,false);
	_oldPrice=getPriceFromString(jQuery(_priceObj).html());
	jQuery(_priceObj).attr('title',_oldPrice);
}

function getPriceFromString(str){
	var _num = 0.00;
	if(str!=undefined){
		 _num = str.match(/\d+(\.\d)?/ig);
	}
	return parseFloat(_num);
}
function resetSymbolTotal(){
	var _totalPrice=getPriceSpanFromLi(jQuery('div.price-box').get(0),false);
	jQuery('span#symbol_total').html(_totalPrice);	
}
//click OK 的时候，获得价格，并且做更改
function calculateRalatedProductsPrice(obj){
	resetSymbolTotal();
	var _objLi=jQuery(obj).parents('li:eq(0)');
	var _priceObj = getPriceSpan(_objLi,false);
	var _newCurrentPrice=jQuery(_priceObj).attr('title');
	_newCurrentPrice=parseFloat(_newCurrentPrice);
	jQuery(_objLi).find('input','textarea').each(function(){
		 if(jQuery(this).attr('price')!=null && jQuery(this).val()!=""){
		 	 _newCurrentPrice += parseFloat(jQuery(this).attr('price'));
		 }	
	});
	jQuery(_objLi).find('select').each(function(){
		if(jQuery(this).val()!="" && jQuery(this).find('option:selected').attr('price')!=null){
			_newCurrentPrice += parseFloat(jQuery(this).find('option:selected').attr('price'));
		}	
	});
	setPriceToSpan(_objLi,_newCurrentPrice);
	setRalatedProductsTotalPrice(_objLi);
}

function replaceNumberInString(str,newnum){
	var oldNum=getPriceFromString(str);
	str=str.replace(oldNum,newnum);
	return str;
}

function setRalatedProductsTotalPrice(obj){
	var _totalPrice= parseFloat(jQuery('#symbol_total').html());
	var _totalOldPrice= parseFloat(jQuery('#symbol_total').attr('title'));
	var num=0;
	
	if(jQuery(obj).find(_relatedCheckboxFlag).attr('checked')){
			_totalPrice += getPriceSpanFromLi(jQuery(obj),false);
			_totalOldPrice+=getPriceSpanFromLi(jQuery(obj),true);
			num++;
	}
	jQuery(obj).siblings('li.item').each(function() {
		if(jQuery(this).find(_relatedCheckboxFlag).attr('checked')){
			_totalPrice += getPriceSpanFromLi(jQuery(this),false);
			_totalOldPrice+=getPriceSpanFromLi(jQuery(this),true);
			num++;
		}
    });
	if(num>0)num++;
	var _percent=_totalOldPrice-_totalPrice;
	_totalPrice=_totalPrice.toFixed(0);
	_percent=_percent.toFixed(0);
	jQuery('p.tt').html(replaceNumberInString(jQuery('p.tt').html(),num));
	jQuery('p.tm span.m').html(replaceNumberInString(jQuery('p.tm span.m').html(),_totalPrice));
	jQuery('p.ts').html(replaceNumberInString(jQuery('p.ts').html(),_percent));
}


function validateCurrentOptions(btnObj){
    var _flag=true;
    jQuery(btnObj).parents(_optionsPopContainer).find('input.required-entry,select.required-entry,textarea.required-entry').each(function(){
        if(jQuery(this).val()==''){
            jQuery(this).css('border','1px solid #ff0000');
            _flag=false;
        }else{
            jQuery(this).css('border','1px solid #009B00');
        }
    });
    return _flag;
}

function getGuideDivByLabelFlag(label){
	if(label==_optionTypeGuide[1]['flag']){
		return getGuideDiv(1);
	}else if(label==_optionTypeGuide[2]['flag']){
		return getGuideDiv(2);
	}
	return '';
}


function getGuideDiv(typeId){
	if(_optionTypeGuide[typeId]!=undefined){
		return '<div class="image-guide"><img src="'+_optionTypeGuide[typeId]["image"]+'"><span>+</span></div>'
		+'<div class="measurement_pop" style="position: fixed;margin:0 auto;display: none;left:20%;top:20px; width:808px;z-index:999;background-color: #FFFFFF;border: 1px solid #EEEEEE;">'
		+'<span class="closeme"></span><img src="'+_optionTypeGuide[typeId]["bigImage"]+'"/>'
		+'<br><b>Note: </b>For a better fitting on high heels, an extra 5cm will be added to those floor-length dresses and dresses with trains.'
		+'</div>'
		+'<div class="fullCover" style="width:100%; height:100%;position: fixed;z-index:29; display: none;opacity: 0.3;filter:alpha(opacity=30);background:#000;-ms-filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=30);left:0px;top:0px;"></div>';
	}
	return '';
}

function bindGuideImageClick(obj){
	if(_isMainProductOptions(obj) && jQuery(obj).parents(_mainProductOptionsContainer).find('.image-guide').length>0){
		jQuery(obj).parents(_mainProductOptionsContainer).find('.image-guide,.image-guide > span').each(function(){
			 jQuery(this).click(function(){
				showBigImageGuide(jQuery(this));
			});
		});
		jQuery(obj).parents(_mainProductOptionsContainer).find('.measurement_pop > img,.closeme,.fullCover').each(function() {
            jQuery(this).click(function(){
				hideBigImageGuide(jQuery(this));
			});
        });
		
	}
	if(_isRelatedProductOptions(obj) && jQuery(obj).parents(_optionsPopContainer).find('.image-guide').length>0){ 
		jQuery(obj).parents(_optionsPopContainer).find('.image-guide,.image-guide > span').each(function(){
			 jQuery(this).click(function(){
				showBigImageGuide(jQuery(this));
			});
		});
		jQuery(obj).parents(_optionsPopContainer).find('.measurement_pop > img,.closeme,.fullCover').each(function() {
            jQuery(this).click(function(){
				hideBigImageGuide(jQuery(this));
			});
        });
	}
	
}

function showBigImageGuide(obj){
	if(jQuery(obj).parents('dd:eq(0)').find('.fullCover:eq(0)').length>0){
		jQuery(obj).parents('dd:eq(0)').find('.fullCover:eq(0)').show();
	}
	if(jQuery(obj).parents('dd:eq(0)').find('.measurement_pop:eq(0)').length>0){
		jQuery(obj).parents('dd:eq(0)').find('.measurement_pop:eq(0)').get(0).show();
	}
	
}

function hideBigImageGuide(obj){
	if(jQuery(obj).parents('dd:eq(0)').find('.measurement_pop:eq(0)').length>0){
		jQuery(obj).parents('dd:eq(0)').find('.measurement_pop:eq(0)').get(0).hide();
	}
	if(jQuery(obj).parents('dd:eq(0)').find('.fullCover:eq(0)').length>0){
		jQuery(obj).parents('dd:eq(0)').find('.fullCover:eq(0)').hide();
	}
}

//获得固定尺码表
function getSizeVals(size,unit){
	size=size.toUpperCase();
	unit=unit.toLowerCase();
	
var sizes={"US_Size":{"US2-UK6-EUR32":{"inch":{"Bust":"32.5","Waist":"25.5","Hips":"35.75","Hollow to floor":"58"},"cm":{"Bust":"83","Waist":"65","Hips":"91","Hollow to floor":"147"}},"US4-UK8-EUR34":{"inch":{"Bust":"33.5","Waist":"26.5","Hips":"36.75","Hollow to floor":"58"},"cm":{"Bust":"85","Waist":"68","Hips":"93","Hollow to floor":"147"}},"US6-UK10-EUR36":{"inch":{"Bust":"34.5","Waist":"27.5","Hips":"37.75","Hollow to floor":"59"},"cm":{"Bust":"88","Waist":"70","Hips":"96","Hollow to floor":"150"}},"US8-UK12-EUR38":{"inch":{"Bust":"35.5","Waist":"28.5","Hips":"38.75","Hollow to floor":"59"},"cm":{"Bust":"90","Waist":"72","Hips":"98","Hollow to floor":"150"}},"US10-UK14-EUR40":{"inch":{"Bust":"36.5","Waist":"29.5","Hips":"39.75","Hollow to floor":"60"},"cm":{"Bust":"93","Waist":"75","Hips":"101","Hollow to floor":"152"}},"US12-UK16-EUR42":{"inch":{"Bust":"38","Waist":"31","Hips":"41.25","Hollow to floor":"60"},"cm":{"Bust":"97","Waist":"79","Hips":"105","Hollow to floor":"152"}},"US14-UK18-EUR44":{"inch":{"Bust":"39.5","Waist":"32.5","Hips":"42.75","Hollow to floor":"61"},"cm":{"Bust":"100","Waist":"83","Hips":"109","Hollow to floor":"155"}},"US16-UK20-EUR46":{"inch":{"Bust":"41","Waist":"34","Hips":"44.25","Hollow to floor":"61"},"cm":{"Bust":"104","Waist":"86","Hips":"112","Hollow to floor":"155"}},"US14W-UK18-EUR44":{"inch":{"Bust":"41","Waist":"34","Hips":"43.5","Hollow to floor":"61"},"cm":{"Bust":"104","Waist":"86","Hips":"110","Hollow to floor":"155"}},"US16W-UK20-EUR46":{"inch":{"Bust":"43","Waist":"36.25","Hips":"45.5","Hollow to floor":"61"},"cm":{"Bust":"109","Waist":"92","Hips":"116","Hollow to floor":"155"}},"US18W-UK22-EUR48":{"inch":{"Bust":"45","Waist":"38.5","Hips":"47.5","Hollow to floor":"61"},"cm":{"Bust":"114","Waist":"98","Hips":"121","Hollow to floor":"155"}},"US20W-UK24-EUR50":{"inch":{"Bust":"47","Waist":"40.75","Hips":"49.5","Hollow to floor":"61"},"cm":{"Bust":"119","Waist":"104","Hips":"126","Hollow to floor":"155"}},"US22W-UK26-EUR52":{"inch":{"Bust":"49","Waist":"43","Hips":"51.5","Hollow to floor":"61"},"cm":{"Bust":"124","Waist":"109","Hips":"131","Hollow to floor":"155"}},"US24W-UK28-EUR54":{"inch":{"Bust":"51","Waist":"45.25","Hips":"53.5","Hollow to floor":"61"},"cm":{"Bust":"130","Waist":"115","Hips":"136","Hollow to floor":"155"}},"US26W-UK30-EUR56":{"inch":{"Bust":"53","Waist":"47.5","Hips":"55.5","Hollow to floor":"61"},"cm":{"Bust":"135","Waist":"121","Hips":"141","Hollow to floor":"155"}},"2":{"inch":{"Bust":"32.5","Waist":"25.5","Hips":"35.75","Hollow to floor":"58"},"cm":{"Bust":"83","Waist":"65","Hips":"91","Hollow to floor":"147"}},"4":{"inch":{"Bust":"33.5","Waist":"26.5","Hips":"36.75","Hollow to floor":"58"},"cm":{"Bust":"84","Waist":"68","Hips":"92","Hollow to floor":"147"}},"6":{"inch":{"Bust":"34.5","Waist":"27.5","Hips":"37.75","Hollow to floor":"59"},"cm":{"Bust":"88","Waist":"70","Hips":"96","Hollow to floor":"150"}},"8":{"inch":{"Bust":"35.5","Waist":"28.5","Hips":"38.75","Hollow to floor":"59"},"cm":{"Bust":"90","Waist":"72","Hips":"98","Hollow to floor":"150"}},"10":{"inch":{"Bust":"36.5","Waist":"29.5","Hips":"39.75","Hollow to floor":"60"},"cm":{"Bust":"93","Waist":"75","Hips":"101","Hollow to floor":"152"}},"12":{"inch":{"Bust":"38","Waist":"31","Hips":"41.25","Hollow to floor":"60"},"cm":{"Bust":"97","Waist":"79","Hips":"105","Hollow to floor":"152"}},"14":{"inch":{"Bust":"39.5","Waist":"32.5","Hips":"42.75","Hollow to floor":"61"},"cm":{"Bust":"100","Waist":"83","Hips":"109","Hollow to floor":"155"}},"16":{"inch":{"Bust":"41","Waist":"34","Hips":"44.25","Hollow to floor":"61"},"cm":{"Bust":"104","Waist":"86","Hips":"112","Hollow to floor":"155"}},"14W":{"inch":{"Bust":"41","Waist":"34","Hips":"43.5","Hollow to floor":"61"},"cm":{"Bust":"104","Waist":"86","Hips":"110","Hollow to floor":"155"}},"16W":{"inch":{"Bust":"43","Waist":"36.25","Hips":"45.5","Hollow to floor":"61"},"cm":{"Bust":"109","Waist":"92","Hips":"116","Hollow to floor":"155"}},"18W":{"inch":{"Bust":"45","Waist":"38.5","Hips":"47.5","Hollow to floor":"61"},"cm":{"Bust":"114","Waist":"98","Hips":"121","Hollow to floor":"155"}},"20W":{"inch":{"Bust":"47","Waist":"40.75","Hips":"49.5","Hollow to floor":"61"},"cm":{"Bust":"119","Waist":"104","Hips":"126","Hollow to floor":"155"}},"22W":{"inch":{"Bust":"49","Waist":"43","Hips":"51.5","Hollow to floor":"61"},"cm":{"Bust":"124","Waist":"109","Hips":"131","Hollow to floor":"155"}},"24W":{"inch":{"Bust":"51","Waist":"45.25","Hips":"53.5","Hollow to floor":"61"},"cm":{"Bust":"130","Waist":"115","Hips":"136","Hollow to floor":"155"}},"26W":{"inch":{"Bust":"53","Waist":"47.5","Hips":"55.5","Hollow to floor":"61"},"cm":{"Bust":"135","Waist":"121","Hips":"141","Hollow to floor":"155"}},"J4":{"inch":{"Bust":"22","Waist":"20","Hips":"25","Hollow to floor":"42"},"cm":{"Bust":"56","Waist":"51","Hips":"64","Hollow to floor":"107"}},"J6":{"inch":{"Bust":"24","Waist":"22","Hips":"27","Hollow to floor":"45"},"cm":{"Bust":"61","Waist":"56","Hips":"69","Hollow to floor":"114"}},"J8":{"inch":{"Bust":"26","Waist":"24","Hips":"29","Hollow to floor":"48"},"cm":{"Bust":"66","Waist":"61","Hips":"74","Hollow to floor":"122"}},"J10":{"inch":{"Bust":"28","Waist":"26","Hips":"31","Hollow to floor":"50"},"cm":{"Bust":"71","Waist":"66","Hips":"79","Hollow to floor":"127"}},"J12":{"inch":{"Bust":"30","Waist":"28","Hips":"33","Hollow to floor":"51"},"cm":{"Bust":"76","Waist":"71","Hips":"84","Hollow to floor":"130"}},"J14":{"inch":{"Bust":"32","Waist":"30","Hips":"35","Hollow to floor":"53"},"cm":{"Bust":"81","Waist":"76","Hips":"89","Hollow to floor":"135"}},"J16":{"inch":{"Bust":"34","Waist":"32","Hips":"37","Hollow to floor":"55"},"cm":{"Bust":"86","Waist":"81","Hips":"94","Hollow to floor":"140"}},"S2":{"inch":{"Bust":"21","Waist":"20","Hips":"20","Hollow to floor":"33"},"cm":{"Bust":"53","Waist":"51","Hips":"51","Hollow to floor":"84"}},"S3":{"inch":{"Bust":"22","Waist":"21","Hips":"21","Hollow to floor":"35"},"cm":{"Bust":"56","Waist":"53","Hips":"53","Hollow to floor":"89"}},"S4":{"inch":{"Bust":"23","Waist":"22","Hips":"22","Hollow to floor":"38"},"cm":{"Bust":"58","Waist":"56","Hips":"56","Hollow to floor":"97"}},"S5":{"inch":{"Bust":"24","Waist":"23","Hips":"23","Hollow to floor":"40"},"cm":{"Bust":"61","Waist":"58","Hips":"58","Hollow to floor":"102"}},"S6":{"inch":{"Bust":"25","Waist":"24","Hips":"25","Hollow to floor":"41"},"cm":{"Bust":"64","Waist":"61","Hips":"64","Hollow to floor":"104"}},"S7":{"inch":{"Bust":"26","Waist":"25","Hips":"26","Hollow to floor":"42"},"cm":{"Bust":"66","Waist":"64","Hips":"66","Hollow to floor":"107"}},"S8":{"inch":{"Bust":"27","Waist":"26","Hips":"27","Hollow to floor":"43"},"cm":{"Bust":"69","Waist":"66","Hips":"69","Hollow to floor":"109"}},"S9":{"inch":{"Bust":"28","Waist":"27","Hips":"29","Hollow to floor":"44"},"cm":{"Bust":"71","Waist":"69","Hips":"74","Hollow to floor":"112"}},"S10":{"inch":{"Bust":"29","Waist":"28","Hips":"31","Hollow to floor":"47"},"cm":{"Bust":"74","Waist":"71","Hips":"79","Hollow to floor":"119"}},"S11":{"inch":{"Bust":"30.5","Waist":"29","Hips":"33","Hollow to floor":"48"},"cm":{"Bust":"77","Waist":"74","Hips":"84","Hollow to floor":"122"}},"S12":{"inch":{"Bust":"32","Waist":"30","Hips":"34","Hollow to floor":"50"},"cm":{"Bust":"81","Waist":"76","Hips":"86","Hollow to floor":"127"}},"S13":{"inch":{"Bust":"33","Waist":"31","Hips":"34.5","Hollow to floor":"51"},"cm":{"Bust":"84","Waist":"79","Hips":"88","Hollow to floor":"130"}},"S14":{"inch":{"Bust":"34","Waist":"32","Hips":"35","Hollow to floor":"52"},"cm":{"Bust":"86","Waist":"81","Hips":"89","Hollow to floor":"132"}},"P0":{"inch":{"Bust":"32","Waist":"25.5","Hips":"36","Hollow to floor":"0"}},"P2":{"inch":{"Bust":"33","Waist":"26.5","Hips":"36.5","Hollow to floor":"0"}},"P4":{"inch":{"Bust":"34","Waist":"27.5","Hips":"37.5","Hollow to floor":"0"}},"P6":{"inch":{"Bust":"35","Waist":"28.5","Hips":"38.5","Hollow to floor":"0"}},"P8":{"inch":{"Bust":"36","Waist":"29.5","Hips":"39.5","Hollow to floor":"0"}},"P10":{"inch":{"Bust":"37","Waist":"30.5","Hips":"40.5","Hollow to floor":"0"}},"P12":{"inch":{"Bust":"38.5","Waist":"31.5","Hips":"41.5","Hollow to floor":"0"}},"P14":{"inch":{"Bust":"40","Waist":"33.5","Hips":"43.5","Hollow to floor":"0"}},"P16":{"inch":{"Bust":"42","Waist":"35","Hips":"46","Hollow to floor":"0"}},"P18":{"inch":{"Bust":"44","Waist":"37","Hips":"48","Hollow to floor":"0"}},"P20":{"inch":{"Bust":"45","Waist":"39.5","Hips":"50","Hollow to floor":"0"}},"P22":{"inch":{"Bust":"48.5","Waist":"42","Hips":"52","Hollow to floor":"0"}},"P24":{"inch":{"Bust":"51","Waist":"45","Hips":"54","Hollow to floor":"0"}},"P26":{"inch":{"Bust":"54","Waist":"47","Hips":"56","Hollow to floor":"0"}},"P28":{"inch":{"Bust":"56","Waist":"49","Hips":"58","Hollow to floor":"0"}},"P30":{"inch":{"Bust":"58","Waist":"51","Hips":"60","Hollow to floor":"0"}},"P32":{"inch":{"Bust":"60","Waist":"54","Hips":"63","Hollow to floor":"0"}}}};

if(sizes.US_Size[size]!=undefined&&sizes.US_Size[size][unit]!=undefined){
	  return sizes.US_Size[size][unit];
	}
   return '';
}