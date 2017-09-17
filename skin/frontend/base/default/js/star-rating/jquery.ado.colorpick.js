/**
 * Created by ado on 2016/4/11.
 * modify by ado on 2017/6/30
 * 统一前后端规则，解决加载过程等待问题
 * 切换无图，显示选中的颜色名称
 * 图片的label中请带上show as picture的选项
 */
function doPickColor(color) {
    var _label = 'Color:';
    var elementId = '#pis-'+color;
    // data 可以添加 可以获取   获取当前选中的value值

    // 设置当前选中元素的样式 先清除 后设置
    jQuery('.pis-color-a .pis-color').each(function(){
        jQuery(this).removeClass('on');
    });
    jQuery(elementId+' .pis-color').addClass('on');

    // 之前只是隐藏 select 选项  此处才是获取值。
    jQuery('select[title=color],select[title=colour]').each(function(){
        var _that = jQuery(this);
        var options = jQuery(_that).find('option');
        if(options && options.length>0){
            jQuery(options).each(function () {
                if(jQuery(this).attr('as') == color){
                    jQuery(this).prop('selected',true);
                   if(opConfig)opConfig.reloadPrice();
                   var _lab = jQuery(this).text();
                   if(jQuery('.color-chart-value') && jQuery('.color-chart-value').length>0)jQuery('.color-chart-value').html(_label+_lab);
                }else{
                    jQuery(this).prop('selected',false);
                }
            });
        }
    });
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
            var _alt = jQuery(this).attr('alt');
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

function doPickSize(selectElem,optionValue) {
    if(jQuery('#'+selectElem) && jQuery('#'+selectElem).length>0){
        jQuery('#'+selectElem).val(optionValue);
        console.log(selectElem,optionValue);
    }
    if(opConfig)opConfig.reloadPrice();
}