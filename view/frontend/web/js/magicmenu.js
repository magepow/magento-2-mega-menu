require(['jquery', 'magiccart/easing'], function($, easing){
   
    /**
     * Magepow 
     * @category    Magepow 
     * @copyright   Copyright (c) 2014 Magepow (https://www.magepow.com) 
     * @license     https://www.magepow.com/license-agreement.html
     * @Author: DOng NGuyen<nguyen@magepow.com>
     * @@Create Date: 2014-04-25 13:16:48
     * @@Modify Date: 2020-07-25 09:16:29
     * @@Function:
     */

    
    !(function($){"use strict";$.fn.magicaccordion=function(options){var defaults={accordion:true,mouseType:false,speed:300,closedSign:'collapse',openedSign:'expand',openedActive:true,};var opts=$.extend(defaults,options);var $this=$(this);$this.find("li").each(function(){if($(this).find("ul").size()!=0){$(this).find("ul").hide();$(this).find("a:first").after("<span class='"+opts.closedSign+"'>"+opts.closedSign+"</span>");if($(this).find("a:first").attr('href')=="#"){$(this).find("a:first").click(function(){return false})}}});if(opts.openedActive){$this.find("li.active").each(function(){$(this).parents("ul").slideDown(opts.speed,opts.easing);$(this).parents("ul").parent("li").find("a:first").next().html(opts.openedSign).removeClass(opts.closedSign).addClass(opts.openedSign);$(this).find("ul:first").slideDown(opts.speed,opts.easing);$(this).find("a:first").next().html(opts.openedSign).removeClass(opts.closedSign).addClass(opts.openedSign)})}if(opts.mouseType){$this.find("li a").mouseenter(function(){if($(this).parent().find("ul").size()!=0){if(opts.accordion){if(!$(this).parent().find("ul").is(':visible')){var parents=$(this).parent().parents("ul");var visible=$this.find("ul:visible");visible.each(function(visibleIndex){var close=true;parents.each(function(parentIndex){if(parents[parentIndex]==visible[visibleIndex]){close=false;return false}});if(close){if($(this).parent().find("ul")!=visible[visibleIndex]){$(visible[visibleIndex]).slideUp(opts.speed,function(){$(this).parent("li").find("a:first").next().html(opts.closedSign).addClass(opts.closedSign)})}}})}}if($(this).parent().find("ul:first").is(":visible")){$(this).parent().find("ul:first").slideUp(opts.speed,function(){$(this).parent("li").find("a:first").next().delay(opts.speed+1000).html(opts.closedSign).removeClass(opts.openedSign).addClass(opts.closedSign)})}else{$(this).parent().find("ul:first").slideDown(opts.speed,function(){$(this).parent("li").find("a:first").next().delay(opts.speed+1000).html(opts.openedSign).removeClass(opts.closedSign).addClass(opts.openedSign)})}}})}else{$this.find("li span").click(function(){if($(this).parent().find("ul").size()!=0){if(opts.accordion){if(!$(this).parent().find("ul").is(':visible')){var parents=$(this).parent().parents("ul");var visible=$this.find("ul:visible");visible.each(function(visibleIndex){var close=true;parents.each(function(parentIndex){if(parents[parentIndex]==visible[visibleIndex]){close=false;return false}});if(close){if($(this).parent().find("ul")!=visible[visibleIndex]){$(visible[visibleIndex]).slideUp(opts.speed,function(){$(this).parent("li").find("a:first").next().html(opts.closedSign).addClass(opts.closedSign)})}}})}}if($(this).parent().find("ul:first").is(":visible")){$(this).parent().find("ul:first").slideUp(opts.speed,opts.easing,function(){$(this).parent("li").find("a:first").next().delay(opts.speed+1000).html(opts.closedSign).removeClass(opts.openedSign).addClass(opts.closedSign)})}else{$(this).parent().find("ul:first").slideDown(opts.speed,opts.easing,function(){$(this).parent("li").find("a:first").next().delay(opts.speed+1000).html(opts.openedSign).removeClass(opts.closedSign).addClass(opts.openedSign)})}}})}var catplus=$this.find('.nav-accordion >.level0:hidden').not('.all-cat');if(catplus.length)$this.find('.all-cat').show().click(function(event){$(this).children().toggle();catplus.slideToggle('slow')});else $this.find('.all-cat').hide()}})(jQuery);
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
                        topmenu.find('.vmega .category-item').on('hover mouseenter', function() {
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
                        $(window).on("magicmenu:refresh", function( event ) {
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
                        });

                        $(window).resize(function(){ $(this).trigger('magicmenu:refresh')});
                        
                        methods.taphover(topmenu.add(vmenu));
                    });
                },

                taphover: function(el){
                    var categories = el.find('.category-item');
                    categories.on('touchstart', function (e) {
                        'use strict'; //satisfy code inspectors
                        var link = $(this); //preselect the link
                        link.trigger('mouseenter');
                        if (link.hasClass('over')) {
                            return true;
                        } else {
                            link.addClass('over');
                            categories.not(this).removeClass('over');
                            link.parents('.category-item').addClass('over');
                            e.preventDefault();
                            return false; //extra, and to make sure the function has consistent return points
                        }
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
                        $(this).trigger('magicmenu:refresh');
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
                    var menuBoxMax  = $('body');
                    if(!fullWidth){
                        var maxWidth = 0;
                        $('.container').each(function(){
                                var width = parseInt($(this).width());
                                if (width > maxWidth) {
                                    maxWidth    = width;
                                    menuBoxMax  = $(this);
                                }
                        });
                    }
                    var maxW        = menuBoxMax.width();
                    var isRTL       = $('body').hasClass('rtl');
                    var dir         = isRTL ? 'right' : 'left';
                    $navtop.on('hover mouseenter', function(){
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
                            var offsetMega          = $item.offset();
                            var offsetMenuBox       = menuBox.offset();
                            var offsetmenuBoxMax    = menuBoxMax.offset();
                            if(isRTL){
                                var wWidth                  = $(window).width();
                                var offsetMegaRight         = wWidth - (offsetMega.left         + $item.outerWidth());
                                var offsetMenuBoxRight      = wWidth - (offsetMenuBox.left      + menuBox.outerWidth());
                                var offsetmenuBoxMaxRight   = wWidth - (offsetmenuBoxMax.left   + menuBoxMax.outerWidth());
                                var itemSpace               = offsetMegaRight                   - offsetMenuBoxRight;
                                var xMaxOffset              = offsetmenuBoxMaxRight             + maxW;
                                var xItemOffset             = offsetMegaRight                   + topMega.outerWidth();     
                            } else {
                                var itemSpace               = offsetMega.left                   - offsetMenuBox.left;
                                var xMaxOffset              = offsetmenuBoxMax.left             + maxW;
                                var xItemOffset             = offsetMega.left                   + topMega.outerWidth();                                
                            }
                            var xSpace                      = xItemOffset - xMaxOffset;
                            var space                       = itemSpace   - xSpace
                            if(space < itemSpace){
                                topMega.css(dir, space);
                            }else {
                                /* Fix error sticky menu position */
                                topMega.css(dir, 'auto');
                            }
                        }
                    })
                },

                vertical: function ($navtop, fullWidth, init)  {
                    if(init) methods.initMenu($navtop, fullWidth);
                    var menuBox = $('.container');
                    if(!menuBox.length) menuBox  = $('body');
                    var maxW    = menuBox.width();
                    $navtop.on('hover mouseenter', function(){
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
                        var wMageMax        = maxW - (topMega.outerWidth(true) - topMega.width());
                        if(fullWidth || menuBox.is('body')){
                            var offsetMega      = $item.offset();
                            var xSpace          = offsetMega.left;
                            wMageMax            = wMageMax - xSpace - $item.width();
                        }
                        if(wMega > wMageMax) wMega = Math.floor(wMageMax / wChild)*wChild;
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
