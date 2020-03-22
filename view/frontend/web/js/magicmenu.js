require(['jquery', 'magiccart/easing'], function($, easing){
   
    /**
     * Magepow 
     * @category    Magepow 
     * @copyright   Copyright (c) 2014 Magepow (https://www.magepow.com) 
     * @license     https://www.magepow.com/license-agreement.html
     * @Author: DOng NGuyen<nguyen@magepow.com>
     * @@Create Date: 2014-04-25 13:16:48
     * @@Modify Date: 2020-03-22 09:16:29
     * @@Function:
     */

    !function(l){"use strict";l.fn.magicaccordion=function(i){var d=l.extend({accordion:!0,mouseType:!1,speed:300,closedSign:"collapse",openedSign:"expand",openedActive:!0},i),e=l(this);e.find("li").each(function(){var i=l(this).find("ul");0!=i&&(i.hide(),l(this).find("a:first").after("<span class='"+d.closedSign+"'>"+d.closedSign+"</span>"),"#"==l(this).find("a:first").attr("href")&&l(this).find("a:first").click(function(){return!1}))}),d.openedActive&&e.find("li.active").each(function(){l(this).parents("ul").slideDown(d.speed,d.easing),l(this).parents("ul").parent("li").find("a:first").next().html(d.openedSign).removeClass(d.closedSign).addClass(d.openedSign),l(this).find("ul:first").slideDown(d.speed,d.easing),l(this).find("a:first").next().html(d.openedSign).removeClass(d.closedSign).addClass(d.openedSign)}),d.mouseType?e.find("li a").mouseenter(function(){if(0!=l(this).parent().find("ul").size()){if(d.accordion&&!l(this).parent().find("ul").is(":visible")){var s=l(this).parent().parents("ul"),t=e.find("ul:visible");t.each(function(e){var n=!0;s.each(function(i){if(s[i]==t[e])return n=!1}),n&&l(this).parent().find("ul")!=t[e]&&l(t[e]).slideUp(d.speed,function(){l(this).parent("li").find("a:first").next().html(d.closedSign).addClass(d.closedSign)})})}l(this).parent().find("ul:first").is(":visible")?l(this).parent().find("ul:first").slideUp(d.speed,function(){l(this).parent("li").find("a:first").next().delay(d.speed+1e3).html(d.closedSign).removeClass(d.openedSign).addClass(d.closedSign)}):l(this).parent().find("ul:first").slideDown(d.speed,function(){l(this).parent("li").find("a:first").next().delay(d.speed+1e3).html(d.openedSign).removeClass(d.closedSign).addClass(d.openedSign)})}}):e.find("li span").click(function(){if(0!=l(this).parent().find("ul").size()){if(d.accordion&&!l(this).parent().find("ul").is(":visible")){var s=l(this).parent().parents("ul"),t=e.find("ul:visible");t.each(function(e){var n=!0;s.each(function(i){if(s[i]==t[e])return n=!1}),n&&l(this).parent().find("ul")!=t[e]&&l(t[e]).slideUp(d.speed,function(){l(this).parent("li").find("a:first").next().html(d.closedSign).addClass(d.closedSign)})})}l(this).parent().find("ul:first").is(":visible")?l(this).parent().find("ul:first").slideUp(d.speed,d.easing,function(){l(this).parent("li").find("a:first").next().delay(d.speed+1e3).html(d.closedSign).removeClass(d.openedSign).addClass(d.closedSign)}):l(this).parent().find("ul:first").slideDown(d.speed,d.easing,function(){l(this).parent("li").find("a:first").next().delay(d.speed+1e3).html(d.openedSign).removeClass(d.closedSign).addClass(d.openedSign)})}});var n=e.find(".nav-accordion >.level0:hidden").not(".all-cat");n.length?e.find(".all-cat").show().click(function(i){l(this).children().toggle(),n.slideToggle("slow")}):e.find(".all-cat").hide()}}(jQuery);
    (function ($) {
        "use strict";
        $.fn.magicmenu = function (options) {
            var defaults = {
                breakpoint : 991,
                horizontal : '.magicmenu',
                vertical   : '.vmagicmenu',
                sticky     : '.header-sticker',
            };

            var settings   = $.extend(defaults, options);
            var breakpoint = settings.breakpoint;
            var hSelector  = settings.horizontal;
            var vSelector  = settings.vertical;
            var sticky     = settings.sticky;

            var methods = {
                options: {
                    responsive: false,
                    expanded: false,
                    showDelay: 42,
                    hideDelay: 300,
                    delay: 0,
                    mediaBreakpoint: '(max-width: ' + breakpoint + 'px)'
                },
                init : function() {
                    methods.controls = {
                        toggleBtn: $('[data-action="toggle-nav"]'),
                        swipeArea: $('.nav-sections')
                    };
                    methods._listen();
                    return this.each(function() {
                        // Topmenu
                        var topmenu = $(hSelector);
                        var navDesktop = topmenu.find('.nav-desktop');
                        if(navDesktop.hasClass('sticker')) methods.sticky(topmenu);
                        /* Active menu top-vmega */
                        topmenu.find('.vmega .category-item').hover(function() {
                            $(this).siblings().removeClass('over');
                            $(this).addClass('over');
                        }, function() {
                            // $(this).removeClass('over');
                            // $(this).parent().children(":first").addClass('over');
                        });

                        var fullWidth  = navDesktop.data('fullwidth');
                        if( navDesktop.data('breakpoint') ) breakpoint = navDesktop.data('breakpoint');
                        var leveltop = topmenu.find('li.level0.hasChild, li.level0.home').not('.dropdown');
                        methods.horizontal(leveltop, fullWidth, true);

                        // Vertical Menu
                        var vmenu   = $(vSelector);
                        methods.toggleVertical(vmenu);
                        var vLeveltop = vmenu.find('li.level0.hasChild, li.level0.home').not('.dropdown');
                        methods.vertical(vLeveltop, fullWidth, true);
                        // Responsive
                        var body = $('body');
                        if ( breakpoint > $(window).width() ) body.addClass('nav-mobile-display');
                        $(window).resize(function(){
                            if ( breakpoint > $(window).width()){
                                body.addClass('nav-mobile-display');
                                $('.nav-mobile').show();
                                navDesktop.hide();
                            } else {
                                body.removeClass('nav-mobile-display');
                                $('.nav-mobile').hide();
                                navDesktop.show();
                                methods.horizontal(leveltop, fullWidth, false);
                                methods.vertical(vLeveltop, fullWidth, false);
                            }
                        })
                    });
                },

                sticky: function(topmenu){
                    var menuSticky  = $(sticky);
                    var menuHeight  = menuSticky.innerHeight();
                    var postionTop  = topmenu.offset().top;
                    var body        = $('body');
                    var heightItem  =  0;
                    var heightAIO   = 0
                    var vmagicmenu = topmenu.parent().find('.vmagicmenu.fixed-auto');
                    var menuAIO = vmagicmenu.find('.nav-desktop');
                    if(body.hasClass('cms-index-index') && menuAIO.length){
                        heightItem  = menuAIO.height();
                        vmagicmenu.hover(
                            function() { heightAIO = menuAIO.height() ; menuAIO.addClass('over').css({"overflow": "", "height": 'auto', "display": ''}); }, 
                            function() { menuAIO.removeClass('over').css({"overflow": "hidden", "height": heightAIO}); }
                        );
                    }
                    $('<div class="fixed-height-sticky" style="display:none;height: ' + menuHeight + 'px"></div>').insertBefore(menuSticky);
                    $(window).scroll(function () {
                        var postion = $(this).scrollTop();
                        if (postion > postionTop ){ /* not use = */
                            menuSticky.addClass('header-container-fixed').parent().find('.fixed-height-sticky').show();
                            if(heightItem && !menuAIO.hasClass('over')){
                                heightAIO = heightItem - (postion - postionTop) - menuHeight;
                                if(heightAIO > 0 )menuAIO.css({"height": heightAIO, "overflow": "hidden", "display": ''});
                                else{
                                    menuAIO.css({"height": 'auto', "display": 'none', "overflow": "" });
                                }
                            } else {
                                menuAIO.css({"height": 'auto', "display": '', "overflow": "" });
                            }
                        } else {
                            menuSticky.removeClass('header-container-fixed').parent().find('.fixed-height-sticky').hide();
                            menuAIO.css({"height": 'auto'});
                        }
                    });
                },

                initMenu: function($navtop, fullWidth){
                    $navtop.each(function(index, val) {
                        var $item     = $(this);
                        if(fullWidth) $item.find('.level-top-mega').addClass('parent-full-width').wrap( '<div class="full-width"></div>' );
                        var options   = $item.data('options');
                        var $catMega = $item.find('.cat-mega');
                        var $children = $catMega.find('.children');
                        var columns   = $children.length;
                        var wchil     = $children.outerWidth();
                        if(options){
                            var col     = parseInt(options.cat_col);
                            if(!isNaN(col)) columns = col;
                            var cat         = parseFloat(options.cat_proportion);
                            var left        = parseFloat(options.left_proportion);
                            var right       = parseFloat(options.right_proportion);
                            if(isNaN(left)) left = 0; if(isNaN(right)) right = 0;
                            var proportion  = cat + left + right;
                            var wCat        = Math.ceil(100*cat/proportion);
                            var wLeft       = Math.floor(100*left/proportion);
                            var wRight      = Math.floor(100*right/proportion);
                            // Init Responsive
                            $catMega.css("width", wCat + "%");
                            $item.find('.mega-block-left').css("width", wLeft + "%");
                            $item.find('.mega-block-right').css("width", wRight + "%");
                            $children.each(function(idx) { if(idx % columns ==0 && idx != 0) $(this).css("clear", "both"); });
                            $item.attr({'data-wcat': wCat, 'data-wleft': wLeft,'data-wright': wRight });
                        } 

                    });
                },

                horizontal: function ($navtop, fullWidth, init) {
                    if(init) methods.initMenu($navtop, fullWidth);
                    var menuBox     = $navtop.closest('.magicmenu');
                    var menuBoxMax  = fullWidth ? $('body'): $('.container');
                    var maxW        = menuBoxMax.width();
                    var float       = $('body').hasClass('rtl') ? 'right' : 'left';
                    $navtop.hover(function(){
                        var $item       = $(this);
                        var options     = $item.data('options');
                        var $children   = $item.find('.cat-mega .children');
                        var columns     = $children.length;
                        var wChild      = $children.outerWidth(true);
                        var wMega       = wChild*columns;
                        if(options){
                            var col     = parseInt(options.cat_col);
                            if(!isNaN(col)) wMega = wChild*col;
                            var wCat    = $item.data('wcat');
                            var wLeft   = Math.ceil($item.data('wleft')*wMega/wCat);
                            var wRight  = Math.ceil($item.data('wright')*wMega/wCat);
                            if( wLeft || wRight ) wMega = wMega + wLeft + wRight;
                        }
                        if(wMega > maxW) wMega = Math.floor(maxW / wChild)*wChild;
                        $item.find('.content-mega-horizontal').width(wMega);
                        var topMega     = $item.find('.level-top-mega');
                        if(topMega.length){
                            var offsetMenuBox        = menuBox.offset();
                            var offsetMega           = $item.offset();
                            var itemLeft             = offsetMega.left - offsetMenuBox.left;
                            var xLeft                = maxW - topMega.outerWidth(true);
                            var left                 = fullWidth ? xLeft - offsetMenuBox.left : xLeft;
                            if(xLeft < 0) left       = left/2;
                            if(left < itemLeft){
                                topMega.css(float,left);
                            }else {
                                /* Fix error sticky menu position */
                                topMega.css(float, 'auto');
                            }                   
                        }
                    })
                },

                vertical: function ($navtop, fullWidth, init)  {
                    if(init) methods.initMenu($navtop, fullWidth);
                    var menuBox = $('.container');
                    var maxW    = menuBox.width();
                    $navtop.hover(function(){
                        var $item       = $(this);
                        var options     = $item.data('options');
                        var $children   = $item.find('.cat-mega .children');
                        var columns     = $children.length;
                        var wChild      = $children.outerWidth(true);
                        var topMega     = $item.find('.level-top-mega');
                        var wMega       = wChild*columns;
                        if(options){
                            var col     = parseInt(options.cat_col);
                            if(!isNaN(col)) wMega = wChild*col;
                            var wCat    = $item.data('wcat');
                            var wLeft   = Math.ceil($item.data('wleft')*wMega/wCat);
                            var wRight  = Math.ceil($item.data('wright')*wMega/wCat);
                            if( wLeft || wRight ) wMega = wMega + wLeft + wRight;
                        }
                        var wMageMax        = maxW- (topMega.outerWidth(true) - topMega.width());
                        if(wMega > wMageMax) wMega = Math.floor(wMageMax / wChild)*wChild;
                        var postionMega     = $item.position();
                        topMega.css('top', postionMega.top);
                        $item.find('.content-mega-horizontal').width(wMega);
                    })
                },

                toggleVertical: function ($vmenu) {
                    $vmenu.find('.v-title').click(function() {
                        // $vmenu.find('.nav-desktop').parent().toggle();
                        $vmenu.find('.nav-desktop').height('').slideToggle(400);
                    });
                    var catplus = $vmenu.find('.nav-desktop > .level0:hidden').not('.all-cat');
                    // var catmore = $vmenu.find('.nav-desktop > .level0');
                    if(catplus.length) $vmenu.find('.all-cat').show().click(function(event) {$(this).children().toggle(); catplus.slideToggle('slow');});
                    // if(catplus.length) $vmenu.find('.all-cat').show().click(function(event) {$(this).children().toggle(); catmore.slideToggle('slow');});
                    else $vmenu.find('.all-cat').hide();
                },
                /**
                 * @private
                 */
                _listen: function () {
                    var controls = this.controls,
                        toggle = this.toggle;

                    controls.toggleBtn.off('click');
                    controls.toggleBtn.on('click', toggle.bind(this));
                    controls.swipeArea.off('swipeleft');
                    controls.swipeArea.on('swipeleft', toggle.bind(this));
                },

                /**
                 * Toggle.
                 */
                toggle: function () {
                    var html = $('html');

                    if (html.hasClass('nav-open')) {
                        html.removeClass('nav-open');
                        setTimeout(function () {
                            html.removeClass('nav-before-open');
                        }, this.options.hideDelay);
                    } else {
                        html.addClass('nav-before-open');
                        setTimeout(function () {
                            html.addClass('nav-open');
                        }, this.options.showDelay);
                    }
                }
            };

            if(methods[options]) { // $("#element").pluginName('methodName', 'arg1', 'arg2');
                return methods[options].apply(this, Array.prototype.slice.call(arguments, 1));
            } else if (typeof options === 'object' || !options) { // $("#element").pluginName({ option: 1, option:2 });
                return methods.init.apply(this);
            } else {
                $.error('Method "' + method + '" does not exist in timer plugin!');
            }
        }

    })(jQuery);

    $(document).ready(function($) {
        // For accordion
        $(".meanmenu-accordion").magicaccordion({
            accordion:true,
            speed: 400,
            closedSign: 'collapse',
            openedSign: 'expand',
            easing: 'easeInOutQuad'
        });
        $(".navigation").magicaccordion({
            accordion:true,
            speed: 400,
            closedSign: 'collapse',
            openedSign: 'expand',
            easing: 'easeInOutQuad',
            openedActive: false,
        });
        // End for Mobile
        $(document).magicmenu();
    });
});
