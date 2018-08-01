/*!
 * nav-side.js - v1.0
 * Side navigation for Bootstrap formatted navigation panel
 * https://github.com/MuntashirAkon/nav-side
 * @author Muntashir Al-Islam <muntashir.islam96@gmail.com>
 * @license MIT
 */

"use strict";
/**
 * Side Navigation for mobile devices
 *
 * Usage:
 * - Use the following with `nav.navbar`
 *  - .nav-side       : Applies basic Nav-Side functionality
 *  - .nav-side-touch : Enable swiping left-right (needs Pure-Swipe.js)
 *
 * - For a close button, use a button with `.close-btn` class
 *
 * - For disabled dropdown, use `.disable` class with `li.dropdown`
 *
 * - For delays, use *one* of the following with `nav.navbar`
 *  - .nav-side-delay-500 : Delay 500 MS
 *  - .nav-side-delay-250 : Delay 250 MS
 *  - .nav-side-delay-150 : Delay 150 MS
 *  - .nav-side-delay-100 : Delay 100 MS
 *
 * @type {{closed: boolean, init: NavSide.init, open: NavSide.open, close: NavSide.close}}
 */
let NavSide = {
    /* Width Constants */
    WIDTH_250PX : 'nav-side-width-250',
    WIDTH_MINUS_BTN: 'nav-side-width-minus-btn', // Default
    WIDTH_FULL: 'nav-side-width-full',
    /* Delay Constants */
    DELAY_500MS: 'nav-side-delay-500',
    DELAY_250MS: 'nav-side-delay-250', // Default
    DELAY_150MS: 'nav-side-delay-150',
    DELAY_100MS: 'nav-side-delay-100',
    /**
     * Sidebar Width
     */
    width: 250,
    /**
     * Size of the button (.btn-close)
     */
    BTN_SIZE: 24,
    delay: 250,
    width_config: this.WIDTH_MINUS_BTN,
    closed : true,
    header: null,
    /**
     * Initializes Nav-Side
     */
    init: function () {
        // If .nav-side isn't available, don't initialize
        if($('body .nav-side').length !== 1) return;
        let nav_side =  $('.nav-side');
        // Create overlay
        if($('body #nav-side-overlay').length !== 1) $('body').prepend('<div id="nav-side-overlay"></div>');
        // Create slider
        if($('body #nav-side-slider').length !== 1) $('body').prepend('<div id="nav-side-slider"></div>');
        // Add header (with button)
        if($('body #nav-side-header').length !== 1) $('body').prepend(
            '<nav id="nav-side-header" role="navigation" class="navbar">' +
            '<div class="container">' +
            '<span id="nav-side-opener">&#9776;</span>' +
            '<div class="navbar-header"></div>' +
            '</div>' +
            '</nav>'
        );
        // Add a cross icon
        if(!nav_side.hasClass('btn-close')) $('.nav-side > .container').prepend('<a href="#" class="btn-close">&times;</a>');
        // Reset width
        NavSide.reset_width();
        // Copy navbar-header
        this.header = $('#nav-side-header');
        this.header.find('.navbar-header').append($('.nav-side .navbar-header').html());
        // Add extra classes that Bootstrap 3 has in the header field
        if(nav_side.hasClass('navbar-default')) this.header.addClass('navbar-default');
        if(nav_side.hasClass('navbar-inverse')) this.header.addClass('navbar-inverse');
        if(nav_side.hasClass('navbar-fixed-top')) this.header.addClass('navbar-fixed-top');
        if(nav_side.hasClass('navbar-fixed-bottom')) this.header.addClass('navbar-fixed-bottom');
        // Event handlers
        $('#nav-side-opener, #nav-side-slider').on('click', function () { NavSide.toggle(); });
        $('#nav-side-overlay, .btn-close').on('click', function () { NavSide.close(); });
        $('.nav-side a').on('click', function () { NavSide.closeIfPossible($(this)); });
        $(window).on('resize', function () {
            if(!NavSide.valid_width()){
                if(!NavSide.closed) NavSide.close();
                // Restore
                $('.nav-side')
                    .css('width', 'unset')
                    .find('.container').delay(this.delay).show(0);
            } else {
                // Restore
                $('.nav-side')
                    .css('width', '0')
                    .find('.container').hide(0);
            }
            NavSide.reset_width();
        });
        // Enable touch events if requested with .nav-side-touch
        if($('.nav-side.nav-side-touch').length === 1) {
            /* Touch events
             * use pure-swipe.min.js (https://raw.githubusercontent.com/john-doherty/pure-swipe/master/dist/pure-swipe.min.js)
             */
            document.addEventListener('swiped-left', function () { NavSide.close(); });
            document.addEventListener('swiped-right', function () { NavSide.open(); });
        }
    },
    open: function () {
        if(!this.closed || !this.valid_width()) return false;
        this.closed = false;
        $('#nav-side-header')
            .css('margin-left', this.width + 'px')
            .find('.navbar-header').hide();
        $('#nav-side-overlay').show();
        $('.nav-side')
            .css('width', this.width + 'px')
            .find('.container').delay(this.delay).show(0);
        $('#nav-side-opener').addClass('open');
        return true;
    },
    close: function() {
        if(this.closed) return false;
        this.closed = true;
        $('#nav-side-header')
            .css('margin-left', '0')
            .find('.navbar-header').delay(this.delay).show(0);
        $('#nav-side-overlay').hide();
        $('.nav-side')
            .css('width', '0')
            .find('.container').hide();
        $('#nav-side-opener').removeClass('open');
        return true;
    },
    closeIfPossible: function (a) {
        let href = a.attr('href');
        switch (href){
            case '':
            case '#':
            case './#':
                // do nothing
                break;
            default:
                (function() {
                    let deferred = $.Deferred();
                    NavSide.close();
                    deferred.resolve();
                    return deferred.promise();
                })().done((function (href) {
                    window.location = href;
                })(href));
        }
    },
    toggle: function () {
        return this.open() || this.close();
    },
    valid_width: function () {
        return $('body').width() < 768;
    },
    reset_width: function () {
        if(this.valid_width()){
            let nav_side = $('.nav-side');
            let body_width = $('body').width();
            // Set width
            if(nav_side.hasClass(this.WIDTH_250PX)) this.width_config = this.WIDTH_250PX;
            else if(nav_side.hasClass(this.WIDTH_FULL)) this.width_config = this.WIDTH_FULL;
            else this.width_config = this.WIDTH_MINUS_BTN; // Default: this.WIDTH_MINUS_BTN
            // Set delay
            if(nav_side.hasClass(this.DELAY_500MS)) this.delay = 500;
            else if(nav_side.hasClass(this.DELAY_150MS)) this.delay = 150;
            else if(nav_side.hasClass(this.DELAY_100MS)) this.delay = 100;
            else this.delay = 250; // Default: this.DELAY_250MS
            // Update width
            switch (this.width_config){
                case this.WIDTH_250PX:
                    this.width = 250;
                    break;
                case this.WIDTH_FULL:
                    this.width = body_width;
                    break;
                case this.WIDTH_MINUS_BTN:
                default:
                    this.width = body_width - this.BTN_SIZE;
            }
        }
    }
};
// Init side-nav
$(document).ready(function () {
    NavSide.init();
});
