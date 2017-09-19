ColorChart = Class.create();
Object.extend(Object.extend(ColorChart.prototype, Abstract.prototype), {
    initialize: function(wrapper, color, options){
        this.wrapper    = $(wrapper);
        this.color      = color || [];  //默认色卡数组
        this.focusblock = '';   //色卡图片焦点块
        this.options    = Object.extend({
            baseUrl: '',//色卡图片路径
            def: {},    //默认色卡
            list: {},   //色卡名称与图片路径映射数组
            x: 16,       //色卡图片左边距
            y: 1,       //色卡图片上边距
            width: 36,  //色卡图片焦点宽度
            height: 36, //色卡图片焦点高度
            rows: 0,    //色卡图片焦点行数
            cols: 7,    //色卡图片焦点列数
            spaceX: 32,  //色卡图片焦点列间距
            spaceY: 20   //色卡图片焦点行间距
        }, options || {});
        this.list();
    },
    list: function(){
        if(!this.color || !Object.isArray(this.color) || this.color.size() <= 0){
            return;
        }
        if(!this.options.def || !this.options.list){
            return;
        }
        this.color.each(function(colorname){
            this.initColor(colorname, this.options.def[colorname]);
            this.listColor(colorname, this.options.list[colorname]);
        }, this);
        this.wrapper.select('dd.color-chart-focus > div').each(function(object){
            Element.setStyle(object, {
                opacity: 0,
                'background-color': '#fff'
            });
            Event.observe(object, 'mouseover', this.hover.bindAsEventListener(this));
            Event.observe(object, 'mouseout', this.resume.bindAsEventListener(this));
            Event.observe(object, 'click', this.click.bindAsEventListener(this));
        }, this);
    },
    initColor: function(colorname, object){
        if(!object){
            return;
        }
        var ele = $(colorname).down('dl.color-chart-content');
        if(!ele) return;
        var dt = Element.down(ele, 'dt');
        var dd = Element.down(ele, 'dd');
        if(!dt || !dd) return;
        
        var ddUrl = this.options.baseUrl + object.ddImg + '.jpg';
        Element.update(dd, '<img src="' + ddUrl + '" />');
        //this.loadImage(ddUrl, function(){Element.update(dd, '<img src="' + ddUrl + '" />')});
        
        var dtUrl = this.options.baseUrl + object.dtImg + '.jpg';
        Element.update(dt, '<img src="' + dtUrl + '" />')
        //Element.update(dt, '<br /><br /><span style="color:#666;font-style:italic">loading ' + object.name + '</span>');
        //this.loadImage(dtUrl, function(){Element.update(dt, '<img src="' + dtUrl + '" /><span>' + object.name + '</span>')});        
    },
    listColor: function(colorname, object){
        if(!Object.isArray(object) || object.size() <= 0){
            return;
        }
        this.focusblock = '';
        this.options.rows = 0;
        object.each(function(object, index){
            var cols = index % this.options.cols;
            if(index > 0 && cols == 0){
                this.options.rows++;
            }
            var x = this.options.x + (this.options.width + this.options.spaceX) * (index % this.options.cols);
            var y = this.options.y + (this.options.height + this.options.spaceY) * this.options.rows;
            this.focusblock += '<div to="' + object.img + '" title="' + object.name + '" style="left: ' + x + 'px;top: ' + y + 'px"></div>';
        }, this);
        var ele = $(colorname).down('dd.color-chart-focus');
        if(!ele){
            return;
        }
        Element.insert(ele, {bottom: this.focusblock});
    },
    loadImage: function(url, callback, id){
        var img = new Image();
        img.src = url;
        if(img.complete){
            callback.call(img, id);
        }else{
            Event.observe(img, 'load', function(){callback.call(img, id)});
        }
    },
    hover: function(event){
        var element = Event.findElement(event);
        if(!element) return;
        Element.setStyle(element, {opacity: 0.6});
    },
    resume: function(event){
        var element = Event.findElement(event);
        if(!element) return;
        Element.setStyle(element, {opacity: 0});
    },
    click: function(event){
        var element = Event.findElement(event);
        if(!element) return;
        var title = Element.readAttribute(element, 'title');
        var url = this.options.baseUrl + Element.readAttribute(element, 'to') + '.jpg';
        var html = '<img src="' + url + '" /><span>' + title + '</span>';
        var bigImg = Element.down(Element.up(Element.up(element, 'dd'), 'dl'), 'dt');
        if(!bigImg) return;
        Element.update(bigImg, '<br /><br /><span style="color:#666;font-style:italic">loading ' + title + '</span>');
        this.loadImage(url, function(){Element.update(bigImg, html)});
    }
});

DressColor = Class.create();
Object.extend(Object.extend(DressColor.prototype, ColorChart.prototype), {
    _parent: ColorChart.prototype,
    
    initialize: function(wrapper, color, options){
        this.doptions = Object.extend({            
            def: this.defaultColor(),    //默认色卡
            list: this.getListColor()   //色卡名称与图片路径映射数组           
        }, options || {});
        
        this._parent.initialize.call(this, wrapper, color, this.doptions);
    },
    defaultColor: function(){
        var def = {};
        def['chiffon'] = {name: 'Daffodil', dtImg: 'Chiffon/Daffodil', ddImg: 'Chiffon'};
        def['elastic_woven_satin'] = {name: 'Daffodil', dtImg: 'Elastic-Woven-Satin/Daffodil', ddImg: 'Elastic-Woven-Satin'};
        def['matte_satin'] = {name: 'Daffodil', dtImg: 'Matte-Satin/Daffodil', ddImg: 'Matte-Satin'};
        def['organza'] = {name: 'Daffodil', dtImg: 'Organza/Daffodil', ddImg: 'Organza'};
        def['satin'] = {name: 'Daffodil', dtImg: 'Satin/Daffodil', ddImg: 'Satin'};
        def['silk_like_satin'] = {name: 'Daffodil', dtImg: 'Silk-Like-Satin/Daffodil', ddImg: 'Silk-Like-Satin'};
        def['taffeta'] = {name: 'Daffodil', dtImg: 'Taffeta/Daffodil', ddImg: 'Taffeta'};
        return def;
    },
    getListColor: function(){
        var list = {};
        list.chiffon = [
            { name: 'Daffodil', img: 'Chiffon/Daffodil' },
            { name: 'Orange', img: 'Chiffon/Orange' },
            { name: 'Pink', img: 'Chiffon/Pink' },
            { name: 'Fuchsia', img: 'Chiffon/Fuchsia' },
            { name: 'Red', img: 'Chiffon/Red' },
            { name: 'Burgundy', img: 'Chiffon/Burgundy' },
            { name: 'Lilac', img: 'Chiffon/Lilac' },
            { name: 'Lavender', img: 'Chiffon/Lavender' },
            { name: 'Grape', img: 'Chiffon/Grape' },
            { name: 'Light Sky Blue', img: 'Chiffon/Light_Sky_Blue' },
            { name: 'Blue', img: 'Chiffon/Blue' },
            { name: 'Royal Blue', img: 'Chiffon/Royal_Blue' },
            { name: 'Dark Navy', img: 'Chiffon/Dark_Navy' },
            { name: 'Sage', img: 'Chiffon/Sage' },
            { name: 'Green', img: 'Chiffon/Green' },
            { name: 'Hunter', img: 'Chiffon/Hunter' },
            { name: 'Dark Green', img: 'Chiffon/Dark_Green' },
            { name: 'Brown', img: 'Chiffon/Brown' },
            { name: 'Chocolate', img: 'Chiffon/Chocolate' },
            { name: 'White', img: 'Chiffon/White' },
            { name: 'Ivory', img: 'Chiffon/Ivory' },
            { name: 'Champagne', img: 'Chiffon/Champagne' },
            { name: 'Gold', img: 'Chiffon/Gold' },
            { name: 'Silver', img: 'Chiffon/Silver' },
            { name: 'Black', img: 'Chiffon/Black' },
            { name: 'Pearl Pink', img: 'Chiffon/Pearl_Pink' },
            { name: 'Watermelon', img: 'Chiffon/Watermelon' },
            { name: 'Regency', img: 'Chiffon/Regency' }
        ];

        list.elastic_woven_satin = [
            { name: 'Daffodil', img: 'Elastic-Woven-Satin/Daffodil' },
            { name: 'Orange', img: 'Elastic-Woven-Satin/Orange' },
            { name: 'Pink', img: 'Elastic-Woven-Satin/Pink' },
            { name: 'Fuchsia', img: 'Elastic-Woven-Satin/Fuchsia' },
            { name: 'Red', img: 'Elastic-Woven-Satin/Red' },
            { name: 'Burgundy', img: 'Elastic-Woven-Satin/Burgundy' },
            { name: 'Lilac', img: 'Elastic-Woven-Satin/Lilac' },
            { name: 'Lavender', img: 'Elastic-Woven-Satin/Lavender' },
            { name: 'Grape', img: 'Elastic-Woven-Satin/Grape' },
            { name: 'Light Sky Blue', img: 'Elastic-Woven-Satin/Light_Sky_Blue' },
            { name: 'Blue', img: 'Elastic-Woven-Satin/Blue' },
            { name: 'Royal Blue', img: 'Elastic-Woven-Satin/Royal_Blue' },
            { name: 'Dark Navy', img: 'Elastic-Woven-Satin/Dark_Navy' },
            { name: 'Sage', img: 'Elastic-Woven-Satin/Sage' },
            { name: 'Green', img: 'Elastic-Woven-Satin/Green' },
            { name: 'Hunter', img: 'Elastic-Woven-Satin/Hunter' },
            { name: 'Dark Green', img: 'Elastic-Woven-Satin/Dark_Green' },
            { name: 'Brown', img: 'Elastic-Woven-Satin/Brown' },
            { name: 'Chocolate', img: 'Elastic-Woven-Satin/Chocolate' },
            { name: 'White', img: 'Elastic-Woven-Satin/White' },
            { name: 'Ivory', img: 'Elastic-Woven-Satin/Ivory' },
            { name: 'Champagne', img: 'Elastic-Woven-Satin/Champagne' },
            { name: 'Gold', img: 'Elastic-Woven-Satin/Gold' },
            { name: 'Silver', img: 'Elastic-Woven-Satin/Silver' },
            { name: 'Black', img: 'Elastic-Woven-Satin/Black' },
            { name: 'Pearl Pink', img: 'Elastic-Woven-Satin/Pearl_Pink' },
            { name: 'Watermelon', img: 'Elastic-Woven-Satin/Watermelon' },
            { name: 'Regency', img: 'Elastic-Woven-Satin/Regency' }
        ];

        list.matte_satin = [
            { name: 'Daffodil', img: 'Matte-Satin/Daffodil' },
            { name: 'Orange', img: 'Matte-Satin/Orange' },
            { name: 'Pink', img: 'Matte-Satin/Pink' },
            { name: 'Fuchsia', img: 'Matte-Satin/Fuchsia' },
            { name: 'Red', img: 'Matte-Satin/Red' },
            { name: 'Burgundy', img: 'Matte-Satin/Burgundy' },
            { name: 'Lilac', img: 'Matte-Satin/Lilac' },
            { name: 'Lavender', img: 'Matte-Satin/Lavender' },
            { name: 'Grape', img: 'Matte-Satin/Grape' },
            { name: 'Light Sky Blue', img: 'Matte-Satin/Light_Sky_Blue' },
            { name: 'Blue', img: 'Matte-Satin/Blue' },
            { name: 'Royal Blue', img: 'Matte-Satin/Royal_Blue' },
            { name: 'Dark Navy', img: 'Matte-Satin/Dark_Navy' },
            { name: 'Sage', img: 'Matte-Satin/Sage' },
            { name: 'Green', img: 'Matte-Satin/Green' },
            { name: 'Hunter', img: 'Matte-Satin/Hunter' },
            { name: 'Dark Green', img: 'Matte-Satin/Dark_Green' },
            { name: 'Brown', img: 'Matte-Satin/Brown' },
            { name: 'Chocolate', img: 'Matte-Satin/Chocolate' },
            { name: 'White', img: 'Matte-Satin/White' },
            { name: 'Ivory', img: 'Matte-Satin/Ivory' },
            { name: 'Champagne', img: 'Matte-Satin/Champagne' },
            { name: 'Gold', img: 'Matte-Satin/Gold' },
            { name: 'Silver', img: 'Matte-Satin/Silver' },
            { name: 'Black', img: 'Matte-Satin/Black' },
            { name: 'Pearl Pink', img: 'Matte-Satin/Pearl_Pink' },
            { name: 'Watermelon', img: 'Matte-Satin/Watermelon' },
            { name: 'Regency', img: 'Matte-Satin/Regency' }
        ];

        list.organza = [
            { name: 'Daffodil', img: 'Organza/Daffodil' },
            { name: 'Orange', img: 'Organza/Orange' },
            { name: 'Pink', img: 'Organza/Pink' },
            { name: 'Fuchsia', img: 'Organza/Fuchsia' },
            { name: 'Red', img: 'Organza/Red' },
            { name: 'Burgundy', img: 'Organza/Burgundy' },
            { name: 'Lilac', img: 'Organza/Lilac' },
            { name: 'Lavender', img: 'Organza/Lavender' },
            { name: 'Grape', img: 'Organza/Grape' },
            { name: 'Light Sky Blue', img: 'Organza/Light_Sky_Blue' },
            { name: 'Blue', img: 'Organza/Blue' },
            { name: 'Royal Blue', img: 'Organza/Royal_Blue' },
            { name: 'Dark Navy', img: 'Organza/Dark_Navy' },
            { name: 'Sage', img: 'Organza/Sage' },
            { name: 'Green', img: 'Organza/Green' },
            { name: 'Hunter', img: 'Organza/Hunter' },
            { name: 'Dark Green', img: 'Organza/Dark_Green' },
            { name: 'Brown', img: 'Organza/Brown' },
            { name: 'Chocolate', img: 'Organza/Chocolate' },
            { name: 'White', img: 'Organza/White' },
            { name: 'Ivory', img: 'Organza/Ivory' },
            { name: 'Champagne', img: 'Organza/Champagne' },
            { name: 'Gold', img: 'Organza/Gold' },
            { name: 'Silver', img: 'Organza/Silver' },
            { name: 'Black', img: 'Organza/Black' },
            { name: 'Pearl Pink', img: 'Organza/Pearl_Pink' },
            { name: 'Watermelon', img: 'Organza/Watermelon' },
            { name: 'Regency', img: 'Organza/Regency' }
        ];

        list.satin = [
            { name: 'Daffodil', img: 'Satin/Daffodil' },
            { name: 'Orange', img: 'Satin/Orange' },
            { name: 'Pink', img: 'Satin/Pink' },
            { name: 'Fuchsia', img: 'Satin/Fuchsia' },
            { name: 'Red', img: 'Satin/Red' },
            { name: 'Burgundy', img: 'Satin/Burgundy' },
            { name: 'Lilac', img: 'Satin/Lilac' },
            { name: 'Lavender', img: 'Satin/Lavender' },
            { name: 'Grape', img: 'Satin/Grape' },
            { name: 'Light Sky Blue', img: 'Satin/Light_Sky_Blue' },
            { name: 'Blue', img: 'Satin/Blue' },
            { name: 'Royal Blue', img: 'Satin/Royal_Blue' },
            { name: 'Dark Navy', img: 'Satin/Dark_Navy' },
            { name: 'Sage', img: 'Satin/Sage' },
            { name: 'Green', img: 'Satin/Green' },
            { name: 'Hunter', img: 'Satin/Hunter' },
            { name: 'Dark Green', img: 'Satin/Dark_Green' },
            { name: 'Brown', img: 'Satin/Brown' },
            { name: 'Chocolate', img: 'Satin/Chocolate' },
            { name: 'White', img: 'Satin/White' },
            { name: 'Ivory', img: 'Satin/Ivory' },
            { name: 'Champagne', img: 'Satin/Champagne' },
            { name: 'Gold', img: 'Satin/Gold' },
            { name: 'Silver', img: 'Satin/Silver' },
            { name: 'Black', img: 'Satin/Black' },
            { name: 'Pearl Pink', img: 'Satin/Pearl_Pink' },
            { name: 'Watermelon', img: 'Satin/Watermelon' },
            { name: 'Regency', img: 'Satin/Regency' }
        ];

        list.silk_like_satin = [
            { name: 'Daffodil', img: 'Silk-Like-Satin/Daffodil' },
            { name: 'Orange', img: 'Silk-Like-Satin/Orange' },
            { name: 'Pink', img: 'Silk-Like-Satin/Pink' },
            { name: 'Fuchsia', img: 'Silk-Like-Satin/Fuchsia' },
            { name: 'Red', img: 'Silk-Like-Satin/Red' },
            { name: 'Burgundy', img: 'Silk-Like-Satin/Burgundy' },
            { name: 'Lilac', img: 'Silk-Like-Satin/Lilac' },
            { name: 'Lavender', img: 'Silk-Like-Satin/Lavender' },
            { name: 'Grape', img: 'Silk-Like-Satin/Grape' },
            { name: 'Light Sky Blue', img: 'Silk-Like-Satin/Light_Sky_Blue' },
            { name: 'Blue', img: 'Silk-Like-Satin/Blue' },
            { name: 'Royal Blue', img: 'Silk-Like-Satin/Royal_Blue' },
            { name: 'Dark Navy', img: 'Silk-Like-Satin/Dark_Navy' },
            { name: 'Sage', img: 'Silk-Like-Satin/Sage' },
            { name: 'Green', img: 'Silk-Like-Satin/Green' },
            { name: 'Hunter', img: 'Silk-Like-Satin/Hunter' },
            { name: 'Dark Green', img: 'Silk-Like-Satin/Dark_Green' },
            { name: 'Brown', img: 'Silk-Like-Satin/Brown' },
            { name: 'Chocolate', img: 'Silk-Like-Satin/Chocolate' },
            { name: 'White', img: 'Silk-Like-Satin/White' },
            { name: 'Ivory', img: 'Silk-Like-Satin/Ivory' },
            { name: 'Champagne', img: 'Silk-Like-Satin/Champagne' },
            { name: 'Gold', img: 'Silk-Like-Satin/Gold' },
            { name: 'Silver', img: 'Silk-Like-Satin/Silver' },
            { name: 'Black', img: 'Silk-Like-Satin/Black' },
            { name: 'Pearl Pink', img: 'Silk-Like-Satin/Pearl_Pink' },
            { name: 'Watermelon', img: 'Silk-Like-Satin/Watermelon' },
            { name: 'Regency', img: 'Silk-Like-Satin/Regency' }
        ];

        list.taffeta = [
            { name: 'Daffodil', img: 'Taffeta/Daffodil' },
            { name: 'Orange', img: 'Taffeta/Orange' },
            { name: 'Pink', img: 'Taffeta/Pink' },
            { name: 'Fuchsia', img: 'Taffeta/Fuchsia' },
            { name: 'Red', img: 'Taffeta/Red' },
            { name: 'Burgundy', img: 'Taffeta/Burgundy' },
            { name: 'Lilac', img: 'Taffeta/Lilac' },
            { name: 'Lavender', img: 'Taffeta/Lavender' },
            { name: 'Grape', img: 'Taffeta/Grape' },
            { name: 'Light Sky Blue', img: 'Taffeta/Light_Sky_Blue' },
            { name: 'Blue', img: 'Taffeta/Blue' },
            { name: 'Royal Blue', img: 'Taffeta/Royal_Blue' },
            { name: 'Dark Navy', img: 'Taffeta/Dark_Navy' },
            { name: 'Sage', img: 'Taffeta/Sage' },
            { name: 'Green', img: 'Taffeta/Green' },
            { name: 'Hunter', img: 'Taffeta/Hunter' },
            { name: 'Dark Green', img: 'Taffeta/Dark_Green' },
            { name: 'Brown', img: 'Taffeta/Brown' },
            { name: 'Chocolate', img: 'Taffeta/Chocolate' },
            { name: 'White', img: 'Taffeta/White' },
            { name: 'Ivory', img: 'Taffeta/Ivory' },
            { name: 'Champagne', img: 'Taffeta/Champagne' },
            { name: 'Gold', img: 'Taffeta/Gold' },
            { name: 'Silver', img: 'Taffeta/Silver' },
            { name: 'Black', img: 'Taffeta/Black' },
            { name: 'Pearl Pink', img: 'Taffeta/Pearl_Pink' },
            { name: 'Watermelon', img: 'Taffeta/Watermelon' },
            { name: 'Regency', img: 'Taffeta/Regency' }
        ];
        return list;
    }
});