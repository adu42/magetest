/**
 * Created by ado on 2016/4/5.
 */
/******** start upload by ado *********/
function getExt(file){
    return (-1!==file.indexOf('.'))?file.replace(/.*[.]/, ''):'';
}
function valid(el){
    var ext = getExt(el.value);
    var lc = ext.toLowerCase();
    var maxsize = 2*1048576;
    if(lc!=='jpg' && lc!=='jpeg' && lc!=='png' && lc!=='gif' && lc!=='bmp'){
        el.value = '';
        alert(window.parent.litb.jpg_only);
    }else if(el.files && el.files[0] ){
        if (el.files[0].size > maxsize) {
            el.value = '';
            window.parent.alert("3 images max, 2MB max per image.");
        }else{
            autoFillLiarLabel(el);
        }
    }else{
        var img = document.createElement("img");
        el.select();
        window.parent.document.body.focus();
        var imgSrc = document.selection.createRange().text;
        img.onload = function ()
        {
            var filesize = img.fileSize;
            if(filesize < maxsize && filesize > 0){
                autoFillLiarLabel(el);
            }else{
                img = null;
                el.value = '';
                window.parent.alert("3 images max, 2MB max per image.");
            }
        }
        img.src = imgSrc;
    }
}
function valfrm(frm){
    for(var o in frm.elements){
        var el = frm.elements[o];
        if(el.type=='file'&&el.value!=''){
            var ext = getExt(el.value);
            var lc = ext.toLowerCase();
            if(lc!=='jpg' && lc!=='jpeg' && lc!=='png' && lc!=='gif' && lc!=='bmp'){
                el.value = '';
            }
        }
    }
}

var _fileFields = jQuery('.review_image_files');
var _fileFieldsNumber = _fileFields.size();
var _alertMsg=_fileFieldsNumber+' images max, 2MB max per image.';
if(_fileFieldsNumber>0){
    jQuery(_fileFields).change(function(){
        validField(this);
        fileFieldsChange();
    });
}


function validField(el){
    var ext = getExt(el.value);
    var lc = ext.toLowerCase();
    var maxsize = 2*1048576;
    if(lc!=='jpg' && lc!=='jpeg' && lc!=='png' && lc!=='gif' && lc!=='bmp'){
        el.value = '';
       alert('file is not a jpg/png file format');
    }else if(el.files && el.files[0] ){
        if (el.files[0].size > maxsize) {
            el.value = '';
            alert(_alertMsg);
        }else{
            fileFieldsChange(el);
        }
    }else{
        var img = document.createElement("img");
        el.select();
        window.document.body.focus();
        var imgSrc = document.selection.createRange().text;
        img.onload = function ()
        {
            var filesize = img.fileSize;
            if(filesize < maxsize && filesize > 0){
                fileFieldsChange(el);
            }else{
                img = null;
                el.value = '';
                alert(_alertMsg);
            }
        }
        img.src = imgSrc;
    }
}

function _bisIe(){
    var userAgent = navigator.userAgent.toLowerCase();
    /*IE11*/
    var isIE = ((/msie/.test(userAgent) && !/opera/.test(userAgent)) || (/Trident\/7\./).test(navigator.userAgent) ) ? true : false;
    return isIE;
}

var count = 0;

/**
 * 文件域叠放
 * 显示 隐藏某个文件域
 * @param index
 * @param idpx
 */
function fileFieldsChange(el){
    var _file_label = 'file';
    var _imgIdEndWith='LiarLabel';
    var _maskIdLabelClassName = '.liar-label';
    var _fileFields = jQuery('.review_image_files');
    var _fileFieldsNumber = _fileFields.size();
    if(_fileFields && _fileFieldsNumber>0){
        var _shown=false;
        var _showIndex = 1;
        jQuery(_fileFields).each(function(index,element){
            var _index=index+1;
            var _cur_file_label = _file_label+_index;
            if(jQuery(this).val()){
                 _showIndex = _index+1;
                if(_index>=_fileFieldsNumber)_showIndex=1;
                var _cur_file_img_label = _file_label+_index+_imgIdEndWith;
                if(jQuery("#"+_cur_file_img_label).size() == 0){ //如果不存在就加上并显示下一个
                  //  console.log(_cur_file_img_label);
                    jQuery(_maskIdLabelClassName).append(jQuery('<span><img class="img" id="'+_cur_file_img_label+'"/><a></a></span>'));
                    preview_pic(_cur_file_img_label,_cur_file_label);
                }
                jQuery("#"+_cur_file_label).css("display","none");
            }

            if(!_shown && _index==_showIndex){
                jQuery("#"+_cur_file_label).css("display","inline-block");
                _shown = true;
            }
        });

        jQuery(_maskIdLabelClassName).delegate("a","click", function() {
            if(_bisIe()){
                for(i=1;i<=_fileFieldsNumber;i++){
                    jQuery(this).closest("span").remove();
                    var _cur_fileField = jQuery('#'+_file_label+i);
                    var _cur_file_img_label = _file_label+i+_imgIdEndWith;
                    if(jQuery(this).prev("img").attr("id") == _cur_file_img_label){
                        _cur_fileField.after(_cur_fileField.clone().val(""));
                        _cur_fileField.remove();
                        _cur_fileField.css("display","inline-block");
                    }else{
                        _cur_fileField.css("display","none");
                    }
                }
            }else{
                for(i=1;i<=_fileFieldsNumber;i++){
                    jQuery(this).closest("span").remove();
                    var _cur_fileField = jQuery('#'+_file_label+i);
                    var _cur_file_img_label = _file_label+i+_imgIdEndWith;
                    if(jQuery("#"+_cur_file_img_label).size() == 0){
                        jQuery(_cur_fileField).val('');
                        _cur_fileField.css("display","inline-block");
                    }else{
                        _cur_fileField.css("display","none");
                    }
                }
            }
        }).delegate("img","click", function() {
            return false;
        });
    }
}

function checkChinese(str) {
    var len = 0;
    var reg = /^[\u4E00-\u9FA5]+jQuery/;
    for(var i = 0;i< str.length;i++) {
        len++;
        if(reg.test(str[i])) {
            len++;
        }
    }
    return len;
}

function cutChinese(str) {
    for(var i = 0;i<str.length;i++) {
        if(reg.test(str[i])) {
            len++;
        }
    }
}

function getFileNameFromPath(str) {
    var n = str.lastIndexOf("\\");
    var filename = str.substring(n + 1);
    var str1 = filename.subCHString(0, 10);
    var str2 = filename.subCHStr((filename.strLen()-10), 10);
    if (checkChinese(filename) > 23) {
        filename = str1 + '...' + str2;
    }
    return filename;
}

String.prototype.strLen = function() {
    var len = 0;
    for (var i = 0; i < this.length; i++) {
        if (this.charCodeAt(i) > 255 || this.charCodeAt(i) < 0) len += 2; else len++;
    }
    return len;
}
//将字符串拆成字符，并存到数组中
String.prototype.strToChars = function(){
    var chars = new Array();
    for (var i = 0; i < this.length; i++){
        chars[i] = [this.substr(i, 1), this.isCHS(i)];
    }
    String.prototype.charsArray = chars;
    return chars;
}
//判断某个字符是否是汉字
String.prototype.isCHS = function(i){
    if (this.charCodeAt(i) > 255 || this.charCodeAt(i) < 0)
        return true;
    else
        return false;
}
//截取字符串（从start字节到end字节）
String.prototype.subCHString = function(start, end){
    var len = 0;
    var str = "";
    this.strToChars();
    for (var i = 0; i < this.length; i++) {
        if(this.charsArray[i][1])
            len += 2;
        else
            len++;
        if (end < len)
            return str;
        else if (start < len) {
            str += this.charsArray[i][0];
        }

    }
    return str;
}
//截取字符串（从start字节截取length个字节）
String.prototype.subCHStr = function(start, length){
    return this.subCHString(start, start + length);
}

function html5Reader(file,pic_id){
    var file = file.files[0];
    var reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = function(e){
        var pic = document.getElementById(pic_id);
        pic.src=this.result;
    }
}


function preview_pic(pic_id,file_id) {
    var pic = document.getElementById(pic_id);
    var file = document.getElementById(file_id);

    // IE浏览器
    if (document.all) {
        file.select();
        window.parent.document.body.focus();
        var reallocalpath = document.selection.createRange().text;
        var ie6 = /msie 6/i.test(navigator.userAgent);
        // IE6浏览器设置img的src为本地路径可以直接显示图片
        if (ie6) pic.src = reallocalpath;
        else {
            // 非IE6版本的IE由于安全问题直接设置img的src无法显示本地图片，但是可以通过滤镜来实现
            pic.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod='scale',src=\"" + reallocalpath + "\")";
            // 设置img的src为base64编码的透明图片 取消显示浏览器默认图片
            pic.src = 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';
        }
    }else{
        html5Reader(file,pic_id);
    }
}
/******** end upload by ado *********/

/**************** start show review images **************/
jQuery(function(){
// by ado ;  this js cna be in js file,Dom element add flag with dir="reviews"

// share
function createShare(image, url, description) {
    if (!url || url.length <= 1)url = encodeURIComponent(location.href);
    image = (typeof(image ) == 'object')?image.src:image;
	 image = encodeURIComponent(image);
    if (description && description.length > 1) {
        description = description.replace('"', '\'');
        description = encodeURIComponent(description);
    } else {
        description = jQuery('#shareTitle').text();
    }
    var shareGoogleBtn = '<a href="https://plus.google.com/share?url=' + url + '" class="share-icon googleplus-share-icon" title="Google+" target="_blank">Google+</a>';
	var sharePinterBtn = '<a  href="http://pinterest.com/pin/create/button/?url=' + url + '&media=' + image + '&description=' + description + '" title="Pinterest" class="share-icon pinterest-share-icon" target="_blank">Pinterest</a>';
    var shareFacebookBtn = '<a href="https://www.facebook.com/sharer/sharer.php?u='+url+'" target="_blank" class="share-icon facebook-share-icon">Facebook</a>';
    return shareFacebookBtn + sharePinterBtn + shareGoogleBtn;
}

// Template
var templatePhoto = {
    // Define markup. Class names should match key names.
    markup:
    '<div class="gallery-popup-wrap">'+
    '<div class="white-popup gallery-detail"><div class="mfp-close"></div>' +
    '<div class="mfp-galleryImages">' +
    '<div class="mfp-galleryLike"></div>' +
    '<div class="mfp-bigImage"></div>' +
    '<div class="mfp-reviewGallery"></div>' +
    '</div>' +
    '<div class="mfp-galleryContents">' +
    '<div class="mfp-reviewDetail"></div>' +
    '<div class="mfp-reviewAuthor"></div>' +
    '<div class="mfp-goods"></div>' +
    '<div class="mfp-share"></div>' +
    '</div>' +
    '</div>'+
        // if in, use next tow line
        // '<button class="mfp-arrow mfp-arrow-left mfp-prevent-close" type="button" title="Previous (Left arrow key)"></button>' +
        //  '<button class="mfp-arrow mfp-arrow-right mfp-prevent-close" type="button" title="Next (Right arrow key)"></button>'+
    '</div>'
};

//get Template Data then click which img first.
function getTemplateData(id,imgUrl){
    var templatedata = [];
    jQuery('#review-'+id).find('.review-thumbnail-images').each(function () {
        var id = jQuery(this).data('id');
        var content = jQuery('#review-detail-content-' + id);
        if (content && content.length > 0) {
            galleryObj = {};
            galleryObj['galleryLike'] = jQuery(content).data('likes');
            var img = new Image();
            if(imgUrl){
                img.src=imgUrl;
            }else{
                img.src = jQuery(this).find('img:first').data('zoom-image');
            }
            img.id = 'gallery-big-image';
            img.className = 'am-img-responsive';
            galleryObj['bigImage'] = img;
            var gelleryImagesObj = jQuery(this).clone();
            jQuery(gelleryImagesObj).find('img').each(function(){ jQuery(this).removeClass('hidden'); });
            var gelleryImages = '';
            gelleryImages += jQuery(gelleryImagesObj).html();
            galleryObj['reviewGallery'] = gelleryImages;
            galleryObj['reviewDetail'] = jQuery(content).html();
            galleryObj['reviewAuthor'] = jQuery('#review-detail-nicename-' + id).html();
            galleryObj['goods'] = jQuery('#review-detail-product-' + id).html(); 
			var _review_detail_product = jQuery('#review-detail-product-' + id).find('a');
			var _goods_src = '';
			if(_review_detail_product && _review_detail_product.length>0){
				_goods_src =  jQuery(_review_detail_product).attr('href');
			}

			galleryObj['goods_src'] = _goods_src;
            galleryObj['share'] =  createShare(galleryObj['bigImage'],_goods_src);
        }
        templatedata.push(galleryObj);
    });
    jQuery('[id^=review-]').not('#review-'+id).find('.review-thumbnail-images').each(function () {
        var id = jQuery(this).data('id');
        var content = jQuery('#review-detail-content-' + id);
        if (content && content.length > 0) {
            galleryObj = {};
            galleryObj['galleryLike'] = jQuery(content).data('likes');
            var img = new Image();
            img.src = jQuery(this).find('img:first').data('zoom-image');
            img.id = 'gallery-big-image';
            img.className = 'am-img-responsive';
            galleryObj['bigImage'] = img;
            var gelleryImagesObj = jQuery(this).clone();
            jQuery(gelleryImagesObj).find('img').each(function(){ jQuery(this).removeClass('hidden'); });
            var gelleryImages = '';
            gelleryImages += jQuery(gelleryImagesObj).html();
            galleryObj['reviewGallery'] = gelleryImages;
            galleryObj['reviewDetail'] = jQuery(content).html();
            galleryObj['reviewAuthor'] = jQuery('#review-detail-nicename-' + id).html();
            galleryObj['goods'] = jQuery('#review-detail-product-' + id).html();
            	var _review_detail_product = jQuery('#review-detail-product-' + id).find('a');
			var _goods_src = '';
			if(_review_detail_product && _review_detail_product.length>0){
				_goods_src =  jQuery(_review_detail_product).attr('href');
			}

			galleryObj['goods_src'] = _goods_src;
            galleryObj['share'] =  createShare(galleryObj['bigImage'],_goods_src);
        }
        templatedata.push(galleryObj);
    });
    return templatedata;
}
    function likesEvent(){
        jQuery('.review-likes').click(function () {
            var id = jQuery(this).attr('id');
            var _num = jQuery(this).text().trim();
            var num = parseInt(_num);
            if (!isNaN(num)) {
                var url = jQuery('#likeUrl').text();
                if (url && url.length > 1) {
                    var ckn = 'review_id_' + id;
                    var cd = Mage.Cookies.get(ckn);
                    if (cd > 0) {  //如果存在，就减1
                        num = num - 1;
                        Mage.Cookies.set(ckn, 0);
                        url = url + '?review_id=' + id+'&act=less';
                        jQuery.get(url);
						jQuery(this).removeClass('on');
                    } else { //加1
                        num = num + 1;
                        url = url + '?review_id=' + id;
                        jQuery.get(url);
                        Mage.Cookies.set(ckn, 1);
						jQuery(this).addClass('on');
                    }
                    jQuery(this).html(num);
                }
            }
        });
    }
//new enevt when new loaded dom
function inBoxAppEvent() {
    // likes
    likesEvent();

    //click thumbnail image Change big image
    jQuery('.gallery-detail').on('click', 'img.gallery-thumbnail-image', function () {
        var imgUrl = jQuery(this).data('zoom-image');
        if (imgUrl && imgUrl.length > 0) {
            if (jQuery('#gallery-big-image') && jQuery('#gallery-big-image').size() > 0) {
                jQuery('#gallery-big-image').attr('src', imgUrl);
            }
        }
    });
    /* left　right btn
     jQuery('.mfp-arrow-left').click(function(){
     var magnificPopup = jQuery.magnificPopup.instance;
     magnificPopup.prev();
     });
     jQuery('.mfp-arrow-right').click(function(){
     var magnificPopup = jQuery.magnificPopup.instance;
     magnificPopup.next();
     });
     */

}

function initThumbnailImages() {


// click img open model and show.
jQuery('.review-thumbnail-images > img').click(function(){
    var _id = jQuery(this).parent('.review-thumbnail-images').data('id');
    var _imgUrl = jQuery(this).data('zoom-image');
    var templatedata = getTemplateData(_id,_imgUrl);
  //  console.log(templatedata);
    jQuery.magnificPopup.open({
     //   delegate: 'a',
        key: 'my-popup',
        items: templatedata,
        type: 'inline',
        inline: templatePhoto,

        gallery: {
            enabled: true
        },
        callbacks: {
            open: function () {
                inBoxAppEvent();
            },
            markupParse: function (template, values, item) {
                // optionally apply your own logic - modify "template" element based on data in "values"
            },
            elementParse: function (item) {
                // Function will fire for each target element
                // "item.el" is a target DOM element (if present)
                // "item.src" is a source that you may modify
            }
        }
    });
});
}


    initThumbnailImages();
    likesEvent();
    jQuery('#rules-details').magnificPopup({type:'inline',midClick: true });
    jQuery('#share-your-photo').magnificPopup({type:'inline',midClick: true });




/**************** end show review images **************/

/**************** start home review images sideshow**************/

var sWidth = 930;
 var _galleryContainer = jQuery(".user-gallery-viewport .user-gallery-list");
    var _galleryParentContainer = jQuery(".user-gallery-viewport .user-images-container");
    var _galleryRootContainer = jQuery(".user-gallery-viewport");
    var _prevele = jQuery(".user-gallery-viewport .user-photos-prev");
    var _nextele = jQuery(".user-gallery-viewport .user-photos-next");
	var len = jQuery(_galleryContainer).length,index = 0,picTimer;
	if(jQuery('.user-gallery-viewport-list') && jQuery('.user-gallery-viewport-list').length>0){
		sWidth = jQuery('.user-gallery-viewport-list').width();
	}
	jQuery(_galleryParentContainer).css("width",sWidth*len);
   
	jQuery(window).resize(function() {  
		if(jQuery('.user-gallery-viewport-list') && jQuery('.user-gallery-viewport-list').length>0){
			sWidth = jQuery('.user-gallery-viewport-list').width();
		}
		jQuery(_galleryParentContainer).css("width",sWidth*len);
	});
	
   
    

    function showOrHidePrevNextBtn(index){
        if(index <1){
            jQuery(_prevele).css('display','none');
        }else{
            jQuery(_prevele).css('display','block');
        }

        if(index == (len-1)){
            jQuery(_nextele).css('display','none');
        }else{
            jQuery(_nextele).css('display','block');
        }
    }

    jQuery(_prevele).click(function() {
        index -= 1;
        if(index == -1) {index = len - 1;  }
    //    showOrHidePrevNextBtn(index);
        showPics(index);
    });
    jQuery(_nextele).click(function() {
        index += 1;
        if(index == len) {index = 0;}
      //  showOrHidePrevNextBtn(index);
        showPics(index);
    });
    
    jQuery(_galleryRootContainer).hover(function() {
        clearInterval(picTimer);
    },function() {
        picTimer = setInterval(function() {
            showPics(index);
            index++;
            if(index == len) {index = 0;}
        },4000);
    }).trigger("mouseleave");
    function showPics(index) { 
		showOrHidePrevNextBtn(index);
        var nowLeft = -index*sWidth;
        jQuery(_galleryParentContainer).stop(true,false).animate({"left":nowLeft},300);
    }
	

/**************** end home review sideshow **************/
/********************** load page in bottom **********************/

    var _loadurl = jQuery('#loadUrl');
    var _containerApp = jQuery('.reviews-group');
    var loading = jQuery('#review-loading');
    var _max = jQuery('#max');
    var maxPage = 12;
    if(_max && _max.length>0){
       maxPage= jQuery(_max).text();
    }
    if(maxPage)maxPage=parseInt(maxPage);
    if(_loadurl && _loadurl.length>0){
        var loadurl =jQuery(_loadurl).text();
        var winH = jQuery(window).height(); //页面可视区域高度
        var i = 2;
        var j = false;
        jQuery(window).scroll(function() {
        var pageH = jQuery(document).height();
        var scrollT = jQuery(window).scrollTop(); //滚动条top
        var outer = (scrollT+winH) > (pageH-50) ;
        if (outer) {
            if(!j && maxPage>=i){
                j=true;
                jQuery(loading).show();
            jQuery.get(loadurl, {p: i}, function(data) {
                if (data){
                    jQuery(loading).before(data);
                    initThumbnailImages();
                    likesEvent();
                    i++;
                    j=false;
                    jQuery(loading).hide();
                } else {
                    jQuery(loading).hide();
                    return false;
                }
            });
            }
        }
    });
    }
});
/********************** load page in bottom **********************/


