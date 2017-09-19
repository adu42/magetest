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
            this.navigatorItems.each( function(index, item ){
                $(item).click( function(){
                    seft.jumping( index, true );
                    seft.setNavActive( index, item );
                } );
                //$(item).css( {'height': seft.settings.navigatorHeight, 'width':  seft.settings.navigatorWidth} );
            })

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
            if(	position  == 'horizontal' ){
                return ['top', this.settings.navigatorHeight];
            }
            return ['left', this.settings.navigatorWidth];

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
        onProcessing:function( manual, start, end ){
            this.previousNo = this.currentNo + (this.currentNo>0 ? -1 : this.slides.length-1);
            this.nextNo 	= this.currentNo + (this.currentNo < this.slides.length-1 ? 1 : 1- this.slides.length);
            this.setCloudZoom();
            return this;
        },

        setCloudZoom:function(){
            var  _index = this.currentNo;
            var _item = $(this.navigatorItems.get(_index));
            if(_item && _item.length>0){
                try{
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
    })
})(window.jQuery || window.Zepto);

jQuery(function($){
    var buttons = { previous:$('.ado-previous') ,
        next:$('.ado-next') };

    var objmoo = $('#slidecontent45').adospin({
        interval : 4000,
        direction		: 'opacity',
        easing			: 'easeOutBounce',//easeInOutExpo   easeOutBounce
        duration		: 96,
        startItem      : currentNo?currentNo:0,
        navPosition	: 'horizontal',
        auto		 	: false,
        maxItemDisplay  : 5,
        navigatorHeight : 96,
        navigatorWidth  : 74,
        mainWidth		: 74,
        buttons		: buttons});
});
