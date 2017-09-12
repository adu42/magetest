ColorChart = Class.create();
Object.extend(Object.extend(ColorChart.prototype, Abstract.prototype), {
    initialize: function (wrapper, color, options) {
        this.wrapper = $(wrapper);
        this.color = color || [];  //默认色卡数组
        this.focusblock = '';   //色卡图片焦点块
        this.options = Object.extend({
            baseUrl: '',//色卡图片路径
            def: {},    //默认色卡
            list: {},   //色卡名称与图片路径映射数组
            x: 16,       //色卡图片左边距
            y: 1,       //色卡图片上边距
            width: 36,  //色卡图片焦点宽度
            height: 36, //色卡图片焦点高度
            rows: 0,    //色卡图片焦点行数
            cols: 4,    //色卡图片焦点列数
            spaceX: 32,  //色卡图片焦点列间距
            spaceY: 20   //色卡图片焦点行间距
        }, options || {});
        this.list();
    },
    list: function () {
        if (!this.color || !Object.isArray(this.color) || this.color.size() <= 0) {
            return;
        }
        if (!this.options.def || !this.options.list) {
            return;
        }
        this.color.each(function (colorname) {
            this.initColor(colorname, this.options.def[colorname]);
            this.listColor(colorname, this.options.list[colorname]);
        }, this);
        this.wrapper.select('dd.color-chart-focus > div').each(function (object) {
            Element.setStyle(object, {
                opacity: 0,
                'background-color': '#fff'
            });
            Event.observe(object, 'mouseover', this.hover.bindAsEventListener(this));
            Event.observe(object, 'mouseout', this.resume.bindAsEventListener(this));
            Event.observe(object, 'click', this.click.bindAsEventListener(this));
        }, this);
    },
    initColor: function (colorname, object) {
        if (!object) {
            return;
        }
        var ele = $(colorname).down('dl.color-chart-content');
        if (!ele) return;
        var dt = Element.down(ele, 'dt');
        var dd = Element.down(ele, 'dd');
        if (!dt || !dd) return;

        var ddUrl = this.options.baseUrl + object.ddImg + '.jpg';
        Element.update(dd, '<img src="' + ddUrl + '" />');
        //this.loadImage(ddUrl, function(){Element.update(dd, '<img src="' + ddUrl + '" />')});

        var dtUrl = this.options.baseUrl + object.dtImg + '.jpg';
        Element.update(dt, '<img src="' + dtUrl + '" />')
        //Element.update(dt, '<br /><br /><span style="color:#666;font-style:italic">loading ' + object.name + '</span>');
        //this.loadImage(dtUrl, function(){Element.update(dt, '<img src="' + dtUrl + '" /><span>' + object.name + '</span>')});

    },
    listColor: function (colorname, object) {
        if (!Object.isArray(object) || object.size() <= 0) {
            return;
        }
        this.focusblock = '';
        this.options.rows = 0;
        object.each(function (object, index) {
            var cols = index % this.options.cols;
            if (index > 0 && cols == 0) {
                this.options.rows++;
            }
            var x = this.options.x + (this.options.width + this.options.spaceX) * (index % this.options.cols);
            var y = this.options.y + (this.options.height + this.options.spaceY) * this.options.rows;
            this.focusblock += '<div to="' + object.img + '" as="' + object.as + '" title="' + object.name + '" style="left: ' + x + 'px;top: ' + y + 'px"></div>';
        }, this);
        var ele = $(colorname).down('dd.color-chart-focus');
        if (!ele) {
            return;
        }
        Element.insert(ele, {bottom: this.focusblock});
    },
    loadImage: function (url, callback, id) {
        var img = new Image();
        img.src = url;
        if (img.complete) {
            callback.call(img, id);
        } else {
            Event.observe(img, 'load', function () {
                callback.call(img, id)
            });
        }
    },
    hover: function (event) {
        var element = Event.findElement(event);
        if (!element) return;
        Element.setStyle(element, {opacity: 0.6});
        var title = Element.readAttribute(element, 'title');
        var url = this.options.baseUrl + Element.readAttribute(element, 'to') + '.jpg';
        var html = '<img src="' + url + '" /><span>' + title + '</span>';
        var bigImg = Element.down(Element.up(Element.up(element, 'dd'), 'dl'), 'dt');
        if (!bigImg) return;
        // Element.update(bigImg, '<br /><br /><span style="color:#666;font-style:italic">loading ' + title + '</span>');
        this.loadImage(url, function () {
            Element.update(bigImg, html);
            Element.setStyle(bigImg, {display: 'block'});
        });
    },
    resume: function (event) {
        var element = Event.findElement(event);
        if (!element) return;
        Element.setStyle(element, {opacity: 0});
        var bigImg = Element.down(Element.up(Element.up(element, 'dd'), 'dl'), 'dt');
        if (!bigImg) return;
        Element.setStyle(bigImg, {display: 'none'});
    },
    click: function (event) {
        var element = Event.findElement(event);
        if (!element) return;
        var title = Element.readAttribute(element, 'title');
        var mas = Element.readAttribute(element, 'as');
        var url = this.options.baseUrl + Element.readAttribute(element, 'to') + '.jpg';
        var html = '<img src="' + url + '" /><span>' + title + '</span>';
        var bigImg = Element.down(Element.up(Element.up(element, 'dd'), 'dl'), 'dt');
        if (!bigImg) return;
        Element.update(bigImg, '<br /><br /><span style="color:#666;font-style:italic">loading ' + title + '</span>');
        this.loadImage(url, function () {
            Element.update(bigImg, html);
            Element.setStyle(bigImg, {display: 'block'});
        });
        this.giveVal(mas);

    },
    giveVal: function (color) {
        var select = jQuery('select[title=color]');
        color = color.toLowerCase();
        if (select && select.length > 0) {
            jQuery(select).find('option').each(function () {
                var val = jQuery(this).attr('as');
                if (val == color) {
                    jQuery(this).attr('selected', true);
                    return false;
                }
            });
        }
    }
});

DressColor = Class.create();
Object.extend(Object.extend(DressColor.prototype, ColorChart.prototype), {
    _parent: ColorChart.prototype,

    initialize: function (wrapper, color, options) {
        this.doptions = Object.extend({
            def: this.defaultColor(),    //默认色卡
            list: this.getListColor()   //色卡名称与图片路径映射数组           
        }, options || {});

        this._parent.initialize.call(this, wrapper, color, this.doptions);
    },
    defaultColor: function () {
        var def = {};
        def['chiffon'] = {name: 'Daffodil', dtImg: 'Chiffon/Daffodil', ddImg: 'Chiffon'};
        def['elastic_woven_satin'] = {
            name: 'Daffodil',
            dtImg: 'Elastic-Woven-Satin/Daffodil',
            ddImg: 'Elastic-Woven-Satin'
        };
        def['matte_satin'] = {name: 'Daffodil', dtImg: 'Matte-Satin/Daffodil', ddImg: 'Matte-Satin'};
        def['organza'] = {name: 'Daffodil', dtImg: 'Organza/Daffodil', ddImg: 'Organza'};
        def['satin'] = {name: 'Daffodil', dtImg: 'Satin/Daffodil', ddImg: 'Satin'};
        def['silk_like_satin'] = {name: 'Daffodil', dtImg: 'Silk-Like-Satin/Daffodil', ddImg: 'Silk-Like-Satin'};
        def['taffeta'] = {name: 'Daffodil', dtImg: 'Taffeta/Daffodil', ddImg: 'Taffeta'};
        return def;
    },
    getListColor: function () {
        var list = {};
        list.chiffon = [
            {name: 'White', img: 'Chiffon/White', as: 'White'},
            {name: 'Regency', img: 'Chiffon/Regency', as: 'Regency'},
            {name: 'Watermelon', img: 'Chiffon/Watermelon', as: 'Watermelon'},
            {name: 'Orange', img: 'Chiffon/Orange', as: 'Orange'},
            {name: 'Black', img: 'Chiffon/Black', as: 'Black'},
            {name: 'Blue', img: 'Chiffon/Blue', as: 'Blue'},
            {name: 'Brown', img: 'Chiffon/Brown', as: 'Brown'},
            {name: 'Burgundy', img: 'Chiffon/Burgundy', as: 'Burgundy'},
            {name: 'Daffodil', img: 'Chiffon/Daffodil', as: 'Daffodil'},
            {name: 'Dark Green', img: 'Chiffon/Dark_Green', as: 'Dark Green'},
            {name: 'Fuchsia', img: 'Chiffon/Fuchsia', as: 'Fuchsia'},
            {name: 'Gold', img: 'Chiffon/Gold', as: 'Gold'},
            {name: 'Ivory', img: 'Chiffon/Ivory', as: 'Ivory'},
            {name: 'Lavender', img: 'Chiffon/Lavender', as: 'Lavender'},
            {name: 'Pearl Pink', img: 'Chiffon/Pearl_Pink', as: 'Pearl Pink'},
            {name: 'Light Sky Blue', img: 'Chiffon/Light_Sky_Blue', as: 'Light Sky Blue'},
            {name: 'Royal Blue', img: 'Chiffon/Royal_Blue', as: 'Royal Blue'},
            {name: 'Sage', img: 'Chiffon/Sage', as: 'Sage'},
            {name: 'Red', img: 'Chiffon/Red', as: 'Red'},
            {name: 'Silver', img: 'Chiffon/Silver', as: 'Silver'},
            {name: 'Champagne', img: 'Chiffon/Champagne', as: 'Champagne'},
            {name: 'Chocolate', img: 'Chiffon/Chocolate', as: 'Chocolate'},
            {name: 'Dark Navy', img: 'Chiffon/Dark_Navy', as: 'Dark Navy'},
            {name: 'Pink', img: 'Chiffon/Pink', as: 'Pink'},
            {name: 'Grape', img: 'Chiffon/Grape', as: 'Grape'},
            {name: 'Green', img: 'Chiffon/Green', as: 'Green'},
            {name: 'Hunter', img: 'Chiffon/Hunter', as: 'Hunter'},
            {name: 'Lilac', img: 'Chiffon/Lilac', as: 'Lilac'}


        ];

        list.elastic_woven_satin = [
            {name: 'Daffodil', img: 'Elastic-Woven-Satin/Daffodil', as: 'Daffodil'},
            {name: 'Orange', img: 'Elastic-Woven-Satin/Orange', as: 'Orange'},
            {name: 'Pink', img: 'Elastic-Woven-Satin/Pink', as: 'Pink'},
            {name: 'Fuchsia', img: 'Elastic-Woven-Satin/Fuchsia', as: 'Fuchsia'},
            {name: 'Lavender', img: 'Elastic-Woven-Satin/Lavender', as: 'Lavender'},
            {name: 'Grape', img: 'Elastic-Woven-Satin/Grape', as: 'Grape'},
            {name: 'Light Sky Blue', img: 'Elastic-Woven-Satin/Light_Sky_Blue', as: 'Light Sky Blue'},
            {name: 'Blue', img: 'Elastic-Woven-Satin/Blue', as: 'Blue'},
            {name: 'Green', img: 'Elastic-Woven-Satin/Green', as: 'Green'},
            {name: 'Hunter', img: 'Elastic-Woven-Satin/Hunter', as: 'Hunter'},
            {name: 'Dark Green', img: 'Elastic-Woven-Satin/Dark_Green', as: 'Dark Green'},
            {name: 'Brown', img: 'Elastic-Woven-Satin/Brown', as: 'Brown'},
            {name: 'Champagne', img: 'Elastic-Woven-Satin/Champagne', as: 'Champagne'},
            {name: 'Gold', img: 'Elastic-Woven-Satin/Gold', as: 'Gold'},
            {name: 'Silver', img: 'Elastic-Woven-Satin/Silver', as: 'Silver'},
            {name: 'Black', img: 'Elastic-Woven-Satin/Black', as: 'Black'},
            {name: 'Red', img: 'Elastic-Woven-Satin/Red', as: 'Red'},
            {name: 'Burgundy', img: 'Elastic-Woven-Satin/Burgundy', as: 'Burgundy'},
            {name: 'Lilac', img: 'Elastic-Woven-Satin/Lilac', as: 'Lilac'},
            {name: 'Regency', img: 'Elastic-Woven-Satin/Regency', as: 'Regency'},
            {name: 'Royal Blue', img: 'Elastic-Woven-Satin/Royal_Blue', as: 'Royal Blue'},
            {name: 'Dark Navy', img: 'Elastic-Woven-Satin/Dark_Navy', as: 'Dark Navy'},
            {name: 'Sage', img: 'Elastic-Woven-Satin/Sage', as: 'Sage'},
            {name: 'Watermelon', img: 'Elastic-Woven-Satin/Watermelon', as: 'Watermelon'},
            {name: 'Chocolate', img: 'Elastic-Woven-Satin/Chocolate', as: 'Chocolate'},
            {name: 'White', img: 'Elastic-Woven-Satin/White', as: 'White'},
            {name: 'Ivory', img: 'Elastic-Woven-Satin/Ivory', as: 'Ivory'},
            {name: 'Pearl Pink', img: 'Elastic-Woven-Satin/Pearl_Pink', as: 'Pearl Pink'}
        ];

        list.matte_satin = [
            {name: 'Daffodil', img: 'Elastic-Woven-Satin/Daffodil', as: 'Daffodil'},
            {name: 'Orange', img: 'Elastic-Woven-Satin/Orange', as: 'Orange'},
            {name: 'Pink', img: 'Elastic-Woven-Satin/Pink', as: 'Pink'},
            {name: 'Fuchsia', img: 'Elastic-Woven-Satin/Fuchsia', as: 'Fuchsia'},
            {name: 'Lavender', img: 'Elastic-Woven-Satin/Lavender', as: 'Lavender'},
            {name: 'Grape', img: 'Elastic-Woven-Satin/Grape', as: 'Grape'},
            {name: 'Light Sky Blue', img: 'Elastic-Woven-Satin/Light_Sky_Blue', as: 'Light Sky Blue'},
            {name: 'Blue', img: 'Elastic-Woven-Satin/Blue', as: 'Blue'},
            {name: 'Green', img: 'Elastic-Woven-Satin/Green', as: 'Green'},
            {name: 'Hunter', img: 'Elastic-Woven-Satin/Hunter', as: 'Hunter'},
            {name: 'Dark Green', img: 'Elastic-Woven-Satin/Dark_Green', as: 'Dark Green'},
            {name: 'Brown', img: 'Elastic-Woven-Satin/Brown', as: 'Brown'},
            {name: 'Champagne', img: 'Elastic-Woven-Satin/Champagne', as: 'Champagne'},
            {name: 'Gold', img: 'Elastic-Woven-Satin/Gold', as: 'Gold'},
            {name: 'Silver', img: 'Elastic-Woven-Satin/Silver', as: 'Silver'},
            {name: 'Black', img: 'Elastic-Woven-Satin/Black', as: 'Black'},
            {name: 'Red', img: 'Elastic-Woven-Satin/Red', as: 'Red'},
            {name: 'Burgundy', img: 'Elastic-Woven-Satin/Burgundy', as: 'Burgundy'},
            {name: 'Lilac', img: 'Elastic-Woven-Satin/Lilac', as: 'Lilac'},
            {name: 'Pearl Pink', img: 'Elastic-Woven-Satin/Pearl_Pink', as: 'Pearl Pink'},
            {name: 'Royal Blue', img: 'Elastic-Woven-Satin/Royal_Blue', as: 'Royal Blue'},
            {name: 'Dark Navy', img: 'Elastic-Woven-Satin/Dark_Navy', as: 'Dark Navy'},
            {name: 'Sage', img: 'Elastic-Woven-Satin/Sage', as: 'Sage'},
            {name: 'Watermelon', img: 'Elastic-Woven-Satin/Watermelon', as: 'Watermelon'},
            {name: 'Chocolate', img: 'Elastic-Woven-Satin/Chocolate', as: 'Chocolate'},
            {name: 'White', img: 'Elastic-Woven-Satin/White', as: 'White'},
            {name: 'Ivory', img: 'Elastic-Woven-Satin/Ivory', as: 'Ivory'},
            {name: 'Regency', img: 'Elastic-Woven-Satin/Regency', as: 'Regency'}
        ];

        list.organza = [
            {name: 'Grape', img: 'Organza/Grape', as: 'Grape'},
            {name: 'Green', img: 'Organza/Green', as: 'Green'},
            {name: 'Hunter', img: 'Organza/Hunter', as: 'Hunter'},
            {name: 'Daffodil', img: 'Organza/Daffodil', as: 'Daffodil'},
            {name: 'Lilac', img: 'Organza/Lilac', as: 'Lilac'},
            {name: 'Orange', img: 'Organza/Orange', as: 'Orange'},
            {name: 'Pink', img: 'Organza/Pink', as: 'Pink'},
            {name: 'Burgundy', img: 'Organza/Burgundy', as: 'Burgundy'},
            {name: 'White', img: 'Organza/White', as: 'White'},
            {name: 'Regency', img: 'Organza/Regency', as: 'Regency'},
            {name: 'Watermelon', img: 'Organza/Watermelon', as: 'Watermelon'},
            {name: 'Chocolate', img: 'Organza/Chocolate', as: 'Chocolate'},
            {name: 'Brown', img: 'Organza/Brown', as: 'Brown'},
            {name: 'Black', img: 'Organza/Black', as: 'Black'},
            {name: 'Blue', img: 'Organza/Blue', as: 'Blue'},
            {name: 'Champagne', img: 'Organza/Champagne', as: 'Champagne'},
            {name: 'Dark Navy', img: 'Organza/Dark_Navy', as: 'Dark Navy'},
            {name: 'Dark Green', img: 'Organza/Dark_Green', as: 'Dark Green'},
            {name: 'Fuchsia', img: 'Organza/Fuchsia', as: 'Fuchsia'},
            {name: 'Gold', img: 'Organza/Gold', as: 'Gold'},
            {name: 'Ivory', img: 'Organza/Ivory', as: 'Ivory'},
            {name: 'Lavender', img: 'Organza/Lavender', as: 'Lavender'},
            {name: 'Pearl Pink', img: 'Organza/Pearl_Pink', as: 'Pearl Pink'},
            {name: 'Light Sky Blue', img: 'Organza/Light_Sky_Blue', as: 'Light Sky Blue'},
            {name: 'Red', img: 'Organza/Red', as: 'Red'},
            {name: 'Royal Blue', img: 'Organza/Royal_Blue', as: 'Royal Blue'},
            {name: 'Sage', img: 'Organza/Sage', as: 'Sage'},
            {name: 'Silver', img: 'Organza/Silver', as: 'Silver'}
        ];

        list.satin = [
            {name: 'Daffodil', img: 'Elastic-Woven-Satin/Daffodil', as: 'Daffodil'},
            {name: 'Orange', img: 'Elastic-Woven-Satin/Orange', as: 'Orange'},
            {name: 'Pink', img: 'Elastic-Woven-Satin/Pink', as: 'Pink'},
            {name: 'Fuchsia', img: 'Elastic-Woven-Satin/Fuchsia', as: 'Fuchsia'},
            {name: 'Lavender', img: 'Elastic-Woven-Satin/Lavender', as: 'Lavender'},
            {name: 'Grape', img: 'Elastic-Woven-Satin/Grape', as: 'Grape'},
            {name: 'Light Sky Blue', img: 'Elastic-Woven-Satin/Light_Sky_Blue', as: 'Light Sky Blue'},
            {name: 'Blue', img: 'Elastic-Woven-Satin/Blue', as: 'Blue'},
            {name: 'Green', img: 'Elastic-Woven-Satin/Green', as: 'Green'},
            {name: 'Hunter', img: 'Elastic-Woven-Satin/Hunter', as: 'Hunter'},
            {name: 'Dark Green', img: 'Elastic-Woven-Satin/Dark_Green', as: 'Dark Green'},
            {name: 'Brown', img: 'Elastic-Woven-Satin/Brown', as: 'Brown'},
            {name: 'Champagne', img: 'Elastic-Woven-Satin/Champagne', as: 'Champagne'},
            {name: 'Gold', img: 'Elastic-Woven-Satin/Gold', as: 'Gold'},
            {name: 'Silver', img: 'Elastic-Woven-Satin/Silver', as: 'Silver'},
            {name: 'Black', img: 'Elastic-Woven-Satin/Black', as: 'Black'},
            {name: 'Royal Blue', img: 'Elastic-Woven-Satin/Royal_Blue', as: 'Royal Blue'},
            {name: 'Dark Navy', img: 'Elastic-Woven-Satin/Dark_Navy', as: 'Dark Navy'},
            {name: 'Sage', img: 'Elastic-Woven-Satin/Sage', as: 'Sage'},
            {name: 'Red', img: 'Elastic-Woven-Satin/Red', as: 'Red'},
            {name: 'Chocolate', img: 'Elastic-Woven-Satin/Chocolate', as: 'Chocolate'},
            {name: 'White', img: 'Elastic-Woven-Satin/White', as: 'White'},
            {name: 'Ivory', img: 'Elastic-Woven-Satin/Ivory', as: 'Ivory'},
            {name: 'Burgundy', img: 'Elastic-Woven-Satin/Burgundy', as: 'Burgundy'},
            {name: 'Pearl Pink', img: 'Elastic-Woven-Satin/Pearl_Pink', as: 'Pearl Pink'},
            {name: 'Watermelon', img: 'Elastic-Woven-Satin/Watermelon', as: 'Watermelon'},
            {name: 'Regency', img: 'Elastic-Woven-Satin/Regency', as: 'Regency'},
            {name: 'Lilac', img: 'Elastic-Woven-Satin/Lilac', as: 'Lilac'}

        ];

        list.silk_like_satin = [
            {name: 'Daffodil', img: 'Elastic-Woven-Satin/Daffodil', as: 'Daffodil'},
            {name: 'Orange', img: 'Elastic-Woven-Satin/Orange', as: 'Orange'},
            {name: 'Pink', img: 'Elastic-Woven-Satin/Pink', as: 'Pink'},
            {name: 'Fuchsia', img: 'Elastic-Woven-Satin/Fuchsia', as: 'Fuchsia'},
            {name: 'Lavender', img: 'Elastic-Woven-Satin/Lavender', as: 'Lavender'},
            {name: 'Grape', img: 'Elastic-Woven-Satin/Grape', as: 'Grape'},
            {name: 'Light Sky Blue', img: 'Elastic-Woven-Satin/Light_Sky_Blue', as: 'Light Sky Blue'},
            {name: 'Blue', img: 'Elastic-Woven-Satin/Blue', as: 'Blue'},
            {name: 'Green', img: 'Elastic-Woven-Satin/Green', as: 'Green'},
            {name: 'Hunter', img: 'Elastic-Woven-Satin/Hunter', as: 'Hunter'},
            {name: 'Dark Green', img: 'Elastic-Woven-Satin/Dark_Green', as: 'Dark Green'},
            {name: 'Brown', img: 'Elastic-Woven-Satin/Brown', as: 'Brown'},
            {name: 'Champagne', img: 'Elastic-Woven-Satin/Champagne', as: 'Champagne'},
            {name: 'Gold', img: 'Elastic-Woven-Satin/Gold', as: 'Gold'},
            {name: 'Silver', img: 'Elastic-Woven-Satin/Silver', as: 'Silver'},
            {name: 'Black', img: 'Elastic-Woven-Satin/Black', as: 'Black'},
            {name: 'Royal Blue', img: 'Elastic-Woven-Satin/Royal_Blue', as: 'Royal Blue'},
            {name: 'Dark Navy', img: 'Elastic-Woven-Satin/Dark_Navy', as: 'Dark Navy'},
            {name: 'Sage', img: 'Elastic-Woven-Satin/Sage', as: 'Sage'},
            {name: 'Red', img: 'Elastic-Woven-Satin/Red', as: 'Red'},
            {name: 'Chocolate', img: 'Elastic-Woven-Satin/Chocolate', as: 'Chocolate'},
            {name: 'White', img: 'Elastic-Woven-Satin/White', as: 'White'},
            {name: 'Ivory', img: 'Elastic-Woven-Satin/Ivory', as: 'Ivory'},
            {name: 'Burgundy', img: 'Elastic-Woven-Satin/Burgundy', as: 'Burgundy'},
            {name: 'Pearl Pink', img: 'Elastic-Woven-Satin/Pearl_Pink', as: 'Pearl Pink'},
            {name: 'Watermelon', img: 'Elastic-Woven-Satin/Watermelon', as: 'Watermelon'},
            {name: 'Regency', img: 'Elastic-Woven-Satin/Regency', as: 'Regency'},
            {name: 'Lilac', img: 'Elastic-Woven-Satin/Lilac', as: 'Lilac'}
        ];

        list.taffeta = [
            {name: 'Brown', img: 'Taffeta/Brown', as: 'Brown'},
            {name: 'Burgundy', img: 'Taffeta/Burgundy', as: 'Burgundy'},
            {name: 'Black', img: 'Taffeta/Black', as: 'Black'},
            {name: 'Blue', img: 'Taffeta/Blue', as: 'Blue'},
            {name: 'Dark Green', img: 'Taffeta/Dark_Green', as: 'Dark Green'},
            {name: 'Dark Navy', img: 'Taffeta/Dark_Navy', as: 'Dark Navy'},
            {name: 'Fuchsia', img: 'Taffeta/Fuchsia', as: 'Fuchsia'},
            {name: 'Gold', img: 'Taffeta/Gold', as: 'Gold'},
            {name: 'Ivory', img: 'Taffeta/Ivory', as: 'Ivory'},
            {name: 'Lavender', img: 'Taffeta/Lavender', as: 'Lavender'},
            {name: 'Pearl Pink', img: 'Taffeta/Pearl_Pink', as: 'Pearl Pink'},
            {name: 'Light Sky Blue', img: 'Taffeta/Light_Sky_Blue', as: 'Light Sky Blue'},
            {name: 'Red', img: 'Taffeta/Red', as: 'Red'},
            {name: 'Royal Blue', img: 'Taffeta/Royal_Blue', as: 'Royal Blue'},
            {name: 'Sage', img: 'Taffeta/Sage', as: 'Sage'},
            {name: 'Silver', img: 'Taffeta/Silver', as: 'Silver'},
            {name: 'Grape', img: 'Taffeta/Grape', as: 'Grape'},
            {name: 'Green', img: 'Taffeta/Green', as: 'Green'},
            {name: 'Hunter', img: 'Taffeta/Hunter', as: 'Hunter'},
            {name: 'Champagne', img: 'Taffeta/Champagne', as: 'Champagne'},
            {name: 'Lilac', img: 'Taffeta/Lilac', as: 'Lilac'},
            {name: 'Orange', img: 'Taffeta/Orange', as: 'Orange'},
            {name: 'Pink', img: 'Taffeta/Pink', as: 'Pink'},
            {name: 'Chocolate', img: 'Taffeta/Chocolate', as: 'Chocolate'},
            {name: 'White', img: 'Taffeta/White', as: 'White'},
            {name: 'Regency', img: 'Taffeta/Regency', as: 'Regency'},
            {name: 'Watermelon', img: 'Taffeta/Watermelon', as: 'Watermelon'},
            {name: 'Daffodil', img: 'Taffeta/Daffodil', as: 'Daffodil'}
        ];
        return list;
    }
});