/**
 * 客户端存储
 * by@ado
 * 114458573@qq.com
 * job 1: auto login
 * job 2: visited history sku sort by time desc
 */
(function (window, document) {
    "use strict";
    var userData, attr, attributes;
    if (!window.localStorage && (userData = document.body) && userData.addBehavior) {
        if (userData.addBehavior("#default#userdata")) {
            userData.load((attr = "localStorage"));
            attributes = userData.XMLDocument.documentElement.attributes;
            window.localStorage = {
                "length": attributes.length,
                "key": function (idx) {
                    return (idx >= this.length) ? null : attributes[idx].name;
                },
                "getItem": function (key) {
                    return userData.getAttribute(key);
                },
                "setItem": function (key, value) {
                    userData.setAttribute(key, value);
                    userData.save(attr);
                    this.length += ((userData.getAttribute(key) === null) ? 1 : 0);
                },
                "removeItem": function (key) {
                    if (userData.getAttribute(key) !== null) {
                        userData.removeAttribute(key);
                        userData.save(attr);
                        this.length = Math.max(0, this.length - 1);
                    }
                },
                "clear": function () {
                    while (this.length) {
                        userData.removeAttribute(attributes[--this.length].name);
                    }
                    userData.save(attr);
                }
            };
        }
    }
})(this, this.document);

if(jQuery){
    var env = 'test';
    var $j = jQuery.noConflict();
    var sku_id = 'product'; // hidden field
    var save_number = 10;  //只保存10个url
    var faceback = '/catalog/viewed/visit';
    var _cookie_name = 'last_url';
    var sendbacksec = 200;
    var filter_key = 'sku'; //有sku的就保留，没有的如分类页，首页，就不需要了
    if(!$j.cookie){
        (function (factory) {
            if (typeof define === 'function' && define.amd) {
                define(['jquery'], factory);
            } else if (typeof exports === 'object') {
                module.exports = factory(require('jquery'));
            } else {
                factory(jQuery);
            }
        }(function ($) {
            var pluses = /\+/g;
            var cookies_path     = '/';
            var cookies_domain   = '.'+location.host;
            function encode(s) {
                return config.raw ? s : encodeURIComponent(s);
            }
            function decode(s) {
                return config.raw ? s : decodeURIComponent(s);
            }
            function stringifyCookieValue(value) {
                return encode(config.json ? JSON.stringify(value) : String(value));
            }
            function parseCookieValue(s) {
                if (s.indexOf('"') === 0) {
                    s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
                }
                try {
                    s = decodeURIComponent(s.replace(pluses, ' '));
                    return config.json ? JSON.parse(s) : s;
                } catch(e) {}
            }
            function read(s, converter) {
                var value = config.raw ? s : parseCookieValue(s);
                return $.isFunction(converter) ? converter(value) : value;
            }
            var config = $.cookie = function (key, value, options) {
                if (arguments.length > 1 && !$.isFunction(value)) {
                    options = $.extend({}, config.defaults, options);
                    if (typeof options.expires === 'number') {
                        var days = options.expires, t = options.expires = new Date();
                        t.setMilliseconds(t.getMilliseconds() + days * 864e+5);
                    }
                    return (document.cookie = [
                        encode(key), '=', stringifyCookieValue(value),
                        options.expires ? '; expires=' + options.expires.toUTCString() : '',
                        options.path    ? '; path=' + options.path : '; path='+cookies_path,
                        options.domain  ? '; domain=' + options.domain : '; domain='+cookies_domain,
                        options.secure  ? '; secure' : ''
                    ].join(''));
                }
                var result = key ? undefined : {},
                    cookies = document.cookie ? document.cookie.split('; ') : [],
                    i = 0,
                    l = cookies.length;

                for (; i < l; i++) {
                    var parts = cookies[i].split('='),
                        name = decode(parts.shift()),
                        cookie = parts.join('=');
                    if (key === name) {
                        result = read(cookie, value);
                        break;
                    }
                    if (!key && (cookie = read(cookie)) !== undefined) {
                        result[name] = cookie;
                    }
                }
                return result;
            };
            config.defaults = {};
            $.removeCookie = function (key, options) {
                $.cookie(key, '', $.extend({}, options, { expires: -1 }));
                return !$.cookie(key);
            };
        }));
    }
    (function () {
        'use strict';
        var _slice = Array.prototype.slice;
        try {
            _slice.call(document.documentElement);
        } catch (e) {
            Array.prototype.slice = function (begin, end) {
                end = (typeof end !== 'undefined') ? end : this.length;
                if (Object.prototype.toString.call(this) === '[object Array]'){
                    return _slice.call(this, begin, end);
                }
                var i, cloned = [],
                    size, len = this.length;
                var start = begin || 0;
                start = (start >= 0) ? start: len + start;
                var upTo = (end) ? end : len;
                if (end < 0) {
                    upTo = len + end;
                }
                size = upTo - start;
                if (size > 0) {
                    cloned = new Array(size);
                    if (this.charAt) {
                        for (i = 0; i < size; i++) {
                            cloned[i] = this.charAt(start + i);
                        }
                    } else {
                        for (i = 0; i < size; i++) {
                            cloned[i] = this[start + i];
                        }
                    }
                }
                return cloned;
            };
        }
    }());


    var second = 0; window.setInterval(function () { second ++; _onbeforeunload(); }, 1000);
// var history_visit = $j.cookie(_cookie_name);
    var current_visit = _clear_url(location.href);
    var current_refer = _clear_url(getReferrer());
    var new_visit = false;
    var _cookie_name_time = 'last_time';
    var visit_begin = $j.cookie(_cookie_name_time);
    if(!visit_begin || visit_begin <= 0){
        visit_begin = (new Date()).getTime()/1000;
        $j.cookie(_cookie_name_time, visit_begin ,{expires:1});
    }

    var _onbeforeunload = function() {
        if(second<3 && env != 'test')return true;  //3秒内的访问都丢弃，作用不大
        new_visit = !calcStat();
        if(new_visit)newStat();
        splitStat();  //只保留几条数据时间存储的数据
    };

    /**
     * Clean up the url anchor
     * @param url
     * @returns {string}
     * @private
     */
    function _clear_url(url) {
        if(!url)return '';
        var _urls = url.split('#');
        return _urls[0].replace('||','##');
    }

    function _reset_url(url) {
        return url.replace('##','||');
    }

    function getStat() {
        var stat = localStorage.getItem(_cookie_name)?localStorage.getItem(_cookie_name) : '[{}]';
        return JSON.parse(stat); // eval('(' + stat + ')');
    }

    function newStat() {
        stat = getStat();
        var data = {
            'url': current_visit,
            'time': second,
            'refer': current_refer
        };
        if (filter_key === 'sku') {
            if ($j('#' + sku_id) && $j('#' + sku_id).length > 0) {
                data.sku = $j('#' + sku_id).val().trim();
                stat.push(data);
                saveStat(stat);
            }
        }else{
            stat.push(data);
            saveStat(stat);
        }
    }

    function calcStat() {
        var stat = getStat();
        var newStat = [];
        var _find = false;
        if(stat && stat.length>0) {
            $j.each(stat, function (key, item) {
                if (item && item.url === current_visit) { item.time += second; _find = true;}
                if(item && item.url)newStat.push(item);
            });
        }
        if(newStat.length>0){
            saveStat(newStat);
        }
        return _find;
    }

    function saveStat(Stat) {
        stat = JSON.stringify(Stat);
        localStorage.setItem(_cookie_name, stat);
    }

    function splitStat() {
        stat = getStat();
        var sortByTime =function (a, b){
            if(!a || !a.time) return 1;
            return b.time - a.time;
        }
        if (stat && (stat.length > save_number)){
            stat.sort(sortByTime);
            stat = stat.slice(0,save_number);
            saveStat(stat);
        }
    }

    function facebackStat() {
        var now_passed = (new Date()).getTime()/1000;
        if(now_passed - visit_begin > sendbacksec){
            $j.cookie(_cookie_name_time, now_passed ,{expires:1});
            var stat = getStat();
            if(faceback)$j.post(faceback,{'data':JSON.stringify(stat)} ,function (data) {});
        }
    }
    facebackStat();

    function getReferrer() {
        var referrer = '';
        try {
            referrer = window.top.document.referrer;
        } catch(e) {
            if(window.parent) {
                try {
                    referrer = window.parent.document.referrer;
                } catch(e2) {
                    referrer = '';
                }
            }
        }
        if(referrer === '') {
            referrer = document.referrer;
        }
        return referrer;
    }
    /**
     * auto login
     */
    (function ($) {
        var url = '/guest';
        var w = window.screen.width,h = window.screen.height,_guest_token = 'guest_token',_minLenght = 12;

        var guest = $j.cookie(_guest_token);
        var token = window.localStorage.getItem(_guest_token);
        if(guest && guest.length>_minLenght){
            window.localStorage.setItem(_guest_token,guest);
            $j.cookie(_guest_token,1);
            guest = 1;
        }
        if(!guest || guest!=1){
            $.get(url,{w:w, h:h, token:token, r: Math.random()}, function (data){ });
        }
    })(jQuery);
}