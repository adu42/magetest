/**
 * Created by ado on 2016/4/11.
 * show as picture 统一用文字代替
 * 切换无图，显示选中的颜色名称
 * 我们不具备每种颜色的切换图。
 */
jQuery(function(){
    jQuery('#product-options-wrapper.product-options').find('select[title=color]').each(function(){
        var _label = 'Color:';
        var _label_value = '';
        var _that = jQuery(this);
        var options = jQuery(_that).find('option');
        if(options && options.length>0){
            var colors = [];
            jQuery(options).each(function(i,item){
                if(i==0){
                    _label_value = jQuery(item).text();
                }else{
                    colors.push(jQuery(item).attr('as'));
                }
            });
            if(colors && colors.length>0){
                var _color_chart = '';
                var _parentTemplate = '<div class="color-chart-box">%s</div><div class="color-chart-value"></div>';
                var _vTemplate = '<a href="javascript:void(0);" class="pis-color-a" title="%s">'+
                    '<div data-kvalue="%s" data-value="%s" class="pis-color">' +
                    '<div class=""><span class="%s" >%s</span></div>'+
                     '<div class="text-tip" style="display: none;">' +
                    '<div class="colorImg"><span class="img-tip %s"></span></div>'+
                    '<div class="colorAlt"><span class="text-tip">%s</span></div>' +
                    '</div>'+
                    '</div>'+
                    '</a>';
                for(i=0;i<colors.length;i++){

                    _color_chart+=_vTemplate.replace(/%s/g,colors[i]);
                }
                _color_chart = _parentTemplate.replace(/%s/g,_color_chart);
                jQuery(_that).before(_color_chart);
                jQuery(_that).hide();
                jQuery('.color-chart-value').html(_label_value);

                jQuery('.pis-color-a .pis-color').click(function(){
                    var _val = jQuery(this).data('value');
                    jQuery('.pis-color-a .pis-color').each(function(){
                        jQuery(this).removeClass('on');
                    });
                    jQuery(this).addClass('on');
                    console.log(_val);
                    jQuery(options).each(function(i,item){
                        if(jQuery(item).attr('as')==_val){
                            jQuery(item).prop('selected',true);
                            var _lab = jQuery(this).text();
                            jQuery('.color-chart-value').html(_label+_lab);
                        }else{
                            jQuery(item).prop('selected',false);
                        }
                    });
                });
            }
        }
    });
});
