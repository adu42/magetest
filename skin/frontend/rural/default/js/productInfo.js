var QuickView = Class.create();
QuickView.prototype = {
    settings: {
        'loadingMessage': 'Please wait ...'
    },
    
    initialize: function(selector, settings)
    {
        Object.extend(this.settings, settings);
        this.createWindow();
        var that = this;
        $$(selector).each(function(el, index){
            el.observe('click', that.loadInfo.bind(that));
        })
    },
    
    createLoader: function()
    {
        var loader = new Element('div', {id: 'ajax-preloader'});
        loader.innerHTML = "<p class='loading'>"+this.settings.loadingMessage+"</p>";
        document.body.appendChild(loader);
        $('ajax-preloader').setStyle({
           // position: 'absolute',
           // top:  document.viewport.getScrollOffsets().top + 200 + 'px',
           // left:  document.body.clientWidth/2 - 75 + 'px'
        });
    },
    
    destroyLoader: function()
    {
        $('ajax-preloader').remove();
    },
    
    showButton: function(e)
    {
        el = this;
        while (el.tagName != 'DIV') {
            el = el.up();
        }
        if($(el).getElementsBySelector('.ajax').length>0){
            $(el).getElementsBySelector('.ajax')[0].setStyle({
                display: 'block','z-index': 11
            });
        }
    },
    
    hideButton: function(e)
    {
        el = this;
        while (el.tagName != 'DIV') {
            el = el.up();
        }
        if($(el).getElementsBySelector('.ajax').length>0) {
            $(el).getElementsBySelector('.ajax')[0].setStyle({
                display: 'none', 'z-index': 0
            });
        }
    },
    
    createWindow: function()
    {
        var qWindow = new Element('div', {id: 'quick-window'});
        qWindow.innerHTML = '<div id="quickview-header"><a href="javascript:void(0)" id="quickview-close">close</a></div><div class="quick-view-content"></div>';
        document.body.appendChild(qWindow);
        $('quickview-close').observe('click', this.hideWindow.bind(this));
        $('quick-window').observe('click', this.hideWindow.bind(this));
    },
    
    showWindow: function()
    {
        $('quick-window').setStyle({
            'position': 'fixed',
            'z-index': 99999,
            'top':0,
            'left':0,
            'bottom': 0,
            'right':0,
            'display': 'block'
        });
        $$('.quick-view-content')[0].setStyle({
            'display': 'block',
            'z-index': 99999,
            'position': 'relative',
            'margin': '50px auto',
            'width': '90%',
            'max-width': '1040px',
            'background-color': '#fff',
            'padding': '28px 0 35px 30px',
            'transition': 'all ease .5s',
            'overflow': 'auto'
        });
        $$('.quick-view-content')[0].observe('click', this.stopHideWindowEvent.bind(this));
    },
    
    setContent: function(content)
    {
        $$('.quick-view-content')[0].update(content);
    },
    
    clearContent: function()
    {
        $$('.quick-view-content')[0].replace('<div class="quick-view-content"></div>');
    },
    
    hideWindow: function()
    {
        this.clearContent();
        $('quick-window').hide();
    },
    stopHideWindowEvent: function (e) {
      e.stopPropagation();
    },
    loadInfo: function(e)
    {
         e.stop();
        var that = this;
        this.createLoader();
        var id = e.element().readAttribute('data-id');
        if(!id)return;
        var url = '/catalog/category/quick/id/'+id;
        new Ajax.Request(url, {
            onComplete: function(response) {
                that.clearContent();
                that.setContent(response.responseText);
                that.destroyLoader();
                that.showWindow();
            }
        }); 
    }
}

Event.observe(window, 'load', function() {
    new QuickView('.ajax',{});
});