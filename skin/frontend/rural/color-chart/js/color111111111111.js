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
        Element.update(bigImg, '<br /><br /><span style="color:#666;font-style:italic">chargement de ' + title + '</span>');
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
        def['chiffon'] = {name: 'Jonquille', dtImg: 'Chiffon/Daffodil', ddImg: 'Chiffon'};
        def['elastic_woven_satin'] = {name: 'Jonquille', dtImg: 'Elastic-Woven-Satin/Daffodil', ddImg: 'Elastic-Woven-Satin'};
        def['matte_satin'] = {name: 'Jonquille', dtImg: 'Matte-Satin/Daffodil', ddImg: 'Matte-Satin'};
        def['organza'] = {name: 'Jonquille', dtImg: 'Organza/Daffodil', ddImg: 'Organza'};
        def['satin'] = {name: 'Jonquille', dtImg: 'Satin/Daffodil', ddImg: 'Satin'};
        def['silk_like_satin'] = {name: 'Jonquille', dtImg: 'Silk-Like-Satin/Daffodil', ddImg: 'Silk-Like-Satin'};
        def['taffeta'] = {name: 'Jonquille', dtImg: 'Taffeta/Daffodil', ddImg: 'Taffeta'};
        return def;
    },
    getListColor: function(){
        var list = {};
        list.chiffon = [
{ name: 'Noir', img: 'Chiffon/Black' },
  { name: 'Bleu', img: 'Chiffon/Blue' },
        { name: 'Marron', img: 'Chiffon/Brown' },
  { name: 'Bordeaux', img: 'Chiffon/Burgundy' },
  { name: 'Champagne', img: 'Chiffon/Champagne' },
       { name: 'Chocolat', img: 'Chiffon/Chocolate' },
 { name: 'marine foncé', img: 'Chiffon/Dark_Navy' },
            { name: 'Jonquille', img: 'Chiffon/Daffodil' },
     { name: 'Vert Foncé', img: 'Chiffon/Dark_Green' },
  { name: 'Fuchsia', img: 'Chiffon/Fuchsia' },
   { name: 'Or', img: 'Chiffon/Gold' },
     { name: 'Pourpre', img: 'Chiffon/Grape' },
          { name: 'Vert de Trèfle', img: 'Chiffon/Green' },
         { name: 'Vert de Jade', img: 'Chiffon/Hunter' },
        { name: 'Ivoire', img: 'Chiffon/Ivory' },
         { name: 'Lavende', img: 'Chiffon/Lavender' },
    { name: 'Pêche', img: 'Chiffon/Pearl_Pink' },
{ name: 'Bleu Ciel', img: 'Chiffon/Light_Sky_Blue' },
{ name: 'Lilas', img: 'Chiffon/Lilac' },

            { name: 'Orange', img: 'Chiffon/Orange' },

            { name: 'Rose Claire', img: 'Chiffon/Pink' },
           { name: 'Bleu Saphir', img: 'Chiffon/Royal_Blue' },
    { name: 'Vert Lichen', img: 'Chiffon/Sage' },


            { name: 'Couleur Rubis', img: 'Chiffon/Red' },
          
            
   
       
            
          
           
           
        
  
   
       
    
                 { name: 'Argent', img: 'Chiffon/Silver' },
            { name: 'Blanc', img: 'Chiffon/White' },
    
          
         

              { name: 'Indigo', img: 'Chiffon/Regency' },
        
            { name: 'Incarnadin', img: 'Chiffon/Watermelon' }
          
        ];

        list.elastic_woven_satin = [
            { name: 'Jonquille', img: 'Elastic-Woven-Satin/Daffodil' },
            { name: 'Orange', img: 'Elastic-Woven-Satin/Orange' },
            { name: 'Rose Claire', img: 'Elastic-Woven-Satin/Pink' },
            { name: 'Fuchsia', img: 'Elastic-Woven-Satin/Fuchsia' },
            { name: 'Couleur Rubis', img: 'Elastic-Woven-Satin/Red' },
            { name: 'Bordeaux', img: 'Elastic-Woven-Satin/Burgundy' },
            { name: 'Lilas', img: 'Elastic-Woven-Satin/Lilac' },
            { name: 'Lavende', img: 'Elastic-Woven-Satin/Lavender' },
            { name: 'Pourpre', img: 'Elastic-Woven-Satin/Grape' },
            { name: 'Bleu Ciel', img: 'Elastic-Woven-Satin/Light_Sky_Blue' },
            { name: 'Bleu', img: 'Elastic-Woven-Satin/Blue' },
            { name: 'Bleu Saphir', img: 'Elastic-Woven-Satin/Royal_Blue' },
            { name: 'marine foncé', img: 'Elastic-Woven-Satin/Dark_Navy' },
            { name: 'Vert Lichen', img: 'Elastic-Woven-Satin/Sage' },
            { name: 'Vert de Trèfle', img: 'Elastic-Woven-Satin/Green' },
            { name: 'Vert de Jade', img: 'Elastic-Woven-Satin/Hunter' },
            { name: 'Vert Foncé', img: 'Elastic-Woven-Satin/Dark_Green' },
            { name: 'Marron', img: 'Elastic-Woven-Satin/Brown' },
            { name: 'Chocolat', img: 'Elastic-Woven-Satin/Chocolate' },
            { name: 'Blanc', img: 'Elastic-Woven-Satin/White' },
            { name: 'Ivoire', img: 'Elastic-Woven-Satin/Ivory' },
            { name: 'Champagne', img: 'Elastic-Woven-Satin/Champagne' },
            { name: 'Or', img: 'Elastic-Woven-Satin/Gold' },
            { name: 'Argent', img: 'Elastic-Woven-Satin/Silver' },
            { name: 'Noir', img: 'Elastic-Woven-Satin/Black' },
            { name: 'Pêche', img: 'Elastic-Woven-Satin/Pearl_Pink' },
            { name: 'Incarnadin', img: 'Elastic-Woven-Satin/Watermelon' },
            { name: 'Indigo', img: 'Elastic-Woven-Satin/Regency' }
        ];

        list.matte_satin = [
            { name: 'Jonquille', img: 'Matte-Satin/Daffodil' },
            { name: 'Orange', img: 'Matte-Satin/Orange' },
            { name: 'Rose Claire', img: 'Matte-Satin/Pink' },
            { name: 'Fuchsia', img: 'Matte-Satin/Fuchsia' },
            { name: 'Couleur Rubis', img: 'Matte-Satin/Red' },
            { name: 'Bordeaux', img: 'Matte-Satin/Burgundy' },
            { name: 'Lilas', img: 'Matte-Satin/Lilac' },
            { name: 'Lavende', img: 'Matte-Satin/Lavender' },
            { name: 'Pourpre', img: 'Matte-Satin/Grape' },
            { name: 'Bleu Ciel', img: 'Matte-Satin/Light_Sky_Blue' },
            { name: 'Bleu', img: 'Matte-Satin/Blue' },
            { name: 'Bleu Saphir', img: 'Matte-Satin/Royal_Blue' },
            { name: 'marine foncé', img: 'Matte-Satin/Dark_Navy' },
            { name: 'Vert Lichen', img: 'Matte-Satin/Sage' },
            { name: 'Vert de Trèfle', img: 'Matte-Satin/Green' },
            { name: 'Vert de Jade', img: 'Matte-Satin/Hunter' },
            { name: 'Vert Foncé', img: 'Matte-Satin/Dark_Green' },
            { name: 'Marron', img: 'Matte-Satin/Brown' },
            { name: 'Chocolat', img: 'Matte-Satin/Chocolate' },
            { name: 'Blanc', img: 'Matte-Satin/White' },
            { name: 'Ivoire', img: 'Matte-Satin/Ivory' },
            { name: 'Champagne', img: 'Matte-Satin/Champagne' },
            { name: 'Or', img: 'Matte-Satin/Gold' },
            { name: 'Argent', img: 'Matte-Satin/Silver' },
            { name: 'Noir', img: 'Matte-Satin/Black' },
            { name: 'Pêche', img: 'Matte-Satin/Pearl_Pink' },
            { name: 'Incarnadin', img: 'Matte-Satin/Watermelon' },
            { name: 'Indigo', img: 'Matte-Satin/Regency' }
        ];

        list.organza = [
			{ name: 'Marron', img: 'Organza/Brown' },
{ name: 'Noir', img: 'Organza/Black' },
 { name: 'Bleu', img: 'Organza/Blue' },
  { name: 'Champagne', img: 'Organza/Champagne' },
 { name: 'Chocolat', img: 'Organza/Chocolate' },
   { name: 'Bordeaux', img: 'Organza/Burgundy' },
            { name: 'Jonquille', img: 'Organza/Daffodil' },
    { name: 'marine foncé', img: 'Organza/Dark_Navy' },
       { name: 'Vert Foncé', img: 'Organza/Dark_Green' },
          { name: 'Fuchsia', img: 'Organza/Fuchsia' },
         { name: 'Or', img: 'Organza/Gold' },
            { name: 'Pourpre', img: 'Organza/Grape' },
       { name: 'Vert de Trèfle', img: 'Organza/Green' },
       { name: 'Vert de Jade', img: 'Organza/Hunter' },
      { name: 'Ivoire', img: 'Organza/Ivory' },
   { name: 'Lavende', img: 'Organza/Lavender' },
           { name: 'Pêche', img: 'Organza/Pearl_Pink' },
    { name: 'Bleu Ciel', img: 'Organza/Light_Sky_Blue' },
               { name: 'Lilas', img: 'Organza/Lilac' },
            { name: 'Orange', img: 'Organza/Orange' },
            { name: 'Rose Claire', img: 'Organza/Pink' },
  
            { name: 'Couleur Rubis', img: 'Organza/Red' },
         
       
         

         
            { name: 'Bleu Saphir', img: 'Organza/Royal_Blue' },
        
            { name: 'Vert Lichen', img: 'Organza/Sage' },
     
     
     
                    { name: 'Argent', img: 'Organza/Silver' },
           
            { name: 'Blanc', img: 'Organza/White' },
      
 { name: 'Indigo', img: 'Organza/Regency' },
   
    
            
 
            { name: 'Incarnadin', img: 'Organza/Watermelon' }
            
        ];

        list.satin = [
            { name: 'Jonquille', img: 'Satin/Daffodil' },
            { name: 'Orange', img: 'Satin/Orange' },
            { name: 'Rose Claire', img: 'Satin/Pink' },
            { name: 'Fuchsia', img: 'Satin/Fuchsia' },
            { name: 'Couleur Rubis', img: 'Satin/Red' },
            { name: 'Bordeaux', img: 'Satin/Burgundy' },
            { name: 'Lilas', img: 'Satin/Lilac' },
            { name: 'Lavende', img: 'Satin/Lavender' },
            { name: 'Pourpre', img: 'Satin/Grape' },
            { name: 'Bleu Ciel', img: 'Satin/Light_Sky_Blue' },
            { name: 'Bleu', img: 'Satin/Blue' },
            { name: 'Bleu Saphir', img: 'Satin/Royal_Blue' },
            { name: 'marine foncé', img: 'Satin/Dark_Navy' },
            { name: 'Vert Lichen', img: 'Satin/Sage' },
            { name: 'Vert de Trèfle', img: 'Satin/Green' },
            { name: 'Vert de Jade', img: 'Satin/Hunter' },
            { name: 'Vert Foncé', img: 'Satin/Dark_Green' },
            { name: 'Marron', img: 'Satin/Brown' },
            { name: 'Chocolat', img: 'Satin/Chocolate' },
            { name: 'Blanc', img: 'Satin/White' },
            { name: 'Ivoire', img: 'Satin/Ivory' },
            { name: 'Champagne', img: 'Satin/Champagne' },
            { name: 'Or', img: 'Satin/Gold' },
            { name: 'Argent', img: 'Satin/Silver' },
            { name: 'Noir', img: 'Satin/Black' },
            { name: 'Pêche', img: 'Satin/Pearl_Pink' },
            { name: 'Incarnadin', img: 'Satin/Watermelon' },
            { name: 'Indigo', img: 'Satin/Regency' }
        ];

        list.silk_like_satin = [
            { name: 'Jonquille', img: 'Silk-Like-Satin/Daffodil' },
            { name: 'Orange', img: 'Silk-Like-Satin/Orange' },
            { name: 'Rose Claire', img: 'Silk-Like-Satin/Pink' },
            { name: 'Fuchsia', img: 'Silk-Like-Satin/Fuchsia' },
            { name: 'Couleur Rubis', img: 'Silk-Like-Satin/Red' },
            { name: 'Bordeaux', img: 'Silk-Like-Satin/Burgundy' },
            { name: 'Lilas', img: 'Silk-Like-Satin/Lilac' },
            { name: 'Lavende', img: 'Silk-Like-Satin/Lavender' },
            { name: 'Pourpre', img: 'Silk-Like-Satin/Grape' },
            { name: 'Bleu Ciel', img: 'Silk-Like-Satin/Light_Sky_Blue' },
            { name: 'Bleu', img: 'Silk-Like-Satin/Blue' },
            { name: 'Bleu Saphir', img: 'Silk-Like-Satin/Royal_Blue' },
            { name: 'Marine Foncé', img: 'Silk-Like-Satin/Dark_Navy' },
            { name: 'Vert Lichen', img: 'Silk-Like-Satin/Sage' },
            { name: 'Vert de Trèfle', img: 'Silk-Like-Satin/Green' },
            { name: 'Vert de Jade', img: 'Silk-Like-Satin/Hunter' },
            { name: 'Vert Foncé', img: 'Silk-Like-Satin/Dark_Green' },
            { name: 'Marron', img: 'Silk-Like-Satin/Brown' },
            { name: 'Chocolat', img: 'Silk-Like-Satin/Chocolate' },
            { name: 'Blanc', img: 'Silk-Like-Satin/White' },
            { name: 'Ivoire', img: 'Silk-Like-Satin/Ivory' },
            { name: 'Champagne', img: 'Silk-Like-Satin/Champagne' },
            { name: 'Or', img: 'Silk-Like-Satin/Gold' },
            { name: 'Argent', img: 'Silk-Like-Satin/Silver' },
            { name: 'Noir', img: 'Silk-Like-Satin/Black' },
            { name: 'Pêche', img: 'Silk-Like-Satin/Pearl_Pink' },
            { name: 'Incarnadin', img: 'Silk-Like-Satin/Watermelon' },
            { name: 'Indigo', img: 'Silk-Like-Satin/Regency' }
        ];

        list.taffeta = [
             { name: 'Marron', img: 'Taffeta/Brown' },
		  { name: 'Bordeaux', img: 'Taffeta/Burgundy' },
 { name: 'Noir', img: 'Taffeta/Black' },
   { name: 'Bleu', img: 'Taffeta/Blue' },
		    { name: 'Champagne', img: 'Taffeta/Champagne' },
       { name: 'Chocolat', img: 'Taffeta/Chocolate' },
	{ name: 'Jonquille', img: 'Taffeta/Daffodil' },
     { name: 'Vert Foncé', img: 'Taffeta/Dark_Green' },
     { name: 'marine foncé', img: 'Taffeta/Dark_Navy' },
            { name: 'Fuchsia', img: 'Taffeta/Fuchsia' },
    { name: 'Or', img: 'Taffeta/Gold' },
          { name: 'Pourpre', img: 'Taffeta/Grape' },
       { name: 'Vert de Trèfle', img: 'Taffeta/Green' },
    { name: 'Vert de Jade', img: 'Taffeta/Hunter' },
     { name: 'Ivoire', img: 'Taffeta/Ivory' },
    { name: 'Lavende', img: 'Taffeta/Lavender' },
	            { name: 'Pêche', img: 'Taffeta/Pearl_Pink' },
   { name: 'Bleu Ciel', img: 'Taffeta/Light_Sky_Blue' },
 { name: 'Lilas', img: 'Taffeta/Lilac' },
            { name: 'Orange', img: 'Taffeta/Orange' },
            { name: 'Rose Claire', img: 'Taffeta/Pink' },

            { name: 'Couleur Rubis', img: 'Taffeta/Red' },
          
           
        
  
         
         
            { name: 'Bleu Saphir', img: 'Taffeta/Royal_Blue' },
       
            { name: 'Vert Lichen', img: 'Taffeta/Sage' },
     
        
              { name: 'Argent', img: 'Taffeta/Silver' },
           
     
            { name: 'Blanc', img: 'Taffeta/White' },
       
        
        
     
            { name: 'Indigo', img: 'Taffeta/Regency' },

            { name: 'Incarnadin', img: 'Taffeta/Watermelon' }
           
        ];
        return list;
    }
});