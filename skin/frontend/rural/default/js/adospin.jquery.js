// JavaScript Document
;(function($) {
    $.fn.adospin = function( settings ) {
        return this.each(function() {
            new  $.adoMooLeft( this, settings );
        });
    }
    $.adoMooLeft = function( obj, settings ){
        this.settings = {
            direction	    	: 'opacity',
            navInnerSelector	: 'ul',
            navSelector  		: 'li' ,
            navigatorEvent		: 'click',
            interval	  	 	: 4000,
            auto			    : true, // whether to automatic play the slideshow
            maxItemDisplay	 	: 3,
            startItem			: 0,
            navPosition			: 'vertical',
            navigatorHeight		: 100,
            navigatorWidth		: 310,
            duration			: 600,
            navItemsSelector    : '.ado-navigator li',
            navOuterSelector    : '.ado-navigator-outer' ,
            isPreloaded			: false,
            easing				: 'easeInOutQuad'
        }
        $.extend( this.settings, settings ||{} );
        this.nextNo         = null;
        this.previousNo     = null;
        this.slides =  $(obj).find( this.settings.navItemsSelector );
        if( this.settings.maxItemDisplay > this.slides.length ){
            this.settings.maxItemDisplay = this.slides.length;
        }

        this.currentNo      = isNaN(this.settings.startItem)||this.settings.startItem > this.slides.length?0:this.settings.startItem;
        this.navigatorOuter = $( obj ).find( this.settings.navOuterSelector );
        this.navigatorItems = $( obj ).find( this.settings.navItemsSelector ) ;
        this.navigatorInner = this.navigatorOuter.find( this.settings.navInnerSelector );

        //if( this.settings.navPosition == 'horizontal' ){
        //	this.navigatorInner.width( this.slides.length * this.settings.navigatorWidth );
        //	this.navigatorOuter.width( this.settings.maxItemDisplay * this.settings.navigatorWidth );
        //this.navigatorOuter.height(	this.settings.navigatorHeight );

        //} else {
        //	this.navigatorInner.height( this.slides.length * this.settings.navigatorHeight );
        //	this.navigatorOuter.height( this.settings.maxItemDisplay * this.settings.navigatorHeight );
        //this.navigatorOuter.width(	this.settings.navigatorWidth );
        //}
        this.navigratorStep = this.__getPositionMode( this.settings.navPosition );
        this.directionMode = this.__getDirectionMode();
        this.onComplete();
    }
    $.adoMooLeft.fn =  $.adoMooLeft.prototype;
    $.adoMooLeft.fn.extend =  $.adoMooLeft.extend = $.extend;
    $.adoMooLeft.fn.extend({
        startUp:function( obj ) {
            seft = this;
            this.navigratorStep = this.__getPositionMode( this.settings.navPosition );

            this.registerItemControl(this.navigatorItems,this);

            this.setNavActive(this.currentNo);

            if( this.settings.buttons && typeof (this.settings.buttons) == "object" ){
                this.registerButtonsControl( 'click', this.settings.buttons, this );
            }

            if( this.settings.auto ){
                this.play( this.settings.interval,'next', true );
                $(this).on('mouseover',function () {
                    this.isRun = true;
                    this.stop();
                });
                $(this).on('mouseout',function () {
                    this.play( this.settings.interval,'next', true );
                })
            }

            return this;
        },
        onComplete:function(){
            //setTimeout( function(){ $('.preload').fadeOut( 900 ); }, 400 );
            this.startUp();
        },

        navivationAnimate:function( currentIndex ) {
            if (currentIndex <= this.settings.startItem
                || currentIndex - this.settings.startItem >= this.settings.maxItemDisplay-1) {
                this.settings.startItem = currentIndex - this.settings.maxItemDisplay+2;
                if (this.settings.startItem < 0) this.settings.startItem = 0;
                if (this.settings.startItem >this.slides.length-this.settings.maxItemDisplay) {
                    this.settings.startItem = this.slides.length-this.settings.maxItemDisplay;
                }
            }
            this.navigatorInner.stop().animate( eval('({'+this.navigratorStep[0]+':-'+this.settings.startItem*this.navigratorStep[1]+'})'),
                {duration:500, easing:'easeInOutQuad'} );
        },
        setNavActive:function( index, item ){
            if( (this.navigatorItems) ){
                this.navigatorItems.removeClass( 'active' );
                $(this.navigatorItems.get(index)).addClass( 'active' );
                console.log(index);
                console.log(this.currentNo);
                this.navivationAnimate( this.currentNo );
            }
        },
        /*__getPositionMode:function( position ){
            if(	position  == 'horizontal' ){
                return ['left', this.settings.navigatorWidth];
            }
            return ['top', this.settings.navigatorHeight];
        },*/
        __getPositionMode:function( position ){
            var _position = this.__getNavPosition();
            if(_position){
                return ['top', this.settings.navigatorHeight];
            }else{
                return ['left', this.settings.navigatorWidth];
            }
            if(	position  == 'horizontal' ){
                return ['top', this.settings.navigatorHeight];
            }
            return ['left', this.settings.navigatorWidth];

        },
        __getNavPosition:function(){
              var _width = $(this.settings.navOuterSelector).width();
              var _height = $(this.settings.navOuterSelector).height();
              return (_width<_height);
        },
        /*__getDirectionMode:function(){
            switch( this.settings.direction ){
                case 'opacity': this.maxSize=0; return ['opacity','opacity'];
                default: this.maxSize=this.maxWidth; return ['left','width'];
            }
        },*/
        __getDirectionMode:function(){
            switch( this.settings.direction ){
                case 'opacity': this.maxSize=this.maxWidth; return ['left','width'];
                default: this.maxSize=0; return ['opacity','opacity'];
            }
        },

        registerButtonsControl:function( eventHandler, objects, self ){
            for( var action in objects ){
                switch (action.toString() ){
                    case 'next':
                        objects[action].click( function() { self.next( true);
                        } );
                        break;
                    case 'previous':
                        objects[action].click( function() {
                            self.previous( true);
                        } );
                        break;
                }
            }
            return this;
        },

        registerItemControl:function (objects, self) {
            $(objects).each( function(index, item ){
                $(item).click(function(){
                    // seft.jumping( index, true );

                    console.log( index );
                    self.currentNo = index;
                    self.setNavActive( index, item );
                    console.log( self.currentNo );
                });
            });
        },

        onProcessing:function( manual, start, end ){
            this.previousNo = this.currentNo + (this.currentNo>0 ? -1 : this.slides.length-1);
            this.nextNo 	= this.currentNo + (this.currentNo < this.slides.length-1 ? 1 : 1- this.slides.length);
            this.setCloudZoom();
            this.setNavActive(this.currentNo);
            return this;
        },

        setCloudZoom:function(){
            var  _index = this.currentNo;
            var _item = $(this.navigatorItems.get(_index));
            if(_item && _item.length>0){
                try{
                    console.log(this.currentNo);
                    $(_item).children('a').trigger('click');
                }catch(e){
                }
            }
        },

        finishFx:function( manual ){
            if( manual ) this.stop();
            if( manual && this.settings.auto ){
                this.play( this.settings.interval,'next', true );
            }
            this.setNavActive( this.currentNo );
        },
        getObjectDirection:function( start, end ){
            return eval("({'"+this.directionMode[0]+"':-"+(this.currentNo*start)+"})");
        },

        fxStart:function( index, obj, currentObj ){
            if( this.settings.direction == 'opacity' ) {
                $(this.slides).stop().animate({opacity:1}, {duration: this.settings.duration, easing:this.settings.easing} );
                $(this.slides).eq(index).stop().animate( {opacity:1}, {duration: this.settings.duration, easing:this.settings.easing} );
            }else {
                this.slides.stop().animate( obj, {duration: this.settings.duration, easing:this.settings.easing} );
            }
            return this;
        },
        jumping:function( no, manual ){
            this.stop();
            if( this.currentNo == no ) return;
            var obj = eval("({'"+this.directionMode[0]+"':-"+(this.maxSize*no)+"})");
            this.onProcessing( null, manual, 0, this.maxSize )
                .fxStart( no, obj, this )
                .finishFx( manual );
            this.currentNo  = no;
        },
        next:function( manual , item){
            this.currentNo += (this.currentNo < this.slides.length-1) ? 1 : (1 - this.slides.length);
            this.onProcessing( item, manual, 0, this.maxSize )
                .fxStart( this.currentNo, this.getObjectDirection(this.maxSize ), this )
                .finishFx( manual );
        },
        previous:function( manual, item ){
            this.currentNo += this.currentNo > 0 ? -1 : this.slides.length - 1;
            this.onProcessing( item, manual )
                .fxStart( this.currentNo, this.getObjectDirection(this.maxSize ), this )
                .finishFx( manual	);
        },
        play:function( delay, direction, wait ){
            this.stop();
            if(!wait){ this[direction](false); }
            var self  = this;
            this.isRun = setTimeout(function() { self[direction](true); }, delay);
        },
        stop:function(){
            if (this.isRun == null) return;
            clearTimeout(this.isRun);
            this.isRun = null;
        }
    });
})(window.jQuery || window.Zepto);

jQuery(function($){
    var buttons = { previous:$('.ado-previous') ,
        next:$('.ado-next') };

    var objmoo = $('#slidecontent45').adospin({
        interval : 4000,
        direction		: 'opacity',
        easing			: 'easeOutBounce',//easeInOutExpo   easeOutBounce
        duration		: 96,
        startItem      : (typeof currentNo !== 'undefined')?currentNo:0,
        navPosition	: 'horizontal',
        auto		 	: false,
        maxItemDisplay  : 5,
        navigatorHeight : 96,
        navigatorWidth  : 74,
        mainWidth		: 74,
        buttons		: buttons});

    $('#header-container').dFixed('.main-container');
});

;(function($) {
    $.fn.dFixed = function(ele) {
        self = this;
        if($(self).length<1)return true;
        var offset_top=$(self).offset().top;
        var offset_left=$(self).offset().left;
        var relative_left=$(self).position().left;
        var limitTop=$(ele).offset().top + $(ele).outerHeight()- $(self).outerHeight();
        $(window).scroll(function(){
            var top=$(window).scrollTop();
            if($(window).scrollTop() > offset_top){
                $(self).css({'position':'fixed','top':'0px','left':offset_left+'px','zIndex':'100000'});
            }else{ //如果滚动的高度不大于 moveObj就不动
                $(self).removeAttr('style');
            }
            if($(window).scrollTop()> limitTop && limitTop>=0 ){ //限定最大的scroll高度
                $(self).css({'position':'absolute','top':limitTop-offset_top+'px','left':relative_left+'px'});
            }
        })
    };
})(window.jQuery || window.Zepto);

