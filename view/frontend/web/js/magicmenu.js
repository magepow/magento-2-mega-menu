/**
 * Copyright © Magiccart. All rights reserved.
 * See LICENSE for license details.
 *
 * Magicmenu - Pure JavaScript (no jQuery) version.
 *  - class MagicAccordion : thay cho $.fn.magicaccordion
 *  - class Magicmenu      : thay cho $.fn.magicmenu
 *
 * Dùng:
 *   AMD / RequireJS : define(['Magiccart_Magicmenu/js/magicmenu'], function (Magicmenu) {...})
 *   CommonJS        : const Magicmenu = require('./magicmenu');
 *   Script thường   : window.Magicmenu
 *
 * File tự khởi tạo khi DOM ready (giống bản cũ). Có thể tạo thủ công:
 *   const menu = new Magicmenu({ breakpoint: 991 }).init();
 */
(function (root, factory) {
    if (typeof define === 'function' && define.amd) {
        define([], factory);
    } else if (typeof module === 'object' && module.exports) {
        module.exports = factory();
    } else {
        root.Magicmenu = factory();
    }
})(typeof self !== 'undefined' ? self : this, function () {
    'use strict';

    /* ------------------------------------------------------------------ *
     * DOM helpers (thay thế các hàm jQuery đã dùng)
     * ------------------------------------------------------------------ */

    const qs  = (selector, scope = document) => scope.querySelector(selector);
    const qsa = (selector, scope = document) => Array.from(scope.querySelectorAll(selector));

    const viewportWidth  = () => document.documentElement.clientWidth;
    const viewportHeight = () => document.documentElement.clientHeight;
    const documentHeight = () => {
        const b = document.body, d = document.documentElement;
        return Math.max(b.scrollHeight, d.scrollHeight, b.offsetHeight, d.offsetHeight, d.clientHeight);
    };

    /** Giống $.fn.data(): tự ép kiểu true/false/null/number/JSON */
    const parseData = (value) => {
        if (value === null || value === undefined) return undefined;
        if (value === 'true') return true;
        if (value === 'false') return false;
        if (value === 'null') return null;
        if (value !== '' && String(+value) === value) return +value;
        if (/^(?:\{[\s\S]*\}|\[[\s\S]*\])$/.test(value)) {
            try { return JSON.parse(value); } catch (e) { /* trả về chuỗi gốc */ }
        }
        return value;
    };
    const getData = (el, key) => (el ? parseData(el.getAttribute('data-' + key)) : undefined);

    const isVisible = (el) => !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length);

    const displayCache = {};
    const defaultDisplay = (tag) => {
        if (!displayCache[tag]) {
            const t = document.createElement(tag);
            document.body.appendChild(t);
            let d = getComputedStyle(t).display;
            t.remove();
            if (d === 'none') d = 'block';
            displayCache[tag] = d;
        }
        return displayCache[tag];
    };

    const show = (el) => {
        if (el.style.display === 'none') el.style.display = el._mmDisplay || '';
        if (getComputedStyle(el).display === 'none') el.style.display = defaultDisplay(el.tagName);
    };
    const hide = (el) => {
        if (el.style.display !== 'none') {
            el._mmDisplay = el.style.display;
            el.style.display = 'none';
        }
    };
    const toggle = (el) => (getComputedStyle(el).display === 'none' ? show(el) : hide(el));

    const PX_PROPS = new Set(['width', 'height', 'minHeight', 'left', 'right', 'top']);
    /** Giống $.fn.css(): tự thêm "px" cho số */
    const css = (els, props) => {
        (Array.isArray(els) ? els : [els]).forEach((el) => {
            if (!el) return;
            Object.keys(props).forEach((key) => {
                let v = props[key];
                if (typeof v === 'number' && PX_PROPS.has(key)) v += 'px';
                el.style[key] = v;
            });
        });
    };

    const extras = (el, horizontal) => {
        const cs = getComputedStyle(el);
        const sides = horizontal ? ['Left', 'Right'] : ['Top', 'Bottom'];
        return sides.reduce((sum, s) =>
            sum + (parseFloat(cs['padding' + s]) || 0) + (parseFloat(cs['border' + s + 'Width']) || 0), 0);
    };
    const width  = (el) => el.getBoundingClientRect().width  - extras(el, true);
    const height = (el) => el.getBoundingClientRect().height - extras(el, false);
    const outerWidth = (el, withMargin = false) => {
        let w = el.getBoundingClientRect().width;
        if (withMargin) {
            const cs = getComputedStyle(el);
            w += (parseFloat(cs.marginLeft) || 0) + (parseFloat(cs.marginRight) || 0);
        }
        return w;
    };
    const setWidth = (el, value) => {
        if (!isFinite(value)) return;
        const add = getComputedStyle(el).boxSizing === 'border-box' ? extras(el, true) : 0;
        el.style.width = (value + add) + 'px';
    };
    const offset = (el) => {
        if (!el.getClientRects().length) return { top: 0, left: 0 };
        const r = el.getBoundingClientRect();
        return { top: r.top + window.pageYOffset, left: r.left + window.pageXOffset };
    };

    const wrap = (el, className) => {
        const div = document.createElement('div');
        div.className = className;
        el.parentNode.insertBefore(div, el);
        div.appendChild(el);
    };
    const siblings = (el) => (el.parentElement ? Array.from(el.parentElement.children).filter((s) => s !== el) : []);
    const ancestors = (el, selector) => {
        const list = [];
        for (let p = el.parentElement; p; p = p.parentElement) {
            if (p.matches(selector)) list.push(p);
        }
        return list;
    };

    /* ---- slideUp / slideDown / slideToggle (Web Animations API) ------- */

    const SPEEDS = { slow: 600, fast: 200 };
    const toMs = (speed, fallback = 400) =>
        typeof speed === 'number' ? speed : (SPEEDS[speed] !== undefined ? SPEEDS[speed] : fallback);
    const SLIDE_PROPS = ['height', 'paddingTop', 'paddingBottom', 'marginTop', 'marginBottom'];

    const stopSlide = (el) => {
        const s = el._mmSlide;
        if (!s) return;
        el._mmSlide = null;
        s.anim.cancel();
        s.done();
    };

    const slide = (el, dir, speed, callback) => {
        const duration = toMs(speed);
        stopSlide(el);
        const visible = isVisible(el);

        const instant = () => {
            if (dir === 'down' && !visible) show(el);
            else if (dir === 'up' && visible) hide(el);
            if (callback) callback.call(el);
        };

        if ((dir === 'down' && visible) || (dir === 'up' && !visible)
            || typeof el.animate !== 'function' || !duration) {
            return instant();
        }

        if (dir === 'down') show(el);
        const cs   = getComputedStyle(el);
        const full = {};
        const zero = {};
        SLIDE_PROPS.forEach((p) => { full[p] = cs[p]; zero[p] = '0px'; });
        if (full.height === 'auto') return instant();

        const prevOverflow = el.style.overflow;
        el.style.overflow = 'hidden';
        const anim = el.animate(dir === 'down' ? [zero, full] : [full, zero],
            { duration, easing: 'ease', fill: 'forwards' });

        const done = () => {
            el.style.overflow = prevOverflow;
            if (dir === 'up') hide(el);
            if (callback) callback.call(el);
        };
        el._mmSlide = { anim, done };
        anim.onfinish = () => {
            if (!el._mmSlide || el._mmSlide.anim !== anim) return;
            el._mmSlide = null;
            done();
            anim.cancel();
        };
    };
    const slideToggle = (el, speed, callback) =>
        slide(el, isVisible(el) ? 'up' : 'down', speed, callback);

    /* ------------------------------------------------------------------ *
     * MagicAccordion
     * ------------------------------------------------------------------ */

    class MagicAccordion {
        /**
         * @param {HTMLElement} element
         * @param {Object} [options]
         */
        constructor(element, options = {}) {
            this.el   = element;
            this.opts = Object.assign({}, MagicAccordion.defaults, options);
            this.init();
        }

        init() {
            const self = this.el;
            const opts = this.opts;

            if (self.classList.contains('menu-init')) return this;
            self.classList.add('menu-init');

            qsa('li', self).forEach((li) => {
                const subs = qsa('ul', li);
                if (!subs.length) return;
                subs.forEach(hide);

                const link = qs('a', li);
                if (!link) return;

                const sign = document.createElement('span');
                sign.className   = opts.closedSign;
                sign.textContent = opts.closedSign;
                link.after(sign);

                if (link.getAttribute('href') === '#') {
                    link.addEventListener('click', (e) => e.preventDefault());
                }
            });

            if (opts.openedActive) this.openedActive();

            if (opts.mouseType) {
                qsa('li a', self).forEach((a) => a.addEventListener('mouseenter', () => this.menuAction(a)));
            } else {
                qsa('li span', self).forEach((s) => s.addEventListener('click', () => this.menuAction(s)));
            }

            this._initAllCat();
            return this;
        }

        _initAllCat() {
            const self    = this.el;
            const allCat  = qsa('.all-cat', self);
            const catplus = qsa('.nav-accordion > .level0', self)
                .filter((el) => !isVisible(el) && !el.matches('.all-cat'));

            if (catplus.length) {
                allCat.forEach((btn) => {
                    show(btn);
                    btn.addEventListener('click', () => {
                        Array.from(btn.children).forEach(toggle);
                        catplus.forEach((el) => slideToggle(el, 'slow'));
                    });
                });
            } else {
                allCat.forEach(hide);
            }
        }

        /** Đổi icon/text expand-collapse của li chứa submenu */
        _setSign(li, open) {
            if (!li || li.tagName !== 'LI') return;
            const link = qs('a', li);
            const sign = link && link.nextElementSibling;
            if (!sign) return;
            const { openedSign, closedSign } = this.opts;
            sign.textContent = open ? openedSign : closedSign;
            sign.classList.remove(open ? closedSign : openedSign);
            sign.classList.add(open ? openedSign : closedSign);
        }

        menuAction(item) {
            const { speed, accordion } = this.opts;
            const parent = item.parentElement;
            if (!parent) return;

            const subs = qsa('ul', parent);
            if (!subs.length) return;

            if (accordion && !subs.some(isVisible)) {
                const parents = ancestors(parent, 'ul');
                qsa('ul', this.el)
                    .filter(isVisible)
                    .forEach((ul) => {
                        if (parents.includes(ul)) return;
                        slide(ul, 'up', speed, () => this._setSign(ul.parentElement, false));
                    });
            }

            const first = subs[0];
            if (isVisible(first)) {
                slide(first, 'up', speed, () => this._setSign(first.parentElement, false));
            } else {
                slide(first, 'down', speed, () => this._setSign(first.parentElement, true));
            }
        }

        openedActive() {
            const { speed } = this.opts;
            qsa('li.active', this.el).forEach((li) => {
                const parents = ancestors(li, 'ul');
                parents.forEach((ul) => {
                    slide(ul, 'down', speed);
                    this._setSign(ul.parentElement, true);
                });

                const sub = qs('ul', li);
                if (sub) {
                    slide(sub, 'down', speed);
                    this._setSign(li, true);
                }
            });
        }
    }

    MagicAccordion.defaults = {
        accordion   : true,
        mouseType   : false,
        speed       : 300,
        closedSign  : 'collapse',
        openedSign  : 'expand',
        openedActive: false
    };

    /* ------------------------------------------------------------------ *
     * Magicmenu
     * ------------------------------------------------------------------ */

    class Magicmenu {
        /**
         * @param {Object} [options]
         * @param {number} [options.breakpoint=991]
         * @param {string} [options.horizontal='.magicmenu']
         * @param {string} [options.vertical='.vmagicmenu']
         * @param {string} [options.sticky='.header-sticker']
         */
        constructor(options = {}) {
            this.settings   = Object.assign({}, Magicmenu.defaults, options);
            this.breakpoint = this.settings.breakpoint;
            this.options    = {
                responsive     : false,
                expanded       : false,
                showDelay      : 42,
                hideDelay      : 300,
                delay          : 0,
                mediaBreakpoint: '(max-width: ' + this.breakpoint + 'px)'
            };
            this.controls     = { toggleBtn: [], swipeArea: [] };
            this._initialized = false;
            this._toggleHandler = () => this.toggle();
        }

        /* ------------------------------ init ------------------------------ */

        init() {
            if (this._initialized) return this;
            this._initialized = true;

            this.controls = {
                toggleBtn: qsa('[data-action="toggle-nav"],[data-action="toggle-topmenu"]'),
                swipeArea: qsa('.nav-sections,.topmenu-close')
            };
            this._listen();

            const html = document.documentElement;
            if (!html.classList.contains('magicmenu-init')) {
                html.classList.add('magicmenu-init');
                this.getAppVersion('Magiccart_Magicmenu', 'https://magepow.com/magento-2-mega-menu.html');
            }

            window.addEventListener('resize', () => this.refresh());

            const accordions = qsa('nav.navigation, .meanmenu-accordion');
            const megamenus  = qsa(this.settings.horizontal).concat(qsa(this.settings.vertical));

            this._observe(accordions, (el) => {
                this.active(el);
                const data = getData(el, 'menu-init');
                new MagicAccordion(el, data && typeof data === 'object' ? data : {});
            });
            this._observe(megamenus, (el) => this.megamenu(el));

            return this;
        }

        /** Lazy-load khi phần tử xuất hiện trong viewport */
        _observe(elements, callback) {
            if (!elements.length) return;
            if (!('IntersectionObserver' in window)) {
                elements.forEach(callback);
                return;
            }
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    callback(entry.target);
                    observer.unobserve(entry.target);
                });
            });
            elements.forEach((el) => observer.observe(el));
        }

        /** Phát sự kiện refresh (tương đương $(window).trigger('magicmenu:refresh')) */
        refresh() {
            window.dispatchEvent(new CustomEvent('magicmenu:refresh'));
        }

        /** Lắng nghe refresh, gộp theo frame để tránh chạy quá nhiều khi scroll */
        _onRefresh(fn) {
            let raf = 0;
            window.addEventListener('magicmenu:refresh', () => {
                if (raf) return;
                raf = requestAnimationFrame(() => { raf = 0; fn(); });
            });
        }

        /** Chỉ chạy fn một lần cho mỗi (element, key) */
        _once(el, key, fn) {
            const flag = '_mm_' + key;
            if (el[flag]) return;
            el[flag] = true;
            fn();
        }

        /* ---------------------------- toggle nav -------------------------- */

        _listen() {
            const { toggleBtn, swipeArea } = this.controls;
            // cùng một function reference => addEventListener tự loại trùng
            toggleBtn.forEach((btn) => btn.addEventListener('click', this._toggleHandler));
            swipeArea.forEach((area) => this._onSwipeLeft(area, this._toggleHandler));
        }

        /** Thay cho sự kiện "swipeleft" của jQuery Mobile */
        _onSwipeLeft(el, callback) {
            this._once(el, 'swipe', () => {
                let start = null;
                el.addEventListener('touchstart', (e) => {
                    const t = e.touches[0];
                    start = { x: t.clientX, y: t.clientY, time: Date.now() };
                }, { passive: true });
                el.addEventListener('touchend', (e) => {
                    if (!start) return;
                    const t  = e.changedTouches[0];
                    const dx = t.clientX - start.x;
                    const dy = t.clientY - start.y;
                    const dt = Date.now() - start.time;
                    start = null;
                    if (dt < 1000 && dx < -30 && Math.abs(dy) < 30) callback();
                }, { passive: true });
            });
        }

        toggle() {
            const html = document.documentElement;
            if (html.classList.contains('nav-open')) {
                html.classList.remove('nav-open');
                setTimeout(() => html.classList.remove('nav-before-open'), this.options.hideDelay);
            } else {
                html.classList.add('nav-before-open');
                setTimeout(() => html.classList.add('nav-open'), this.options.showDelay);
            }
        }

        /* ---------------------------- tap hover --------------------------- */

        taphover(menu) {
            const categories = qsa('.category-item.hasChild', menu);
            const events     = qs('.nav-desktop.click', menu) ? ['touchstart', 'click'] : ['touchstart'];

            categories.forEach((link) => {
                events.forEach((type) => {
                    link.addEventListener(type, (e) => {
                        link.dispatchEvent(new MouseEvent('mouseenter', { bubbles: true }));
                        if (link.classList.contains('over')) return;

                        link.classList.add('over');
                        categories.forEach((c) => { if (c !== link) c.classList.remove('over'); });
                        ancestors(link, '.category-item').forEach((p) => p.classList.add('over'));

                        e.preventDefault();
                        e.stopPropagation();
                    }, { passive: false });
                });
            });
        }

        /* ------------------------------ sticky ---------------------------- */

        sticky(topmenu) {
            if (documentHeight() <= viewportHeight()) return;

            const body       = document.body;
            const menuSticky = qsa(this.settings.sticky);
            const stickyH    = () => (menuSticky[0] ? menuSticky[0].clientHeight : 0);
            const menuHeight = stickyH();
            const postionTop = offset(topmenu).top + height(topmenu);

            let heightItem = 0;
            let heightAIO  = 0;
            const vmagicmenu = topmenu.parentElement ? qsa('.vmagicmenu.fixed-auto', topmenu.parentElement) : [];
            const menuAIO    = vmagicmenu.reduce((list, v) => list.concat(qsa('.nav-desktop', v)), []);

            if (body.classList.contains('cms-index-index') && menuAIO.length) {
                heightItem = height(menuAIO[0]);
                vmagicmenu.forEach((v) => {
                    v.addEventListener('mouseenter', () => {
                        heightAIO = height(menuAIO[0]);
                        menuAIO.forEach((el) => el.classList.add('over'));
                        css(menuAIO, { overflow: '', height: 'auto', display: '' });
                    });
                    v.addEventListener('mouseleave', () => {
                        menuAIO.forEach((el) => el.classList.remove('over'));
                        css(menuAIO, { overflow: 'hidden', height: heightAIO });
                    });
                });
            }

            const header = qsa('header.page-header');
            css(header, { minHeight: menuHeight });

            window.addEventListener('resize', () => {
                if (!menuSticky.some((el) => el.classList.contains('header-container-fixed'))) {
                    css(header, { minHeight: stickyH() });
                }
            });

            window.addEventListener('scroll', () => {
                const position = window.pageYOffset || document.documentElement.scrollTop;
                this.refresh();

                if (position > postionTop) { /* not use = */
                    menuSticky.forEach((el) => el.classList.add('header-container-fixed'));
                    if (heightItem && !menuAIO.some((el) => el.classList.contains('over'))) {
                        heightAIO = heightItem - (position - postionTop) - menuHeight;
                        if (heightAIO > 0) css(menuAIO, { height: heightAIO, overflow: 'hidden', display: '' });
                        else css(menuAIO, { height: 'auto', display: 'none', overflow: '' });
                    } else {
                        css(menuAIO, { height: 'auto', display: '', overflow: '' });
                    }
                } else {
                    menuSticky.forEach((el) => el.classList.remove('header-container-fixed'));
                    css(menuAIO, { height: 'auto' });
                }
            }, { passive: true });
        }

        /* ---------------------------- mega layout ------------------------- */

        initMenu(navtop, fullWidth, horizontal = true) {
            navtop.forEach((item) => {
                if (fullWidth) {
                    qsa('.level-top-mega', item).forEach((mega) => {
                        const parent = mega.parentElement;
                        if (Number(fullWidth) === 2 && horizontal) {
                            mega.classList.add('parent-full-width');
                            if (!parent.classList.contains('full-width')) wrap(mega, 'full-width');
                            setWidth(mega, width(document.body));
                        } else {
                            mega.classList.add('parent-auto-width');
                            if (!parent.classList.contains('auto-width')) wrap(mega, 'auto-width');
                        }
                    });
                }

                const options  = getData(item, 'options');
                const catMega  = qsa('.cat-mega', item);
                const children = catMega.reduce((list, c) => list.concat(qsa('.children', c)), []);
                let columns    = children.length;

                if (options && typeof options === 'object') {
                    const col = parseInt(options.cat_col, 10);
                    if (!isNaN(col)) columns = col;

                    const cat   = parseFloat(options.cat_proportion);
                    let left    = parseFloat(options.left_proportion);
                    let right   = parseFloat(options.right_proportion);
                    if (isNaN(left)) left = 0;
                    if (isNaN(right)) right = 0;

                    const proportion = cat + left + right;
                    const wCat   = Math.ceil(100 * cat / proportion);
                    const wLeft  = Math.floor(100 * left / proportion);
                    const wRight = Math.floor(100 * right / proportion);

                    // Init Responsive
                    catMega.forEach((el) => { el.style.width = wCat + '%'; });
                    qsa('.mega-block-left', item).forEach((el) => { el.style.width = wLeft + '%'; });
                    qsa('.mega-block-right', item).forEach((el) => { el.style.width = wRight + '%'; });
                    children.forEach((el, idx) => {
                        if (columns && idx % columns === 0 && idx !== 0) el.style.clear = 'both';
                    });

                    item.setAttribute('data-wcat', wCat);
                    item.setAttribute('data-wleft', wLeft);
                    item.setAttribute('data-wright', wRight);
                }
            });
        }

        /** Khung tối đa để mega menu không tràn ra ngoài */
        _boxMax(fullWidth) {
            let box = document.body;
            if (!fullWidth) {
                let maxWidth = 0;
                const container = qsa('.container');
                const boxed     = container.length ? container : qsa('.navigation');
                boxed.forEach((el) => {
                    const w = parseInt(width(el), 10);
                    if (w > maxWidth) {
                        maxWidth = w;
                        box = el;
                    }
                });
            }
            return box;
        }

        /** Tính độ rộng mega theo số cột + block trái/phải */
        _calcMega(item) {
            const options  = getData(item, 'options');
            const children = qsa('.cat-mega .children', item);
            const wChild   = children.length ? outerWidth(children[0], true) : NaN;
            let wMega      = wChild * children.length;

            if (options && typeof options === 'object') {
                const col = parseInt(options.cat_col, 10);
                if (!isNaN(col)) wMega = wChild * col;
                const wCat   = getData(item, 'wcat');
                const wLeft  = Math.ceil(getData(item, 'wleft')  * wMega / wCat);
                const wRight = Math.ceil(getData(item, 'wright') * wMega / wCat);
                if (wLeft || wRight) wMega = wMega + wLeft + wRight;
            }
            return { wChild, wMega };
        }

        horizontal(navtop, fullWidth, init) {
            if (init) this.initMenu(navtop, fullWidth);
            navtop.forEach((item) => {
                this._once(item, 'horizontal', () => {
                    item.addEventListener('mouseenter', () => this._hoverHorizontal(item, fullWidth));
                });
            });
        }

        _hoverHorizontal(item, fullWidth) {
            const body    = document.body;
            const menuBox = item.closest(this.settings.horizontal);
            const boxMax  = this._boxMax(fullWidth);
            const maxW    = width(boxMax);
            const isRTL   = body.classList.contains('rtl');
            const dir     = isRTL ? 'right' : 'left';

            const calc  = this._calcMega(item);
            const wChild = calc.wChild;
            let wMega   = calc.wMega;
            if (wMega > maxW) wMega = Math.floor(maxW / wChild) * wChild;
            qsa('.content-mega-horizontal', item).forEach((el) => setWidth(el, wMega));

            const topMega = qs('.level-top-mega', item);
            if (!topMega || !menuBox) return;

            const offsetMega   = offset(item);
            const offsetBox    = offset(menuBox);
            const offsetBoxMax = offset(boxMax);
            let itemSpace, xMaxOffset, xItemOffset;

            if (isRTL) {
                const wWin            = viewportWidth();
                const megaRight       = wWin - (offsetMega.left   + outerWidth(item));
                const boxRight        = wWin - (offsetBox.left    + outerWidth(menuBox));
                const boxMaxRight     = wWin - (offsetBoxMax.left + outerWidth(boxMax));
                itemSpace  = megaRight - boxRight;
                xMaxOffset = boxMaxRight + maxW;
                xItemOffset = megaRight + outerWidth(topMega);
            } else {
                itemSpace  = offsetMega.left - offsetBox.left;
                xMaxOffset = offsetBoxMax.left + maxW;
                xItemOffset = offsetMega.left + outerWidth(topMega);
            }

            const xSpace = xItemOffset - xMaxOffset;
            const space  = itemSpace - xSpace;
            if (space < itemSpace) {
                css(topMega, { [dir]: space });
            } else {
                /* Fix error sticky menu position */
                css(topMega, { [dir]: 'auto' });
            }
        }

        vertical(navtop, fullWidth, init) {
            if (init) this.initMenu(navtop, fullWidth, false);
            navtop.forEach((item) => {
                this._once(item, 'vertical', () => {
                    item.addEventListener('mouseenter', () => this._hoverVertical(item, fullWidth));
                });
            });
        }

        _hoverVertical(item, fullWidth) {
            const boxMax  = this._boxMax(fullWidth);
            const maxW    = width(boxMax);
            const topMega = qs('.level-top-mega', item);

            const calc   = this._calcMega(item);
            const wChild = calc.wChild;
            let wMega    = calc.wMega;

            const extra = topMega ? outerWidth(topMega, true) - width(topMega) : NaN;
            let wMegaMax = maxW - extra;
            if (fullWidth || boxMax === document.body) {
                wMegaMax = wMegaMax - offset(item).left - width(item);
            }
            if (wMega > wMegaMax) wMega = Math.floor(wMegaMax / wChild) * wChild;
            qsa('.content-mega-horizontal', item).forEach((el) => setWidth(el, wMega));
        }

        toggleVertical(vmenu) {
            if (vmenu.classList.contains('init')) return;
            vmenu.classList.add('init');

            qsa('.v-title', vmenu).forEach((title) => {
                title.addEventListener('click', () => {
                    qsa('.nav-desktop', vmenu).forEach((nav) => {
                        nav.style.height = '';
                        slideToggle(nav, 400);
                    });
                });
            });

            const catplus = qsa('.nav-desktop > .level0', vmenu)
                .filter((el) => !isVisible(el) && !el.matches('.all-cat'));
            const allCat = qsa('.all-cat', vmenu);

            if (catplus.length) {
                allCat.forEach((btn) => {
                    show(btn);
                    btn.addEventListener('click', () => {
                        Array.from(btn.children).forEach(toggle);
                        catplus.forEach((el) => slideToggle(el, 'slow'));
                    });
                });
            } else {
                allCat.forEach(hide);
            }
        }

        /* ----------------------------- active menu ------------------------ */

        active(menu) {
            if (document.body.classList.contains('cms-index-index')) {
                qsa('li.home', menu).forEach((li) => li.classList.add('active'));
                qsa('li:not(.home)', menu).forEach((li) => li.classList.remove('active'));
                return;
            }

            const currentUrl = window.location.href.replace(/\/$/, '');
            qsa('li:not(.home) a', menu).forEach((a) => {
                const href = a.getAttribute('href');
                if (href === null) return;
                const thisHref = href.split('?')[0];
                if (thisHref && currentUrl.indexOf(thisHref) === 0) {
                    qsa('li.home', menu).forEach((li) => li.classList.remove('active'));
                    const li = a.closest('li');
                    if (li) li.classList.add('active');
                }
            });
        }

        /* ------------------------------ megamenu -------------------------- */

        megamenu(menu) {
            const body         = document.body;
            const isHorizontal = menu.matches(this.settings.horizontal);
            const navDesktop   = qsa('.nav-desktop', menu);

            /* Topmenu */
            if (isHorizontal && navDesktop.some((n) => n.classList.contains('sticker'))) this.sticky(menu);

            /* Active menu top-vmega */
            qsa('.vmega .category-item', menu).forEach((item) => {
                item.addEventListener('mouseenter', () => {
                    siblings(item).forEach((s) => s.classList.remove('over'));
                    item.classList.add('over');
                });
            });

            const fullWidth = getData(navDesktop[0], 'fullwidth');
            const bp        = getData(navDesktop[0], 'breakpoint');
            if (bp) this.breakpoint = bp;

            const leveltop = navDesktop
                .reduce((list, nav) => list.concat(qsa('li.level0.hasChild, li.level0.home', nav)), [])
                .filter((li) => !li.classList.contains('dropdown'));

            this.toggleVertical(menu);

            const build = () => (isHorizontal
                ? this.horizontal(leveltop, fullWidth, true)
                : this.vertical(leveltop, fullWidth, true));
            build();

            // Responsive
            const isMobile = () => this.breakpoint > viewportWidth();
            if (isMobile()) body.classList.add('nav-mobile-display');

            this._onRefresh(() => {
                if (isMobile()) {
                    body.classList.add('nav-mobile-display');
                    qsa('.nav-mobile').forEach(show);
                    if (isHorizontal) navDesktop.forEach(hide);
                } else {
                    body.classList.remove('nav-mobile-display');
                    qsa('.nav-mobile').forEach(hide);
                    if (isHorizontal) navDesktop.forEach(show);
                    build();
                }
            });

            this.taphover(menu);
            this.active(menu);
        }

        /* ------------------------------- misc ----------------------------- */

        getAppVersion(appName, href) {
            // this expression is to get the version string
            const regx    = /.*\/(.*?)\/assets\//i;
            const result  = regx.exec(href);
            const version = result ? result[1].replace(/\D/g, '') : '1.0.0';
            console.log(
                `%c ${appName} %c v${version}  %c`,
                'background: #555555; padding: 1px; margin-bottom: 2px; border-radius: 3px 0 0 3px; color: #fff',
                'background: #44cc11; padding: 1px; margin-bottom: 2px; border-radius: 0 3px 3px 0; color: #fff',
                'background:transparent',
                `🚀 ${href}`
            );
            return version;
        }
    }

    Magicmenu.defaults = {
        breakpoint: 991,
        horizontal: '.magicmenu',
        vertical  : '.vmagicmenu',
        sticky    : '.header-sticker'
    };
    Magicmenu.Accordion = MagicAccordion;

    /* ---- Auto init khi DOM ready (tương đương $(document).ready) ------- */
    if (typeof document !== 'undefined') {
        const start = () => { Magicmenu.instance = new Magicmenu().init(); };
        if (document.readyState !== 'loading') start();
        else document.addEventListener('DOMContentLoaded', start, { once: true });
    }

    return Magicmenu;
});
