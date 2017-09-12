// 需要jQuery支持,不带向导的尺码自定义，不带相关商品的添加购物车，给手机模版用
// 2014-12-5 by 杜兵 114458573@qq.com
//标题标识,根据不同站翻译
//翻译en
function _cnstra(key){
   var lan = {
	   "bust":"Bust",
	   "waist":"Waist",
	   "hips":"Hips",
	   "hollow to floor":"Hollow to floor",
	   "shoulder (inch)":"Shoulder (inch)",
	   "armseye (inch)":"Armseye(inch)",
	   "armhole (inch)":"Armhole(inch)",
	   "arm length (inch)":"Arm length (inch)",
	   "height":"Height",
	   'inch':'inch',
	   'cm':'cm'
   };
    key=key.toLowerCase();
	key=jQuery.trim(key);
	return (lan[key]!=undefined)?lan[key]:key;
}

//下面不需要翻译
var _hideLabel = true;
var _inch='inch';
var _cm='cm';
var _customSizeOptionFlag='custom size'; //自定义size标识.小写
var _jacketOptionFlag='with jacket'; //衣服带夹克标识Mit Jacke
var _sizeLable='size';//尺码标题
var _sizeLables=["hips","hollow to floor","height","bust","waist","measurement unit"];
var _sizeUnitLable='measurement unit';
var _jacketLable='jacket';//夹克标题
var _jacketLablesStartWith=["shoulder","armseye","arm length"];
//元素标识，固定
var _sizeUnitFlag='#sizeUnit'; //尺码单位元素标识
var _mainProductOptionsContainer='.product-options'; //主商品options容器
var _optionsContainer='.divShowSizeVals';//尺码展示容器
var _sizeHeight='height';


jQuery(document).ready(function(){
    //绑定size事件
	setInit();
});


function getSelectText(selectObj){
   return getLowerString(jQuery(selectObj).find('option:selected').attr('as'));
}

function getSelectValue(selectObj){
   return getLowerString(jQuery(selectObj).find('option:selected').val());
}

function getLabel(lableObj){
    var _lable=jQuery(lableObj).attr('as');
	if(_lable!=undefined){
		return getLowerString(_lable);
	}else{
		_lable=jQuery(lableObj).attr('for');
		return getLowerString(_lable);
	}
}

function getStartWord(str,sper){
    var _str=str.split(sper);
	return jQuery.trim(_str[0]);
}

function getSelectLabel(selectObj){
   var _lable='';
   _lable=jQuery(selectObj).parent().parent('dd').prev('dt').children('label').attr('as');
   return getLowerString(_lable);
}


function getDtSelectLabel(dtObj){
   var _lable=jQuery(dtObj).children('label').attr('as');
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


function CustomJacket(obj){
     var selectValue=getSelectText(obj);
	 if(selectValue.startWith(_jacketOptionFlag)){
		 showhideSizeInputs(obj,'show');
		 setJecketValueDefault(obj,'');
	 }else{
	    showhideSizeInputs(obj,'hide');
		setJecketValueDefault(obj,'0');
	 }
}

function CustomSize(obj){
		var unit=getSizeUnit();
		if(unit==undefined)unit=_inch;
		 var size=getSelectText(obj);
		// size = getStartWord(size);
		 if(size==_customSizeOptionFlag){ 
							hideDivShowSizeVals(obj); //隐藏赋值层
							showhideSizeInputs(obj,'show'); //显示各表单域
							setCustomSizeValueDefault(obj);
							
		}else if(getSelectValue(obj)!=''){
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
  str=str.toLowerCase();   
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
    jQuery(obj).parents('dd:eq(0)').siblings('dt').each(function(){
                     var _label_as=getDtSelectLabel(jQuery(this));
                     if(jQuery.inArray(_label_as,_sizeLables)>-1){
                        _guide += mvSelectInputDtDdToGuide(jQuery(this));
                     }
    });
	_guide+='</dl>';
    _guide+='</div>';
	if(jQuery(obj).find('.input-box-'+_lable+'-guide').length==0){
    appendToGuide(obj,_guide);	
	}
}

function createJacketGuideFromLabel(obj,_lable){
    var _guide='<div class="input-box-'+_lable+'-guide" style="display:none;"><dl>';
    jQuery(obj).parents('dd:eq(0)').siblings('dt').each(function(){
             var _label_as=getDtSelectLabel(jQuery(this));
             _label_as=getStartWord(_label_as,' (');
             if(jQuery.inArray(_label_as,_jacketLablesStartWith)>-1){
                _guide += mvSelectInputDtDdToGuide(jQuery(this));
             }
    });
	_guide+='</dl>';
    _guide+='</div>';
	if(jQuery(obj).find('.input-box-'+_lable+'-guide').length==0){
    appendToGuide(obj,_guide);
	}
}

function appendToGuide(obj,_guide){
    jQuery(obj).parents('dd:eq(0)').append(_guide);
}


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
					
						jQuery(this).removeAttr('onclick');
						jQuery(this).attr('onclick','setSizeUnit(this)');
						
			  });
		}else{
		  var _input = jQuery(this).parent().next('dd').find('input');
			resetFormEv(_input);
			var _input_select = jQuery(this).parent().next('dd').find('select');
			resetFormEv(_input_select);
		}
		});
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
        var leftBegin='<div id="sizeUnitTitleLeft"><div class="sizeUnitTitle">&hearts; '+_cnstra(_inch)+':</div><div class="sizeUnitContents">';
        //显示cm
        var rightCm='';
        var rightBegin='<div id="sizeUnitTitleRight"><div class="sizeUnitTitle">&hearts; '+_cnstra(_cm)+':</div><div class="sizeUnitContents">';
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
	var val=jQuery(obj).next('span').children('label').attr('as');
	val=jQuery.trim(val);
	jQuery(_sizeUnitFlag).val(val);
	resetUnitFromSizeUnit(obj);
}
//获取单位，必须有隐藏域sizeUnit
function getSizeUnit(){
	var u=jQuery(_sizeUnitFlag).val();
	if(u==undefined||u==''){
		u=_inch;
	}else{
		u=u.toLowerCase();
	}
	return jQuery.trim(u);
}

function resetMainUnitFromSizeUnit(){
	var unit=getSizeUnit();
	if(jQuery(_mainProductOptionsContainer).length>0){
		jQuery(_mainProductOptionsContainer).each(function(){
			 jQuery(this).find('label').each(function() {
				var _label=getLabel(jQuery(this));
				if(_label==_sizeUnitLable){
					resetUnitFromSizeUnit(jQuery(this));
					return false; 
				}
            });
		});
	}
}


function resetUnitFromSizeUnit(obj){
  var unit=getSizeUnit();
  var _unit=(unit==_inch)?_cnstra(_cm):_cnstra(_inch);
  var _dunit=(unit==_inch)?_cnstra(_inch):_cnstra(_cm);
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


//获得固定尺码表
function getSizeVals(size,unit){
	size=size.toUpperCase();
	unit=unit.toLowerCase();
	
var sizes={"US_Size":{"UK6-US2-EUR32":{"inch":{"Bust":"32.5","Waist":"25.5","Hips":"35.75","Hollow to floor":"58"},"cm":{"Bust":"83","Waist":"65","Hips":"91","Hollow to floor":"147"}},"UK8-US4-EUR34":{"inch":{"Bust":"33.5","Waist":"26.5","Hips":"36.75","Hollow to floor":"58"},"cm":{"Bust":"85","Waist":"68","Hips":"93","Hollow to floor":"147"}},"UK10-US6-EUR36":{"inch":{"Bust":"34.5","Waist":"27.5","Hips":"37.75","Hollow to floor":"59"},"cm":{"Bust":"88","Waist":"70","Hips":"96","Hollow to floor":"150"}},"UK12-US8-EUR38":{"inch":{"Bust":"35.5","Waist":"28.5","Hips":"38.75","Hollow to floor":"59"},"cm":{"Bust":"90","Waist":"72","Hips":"98","Hollow to floor":"150"}},"UK14-US10-EUR40":{"inch":{"Bust":"36.5","Waist":"29.5","Hips":"39.75","Hollow to floor":"60"},"cm":{"Bust":"93","Waist":"75","Hips":"101","Hollow to floor":"152"}},"UK16-US12-EUR42":{"inch":{"Bust":"38","Waist":"31","Hips":"41.25","Hollow to floor":"60"},"cm":{"Bust":"97","Waist":"79","Hips":"105","Hollow to floor":"152"}},"UK18-US14-EUR44":{"inch":{"Bust":"39.5","Waist":"32.5","Hips":"42.75","Hollow to floor":"61"},"cm":{"Bust":"100","Waist":"83","Hips":"109","Hollow to floor":"155"}},"UK20-US16-EUR46":{"inch":{"Bust":"41","Waist":"34","Hips":"44.25","Hollow to floor":"61"},"cm":{"Bust":"104","Waist":"86","Hips":"112","Hollow to floor":"155"}},"UK18-US14W-EUR44":{"inch":{"Bust":"41","Waist":"34","Hips":"43.5","Hollow to floor":"61"},"cm":{"Bust":"104","Waist":"86","Hips":"110","Hollow to floor":"155"}},"UK20-US16W-EUR46":{"inch":{"Bust":"43","Waist":"36.25","Hips":"45.5","Hollow to floor":"61"},"cm":{"Bust":"109","Waist":"92","Hips":"116","Hollow to floor":"155"}},"UK22-US18W-EUR48":{"inch":{"Bust":"45","Waist":"38.5","Hips":"47.5","Hollow to floor":"61"},"cm":{"Bust":"114","Waist":"98","Hips":"121","Hollow to floor":"155"}},"UK24-US20W-EUR50":{"inch":{"Bust":"47","Waist":"40.75","Hips":"49.5","Hollow to floor":"61"},"cm":{"Bust":"119","Waist":"104","Hips":"126","Hollow to floor":"155"}},"UK26-US22W-EUR52":{"inch":{"Bust":"49","Waist":"43","Hips":"51.5","Hollow to floor":"61"},"cm":{"Bust":"124","Waist":"109","Hips":"131","Hollow to floor":"155"}},"UK28-US24W-EUR54":{"inch":{"Bust":"51","Waist":"45.25","Hips":"53.5","Hollow to floor":"61"},"cm":{"Bust":"130","Waist":"115","Hips":"136","Hollow to floor":"155"}},"UK30-US26W-EUR56":{"inch":{"Bust":"53","Waist":"47.5","Hips":"55.5","Hollow to floor":"61"},"cm":{"Bust":"135","Waist":"121","Hips":"141","Hollow to floor":"155"}},"US2-UK6-EUR32":{"inch":{"Bust":"32.5","Waist":"25.5","Hips":"35.75","Hollow to floor":"58"},"cm":{"Bust":"83","Waist":"65","Hips":"91","Hollow to floor":"147"}},"US4-UK8-EUR34":{"inch":{"Bust":"33.5","Waist":"26.5","Hips":"36.75","Hollow to floor":"58"},"cm":{"Bust":"85","Waist":"68","Hips":"93","Hollow to floor":"147"}},"US6-UK10-EUR36":{"inch":{"Bust":"34.5","Waist":"27.5","Hips":"37.75","Hollow to floor":"59"},"cm":{"Bust":"88","Waist":"70","Hips":"96","Hollow to floor":"150"}},"US8-UK12-EUR38":{"inch":{"Bust":"35.5","Waist":"28.5","Hips":"38.75","Hollow to floor":"59"},"cm":{"Bust":"90","Waist":"72","Hips":"98","Hollow to floor":"150"}},"US10-UK14-EUR40":{"inch":{"Bust":"36.5","Waist":"29.5","Hips":"39.75","Hollow to floor":"60"},"cm":{"Bust":"93","Waist":"75","Hips":"101","Hollow to floor":"152"}},"US12-UK16-EUR42":{"inch":{"Bust":"38","Waist":"31","Hips":"41.25","Hollow to floor":"60"},"cm":{"Bust":"97","Waist":"79","Hips":"105","Hollow to floor":"152"}},"US14-UK18-EUR44":{"inch":{"Bust":"39.5","Waist":"32.5","Hips":"42.75","Hollow to floor":"61"},"cm":{"Bust":"100","Waist":"83","Hips":"109","Hollow to floor":"155"}},"US16-UK20-EUR46":{"inch":{"Bust":"41","Waist":"34","Hips":"44.25","Hollow to floor":"61"},"cm":{"Bust":"104","Waist":"86","Hips":"112","Hollow to floor":"155"}},"US14W-UK18-EUR44":{"inch":{"Bust":"41","Waist":"34","Hips":"43.5","Hollow to floor":"61"},"cm":{"Bust":"104","Waist":"86","Hips":"110","Hollow to floor":"155"}},"US16W-UK20-EUR46":{"inch":{"Bust":"43","Waist":"36.25","Hips":"45.5","Hollow to floor":"61"},"cm":{"Bust":"109","Waist":"92","Hips":"116","Hollow to floor":"155"}},"US18W-UK22-EUR48":{"inch":{"Bust":"45","Waist":"38.5","Hips":"47.5","Hollow to floor":"61"},"cm":{"Bust":"114","Waist":"98","Hips":"121","Hollow to floor":"155"}},"US20W-UK24-EUR50":{"inch":{"Bust":"47","Waist":"40.75","Hips":"49.5","Hollow to floor":"61"},"cm":{"Bust":"119","Waist":"104","Hips":"126","Hollow to floor":"155"}},"US22W-UK26-EUR52":{"inch":{"Bust":"49","Waist":"43","Hips":"51.5","Hollow to floor":"61"},"cm":{"Bust":"124","Waist":"109","Hips":"131","Hollow to floor":"155"}},"US24W-UK28-EUR54":{"inch":{"Bust":"51","Waist":"45.25","Hips":"53.5","Hollow to floor":"61"},"cm":{"Bust":"130","Waist":"115","Hips":"136","Hollow to floor":"155"}},"US26W-UK30-EUR56":{"inch":{"Bust":"53","Waist":"47.5","Hips":"55.5","Hollow to floor":"61"},"cm":{"Bust":"135","Waist":"121","Hips":"141","Hollow to floor":"155"}},"US2-AU/UK6-EUR32":{"inch":{"Bust":"32.5","Waist":"25.5","Hips":"35.75","Hollow to floor":"58"},"cm":{"Bust":"83","Waist":"65","Hips":"91","Hollow to floor":"147"}},"US4-AU/UK8-EUR34":{"inch":{"Bust":"33.5","Waist":"26.5","Hips":"36.75","Hollow to floor":"58"},"cm":{"Bust":"85","Waist":"68","Hips":"93","Hollow to floor":"147"}},"US6-AU/UK10-EUR36":{"inch":{"Bust":"34.5","Waist":"27.5","Hips":"37.75","Hollow to floor":"59"},"cm":{"Bust":"88","Waist":"70","Hips":"96","Hollow to floor":"150"}},"US8-AU/UK12-EUR38":{"inch":{"Bust":"35.5","Waist":"28.5","Hips":"38.75","Hollow to floor":"59"},"cm":{"Bust":"90","Waist":"72","Hips":"98","Hollow to floor":"150"}},"US10-AU/UK14-EUR40":{"inch":{"Bust":"36.5","Waist":"29.5","Hips":"39.75","Hollow to floor":"60"},"cm":{"Bust":"93","Waist":"75","Hips":"101","Hollow to floor":"152"}},"US12-AU/UK16-EUR42":{"inch":{"Bust":"38","Waist":"31","Hips":"41.25","Hollow to floor":"60"},"cm":{"Bust":"97","Waist":"79","Hips":"105","Hollow to floor":"152"}},"US14-AU/UK18-EUR44":{"inch":{"Bust":"39.5","Waist":"32.5","Hips":"42.75","Hollow to floor":"61"},"cm":{"Bust":"100","Waist":"83","Hips":"109","Hollow to floor":"155"}},"US16-AU/UK20-EUR46":{"inch":{"Bust":"41","Waist":"34","Hips":"44.25","Hollow to floor":"61"},"cm":{"Bust":"104","Waist":"86","Hips":"112","Hollow to floor":"155"}},"US14W-AU/UK18-EUR44":{"inch":{"Bust":"41","Waist":"34","Hips":"43.5","Hollow to floor":"61"},"cm":{"Bust":"104","Waist":"86","Hips":"110","Hollow to floor":"155"}},"US16W-AU/UK20-EUR46":{"inch":{"Bust":"43","Waist":"36.25","Hips":"45.5","Hollow to floor":"61"},"cm":{"Bust":"109","Waist":"92","Hips":"116","Hollow to floor":"155"}},"US18W-AU/UK22-EUR48":{"inch":{"Bust":"45","Waist":"38.5","Hips":"47.5","Hollow to floor":"61"},"cm":{"Bust":"114","Waist":"98","Hips":"121","Hollow to floor":"155"}},"US20W-AU/UK24-EUR50":{"inch":{"Bust":"47","Waist":"40.75","Hips":"49.5","Hollow to floor":"61"},"cm":{"Bust":"119","Waist":"104","Hips":"126","Hollow to floor":"155"}},"US22W-AU/UK26-EUR52":{"inch":{"Bust":"49","Waist":"43","Hips":"51.5","Hollow to floor":"61"},"cm":{"Bust":"124","Waist":"109","Hips":"131","Hollow to floor":"155"}},"US24W-AU/UK28-EUR54":{"inch":{"Bust":"51","Waist":"45.25","Hips":"53.5","Hollow to floor":"61"},"cm":{"Bust":"130","Waist":"115","Hips":"136","Hollow to floor":"155"}},"US26W-AU/UK30-EUR56":{"inch":{"Bust":"53","Waist":"47.5","Hips":"55.5","Hollow to floor":"61"},"cm":{"Bust":"135","Waist":"121","Hips":"141","Hollow to floor":"155"}},"2":{"inch":{"Bust":"32.5","Waist":"25.5","Hips":"35.75","Hollow to floor":"58"},"cm":{"Bust":"83","Waist":"65","Hips":"91","Hollow to floor":"147"}},"4":{"inch":{"Bust":"33.5","Waist":"26.5","Hips":"36.75","Hollow to floor":"58"},"cm":{"Bust":"84","Waist":"68","Hips":"92","Hollow to floor":"147"}},"6":{"inch":{"Bust":"34.5","Waist":"27.5","Hips":"37.75","Hollow to floor":"59"},"cm":{"Bust":"88","Waist":"70","Hips":"96","Hollow to floor":"150"}},"8":{"inch":{"Bust":"35.5","Waist":"28.5","Hips":"38.75","Hollow to floor":"59"},"cm":{"Bust":"90","Waist":"72","Hips":"98","Hollow to floor":"150"}},"10":{"inch":{"Bust":"36.5","Waist":"29.5","Hips":"39.75","Hollow to floor":"60"},"cm":{"Bust":"93","Waist":"75","Hips":"101","Hollow to floor":"152"}},"12":{"inch":{"Bust":"38","Waist":"31","Hips":"41.25","Hollow to floor":"60"},"cm":{"Bust":"97","Waist":"79","Hips":"105","Hollow to floor":"152"}},"14":{"inch":{"Bust":"39.5","Waist":"32.5","Hips":"42.75","Hollow to floor":"61"},"cm":{"Bust":"100","Waist":"83","Hips":"109","Hollow to floor":"155"}},"16":{"inch":{"Bust":"41","Waist":"34","Hips":"44.25","Hollow to floor":"61"},"cm":{"Bust":"104","Waist":"86","Hips":"112","Hollow to floor":"155"}},"14W":{"inch":{"Bust":"41","Waist":"34","Hips":"43.5","Hollow to floor":"61"},"cm":{"Bust":"104","Waist":"86","Hips":"110","Hollow to floor":"155"}},"16W":{"inch":{"Bust":"43","Waist":"36.25","Hips":"45.5","Hollow to floor":"61"},"cm":{"Bust":"109","Waist":"92","Hips":"116","Hollow to floor":"155"}},"18W":{"inch":{"Bust":"45","Waist":"38.5","Hips":"47.5","Hollow to floor":"61"},"cm":{"Bust":"114","Waist":"98","Hips":"121","Hollow to floor":"155"}},"20W":{"inch":{"Bust":"47","Waist":"40.75","Hips":"49.5","Hollow to floor":"61"},"cm":{"Bust":"119","Waist":"104","Hips":"126","Hollow to floor":"155"}},"22W":{"inch":{"Bust":"49","Waist":"43","Hips":"51.5","Hollow to floor":"61"},"cm":{"Bust":"124","Waist":"109","Hips":"131","Hollow to floor":"155"}},"24W":{"inch":{"Bust":"51","Waist":"45.25","Hips":"53.5","Hollow to floor":"61"},"cm":{"Bust":"130","Waist":"115","Hips":"136","Hollow to floor":"155"}},"26W":{"inch":{"Bust":"53","Waist":"47.5","Hips":"55.5","Hollow to floor":"61"},"cm":{"Bust":"135","Waist":"121","Hips":"141","Hollow to floor":"155"}},"J4":{"inch":{"Bust":"22","Waist":"20","Hips":"25","Hollow to floor":"42"},"cm":{"Bust":"56","Waist":"51","Hips":"64","Hollow to floor":"107"}},"J6":{"inch":{"Bust":"24","Waist":"22","Hips":"27","Hollow to floor":"45"},"cm":{"Bust":"61","Waist":"56","Hips":"69","Hollow to floor":"114"}},"J8":{"inch":{"Bust":"26","Waist":"24","Hips":"29","Hollow to floor":"48"},"cm":{"Bust":"66","Waist":"61","Hips":"74","Hollow to floor":"122"}},"J10":{"inch":{"Bust":"28","Waist":"26","Hips":"31","Hollow to floor":"50"},"cm":{"Bust":"71","Waist":"66","Hips":"79","Hollow to floor":"127"}},"J12":{"inch":{"Bust":"30","Waist":"28","Hips":"33","Hollow to floor":"51"},"cm":{"Bust":"76","Waist":"71","Hips":"84","Hollow to floor":"130"}},"J14":{"inch":{"Bust":"32","Waist":"30","Hips":"35","Hollow to floor":"53"},"cm":{"Bust":"81","Waist":"76","Hips":"89","Hollow to floor":"135"}},"J16":{"inch":{"Bust":"34","Waist":"32","Hips":"37","Hollow to floor":"55"},"cm":{"Bust":"86","Waist":"81","Hips":"94","Hollow to floor":"140"}},"S2":{"inch":{"Bust":"21","Waist":"20","Hips":"20","Hollow to floor":"33"},"cm":{"Bust":"53","Waist":"51","Hips":"51","Hollow to floor":"84"}},"S3":{"inch":{"Bust":"22","Waist":"21","Hips":"21","Hollow to floor":"35"},"cm":{"Bust":"56","Waist":"53","Hips":"53","Hollow to floor":"89"}},"S4":{"inch":{"Bust":"23","Waist":"22","Hips":"22","Hollow to floor":"38"},"cm":{"Bust":"58","Waist":"56","Hips":"56","Hollow to floor":"97"}},"S5":{"inch":{"Bust":"24","Waist":"23","Hips":"23","Hollow to floor":"40"},"cm":{"Bust":"61","Waist":"58","Hips":"58","Hollow to floor":"102"}},"S6":{"inch":{"Bust":"25","Waist":"24","Hips":"25","Hollow to floor":"41"},"cm":{"Bust":"64","Waist":"61","Hips":"64","Hollow to floor":"104"}},"S7":{"inch":{"Bust":"26","Waist":"25","Hips":"26","Hollow to floor":"42"},"cm":{"Bust":"66","Waist":"64","Hips":"66","Hollow to floor":"107"}},"S8":{"inch":{"Bust":"27","Waist":"26","Hips":"27","Hollow to floor":"43"},"cm":{"Bust":"69","Waist":"66","Hips":"69","Hollow to floor":"109"}},"S9":{"inch":{"Bust":"28","Waist":"27","Hips":"29","Hollow to floor":"44"},"cm":{"Bust":"71","Waist":"69","Hips":"74","Hollow to floor":"112"}},"S10":{"inch":{"Bust":"29","Waist":"28","Hips":"31","Hollow to floor":"47"},"cm":{"Bust":"74","Waist":"71","Hips":"79","Hollow to floor":"119"}},"S11":{"inch":{"Bust":"30.5","Waist":"29","Hips":"33","Hollow to floor":"48"},"cm":{"Bust":"77","Waist":"74","Hips":"84","Hollow to floor":"122"}},"S12":{"inch":{"Bust":"32","Waist":"30","Hips":"34","Hollow to floor":"50"},"cm":{"Bust":"81","Waist":"76","Hips":"86","Hollow to floor":"127"}},"S13":{"inch":{"Bust":"33","Waist":"31","Hips":"34.5","Hollow to floor":"51"},"cm":{"Bust":"84","Waist":"79","Hips":"88","Hollow to floor":"130"}},"S14":{"inch":{"Bust":"34","Waist":"32","Hips":"35","Hollow to floor":"52"},"cm":{"Bust":"86","Waist":"81","Hips":"89","Hollow to floor":"132"}},"P0":{"inch":{"Bust":"32","Waist":"25.5","Hips":"36","Hollow to floor":"0"}},"P2":{"inch":{"Bust":"33","Waist":"26.5","Hips":"36.5","Hollow to floor":"0"}},"P4":{"inch":{"Bust":"34","Waist":"27.5","Hips":"37.5","Hollow to floor":"0"}},"P6":{"inch":{"Bust":"35","Waist":"28.5","Hips":"38.5","Hollow to floor":"0"}},"P8":{"inch":{"Bust":"36","Waist":"29.5","Hips":"39.5","Hollow to floor":"0"}},"P10":{"inch":{"Bust":"37","Waist":"30.5","Hips":"40.5","Hollow to floor":"0"}},"P12":{"inch":{"Bust":"38.5","Waist":"31.5","Hips":"41.5","Hollow to floor":"0"}},"P14":{"inch":{"Bust":"40","Waist":"33.5","Hips":"43.5","Hollow to floor":"0"}},"P16":{"inch":{"Bust":"42","Waist":"35","Hips":"46","Hollow to floor":"0"}},"P18":{"inch":{"Bust":"44","Waist":"37","Hips":"48","Hollow to floor":"0"}},"P20":{"inch":{"Bust":"45","Waist":"39.5","Hips":"50","Hollow to floor":"0"}},"P22":{"inch":{"Bust":"48.5","Waist":"42","Hips":"52","Hollow to floor":"0"}},"P24":{"inch":{"Bust":"51","Waist":"45","Hips":"54","Hollow to floor":"0"}},"P26":{"inch":{"Bust":"54","Waist":"47","Hips":"56","Hollow to floor":"0"}},"P28":{"inch":{"Bust":"56","Waist":"49","Hips":"58","Hollow to floor":"0"}},"P30":{"inch":{"Bust":"58","Waist":"51","Hips":"60","Hollow to floor":"0"}},"P32":{"inch":{"Bust":"60","Waist":"54","Hips":"63","Hollow to floor":"0"}}}};

if(sizes.US_Size[size]!=undefined&&sizes.US_Size[size][unit]!=undefined){
	  return sizes.US_Size[size][unit];
	}
   return '';
}

var JPlaceHolder = {
    //检测
    _check : function(){
        return 'placeholder' in document.createElement('input');
    },
    //初始化
    init : function(){
        if(!this._check()){
            this.fix();
        }
    },
    //修复
    fix : function(){
        jQuery(':input[placeholder]').each(function(index, element) {
            var self = jQuery(this), txt = self.attr('placeholder');
            self.wrap(jQuery('<div></div>').css({position:'relative', zoom:'1', border:'none', background:'none', padding:'none', margin:'none'}));
            var pos = self.position(), h = self.outerHeight(true), paddingleft = self.css('padding-left');
            var holder = jQuery('<span></span>').text(txt).css({position:'absolute', left:pos.left, top:pos.top, height:h, lienHeight:h, paddingLeft:paddingleft, color:'#aaa'}).appendTo(self.parent());
            self.focusin(function(e) {
                holder.hide();
            }).focusout(function(e) {
                if(!self.val()){
                    holder.show();
                }
            });
            holder.click(function(e) {
                holder.hide();
                self.focus();
            });
        });
    }
};
//执行
jQuery(function(){
    JPlaceHolder.init();
	if(_hideLabel){
		jQuery('#product-options-wrapper dt label').hide();
	}
});