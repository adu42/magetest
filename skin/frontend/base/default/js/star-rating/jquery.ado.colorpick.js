/**
 * Created by ado on 2016/4/11.
 * modify by ado on 2017/6/30
 * 统一前后端规则，解决加载过程等待问题
 * 切换无图，显示选中的颜色名称
 * 图片的label中请带上show as picture的选项
 */
function doPickColor(event) {
    var _label = 'Color:';
    var color = jQuery(event).data('color');
    var elementId = '#pis-'+color;
    var _key = jQuery(event).data('optionkey');
    var _value = jQuery(event).data('optionvalue');
    var _alt = jQuery(event).data('alt');
    var _title =jQuery(event).data('label');
    if(jQuery('#select_'+_key) && jQuery('#select_'+_key).length>0){
        jQuery('#select_'+_key).val(_value);
        if(opConfig)opConfig.reloadPrice();
    }
    if(jQuery('#value-'+_key) && jQuery('#value-'+_key).length>0){
        if(_title){
            jQuery('#value-'+_key).html(_title).show();
        }else{
            jQuery('#value-'+_key).html('').hide();
        }
    }
    // 设置当前选中元素的样式 先清除 后设置
    jQuery('.pis-color-a.pis-color').each(function(){
        jQuery(this).removeClass('on');
    });
    jQuery(event).addClass('on');
    if(jQuery('.color-chart-value') && jQuery('.color-chart-value').length>0)jQuery('.color-chart-value').html(_label+_alt);

    // 切换大图
    if(color) {
        /**
         * 不等待加载执行
         * @type {number}
         * @private
         */
        var _index = 0;
        var _slideElement = '#slidecontent45 .ado-navigator li';
        //get img index
        jQuery(_slideElement+' img').each(function(index){
            var _alt = jQuery(this).data('color');
            if(!_alt)_alt=jQuery(this).attr('alt');
            _alt = _alt.replace(new RegExp(/( )/g),'_');
            _alt=_alt.toLowerCase();
            if(_alt.indexOf(color)>-1) {
                _index = index; return false;
            }
        });
        jQuery(_slideElement).each(function (index) {
            if(index == _index){
                jQuery(this).addClass('active');
            }else{
                jQuery(this).removeClass('active');
            }
        });
        jQuery(_slideElement).eq(_index).addClass('active');
         var _a = jQuery(_slideElement+' a').eq(_index);
         var _rel = jQuery(_a).attr('rel');
         var rel = eval("({" + _rel + "})");
         if(rel){
             var gallery = (rel.popupWin)?rel.popupWin:'#';
             var useZoom = (rel.useZoom)?rel.useZoom:'';
             var smallImage = (rel.smallImage)?rel.smallImage:'';
             var bigImage = jQuery(_a).attr('href');
             if(jQuery('#'+useZoom) && jQuery('#'+useZoom).length>0) {
                 jQuery('#' + useZoom).attr('href', bigImage);
                 jQuery('#' + useZoom).attr('gallery', gallery);
                 jQuery('#' + useZoom + ' img#image').attr('src', smallImage);
             }
         }
        /**
         * 不等待加载执行完毕
         * 如果加载完成，直接执行
         */
        jQuery(_a).trigger('click');
    }
}

function doPickSize(event) {
    if(event){
        var _key = jQuery(event).data('optionkey');
        var _value = jQuery(event).data('optionvalue');
        var _alt = jQuery(event).data('alt');
        var _title =jQuery(event).data('label');
        if(jQuery('#select_'+_key) && jQuery('#select_'+_key).length>0){
            jQuery('#select_'+_key).val(_value);
            jQuery('.pick-option').removeClass('on');
        }
        if(jQuery('#select-box-description-'+_key) && jQuery('#select-box-description-'+_key).length>0){
            if(_alt){
                jQuery('#select-box-description-'+_key).html(_alt).show();
            }else{
                jQuery('#select-box-description-'+_key).html('').hide();
            }
        }
        if(jQuery('#value-'+_key) && jQuery('#value-'+_key).length>0){
            if(_title){
                jQuery('#value-'+_key).html(_title).show();
            }else{
                jQuery('#value-'+_key).html('').hide();
            }
        }
        jQuery(event).addClass('on');
    }
    if(opConfig)opConfig.reloadPrice();
}