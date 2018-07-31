/*!
 * nav-side.js - v1.0
 * Side navigation for Bootstrap formatted navigation panel
 * https://github.com/MuntashirAkon/nav-side
 * @author Muntashir Al-Islam <muntashir.islam96@gmail.com>
 * @license MIT
 */

/**
 * Side Navigation for mobile devices
 *
 * Usage:
 * - Use the following with nav.navbar
 *  - .nav-side       : Applies basic Nav-Side functionality
 *  - .nav-side-touch : Enable swiping left-right (needs Pure-Swipe.js)
 *
 * - For a close button, use a button with `.close-btn` class
 *
 * @type {{closed: boolean, init: NavSide.init, open: NavSide.open, close: NavSide.close}}
 */
NavSide = {
    /* Constants */
    WIDTH_250PX : 'nav-side-width-250',
    WIDTH_MINUS_BTN: 'nav-side-width-minus-btn', // Default
    WIDTH_FULL: 'nav-side-width-full',
    /**
     * Sidebar Width
     */
    WIDTH: 250,
    /**
     * Size of the button (.btn-close)
     */
    BTN_SIZE: 24,
    config: this.WIDTH_MINUS_BTN,
    closed : true,
    header: null,
    /**
     * Initializes Nav-Side
     */
    init: function () {
        // If .nav-side isn't available, don't initialize
        if($('body .nav-side').length !== 1) return;
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
        if($('.nav-side .btn-close').length !== 1) $('.nav-side > .container').prepend('<a href="#" class="btn-close">&times;</a>');
        // Reset width
        NavSide.reset_width();
        // Copy navbar-header
        this.header = $('#nav-side-header');
        this.header.find('.navbar-header').append($('.nav-side .navbar-header').html());
        // Event handlers
        $('#nav-side-opener, #nav-side-slider').on('click', function () { NavSide.toggle(); });
        $('#nav-side-overlay, .btn-close').on('click', function () { NavSide.close(); });
        $('.nav-side a').on('click', function () { NavSide.closeIfPossible($(this)); });
        $(window).on('resize', function () {
            NavSide.reset_width();
            if(!NavSide.valid_width()){
                if(!NavSide.closed) NavSide.close();
                // Restore
                $('.nav-side')
                    .css('width', 'unset')
                    .find('.container').delay(500).show(0);
            } else {
                // Side Nav
                $('.nav-side')
                    .css('width', '0')
                    .find('.container').hide(0);
            }
        });
        // Enable touch events if requested with .nav-side-touch
        if($('.nav-side.nav-side-touch').length === 1) {
            /* Touch events
             * use pure-swipe.min.js (https://raw.githubusercontent.com/john-doherty/pure-swipe/master/dist/pure-swipe.min.js)
             */
            document.addEventListener('swiped-left', function (e) {
                NavSide.close();
            });
            document.addEventListener('swiped-right', function (e) {
                NavSide.open();
            });
        }
    },
    open: function () {
        if(!this.closed || !this.valid_width()) return false;
        this.closed = false;
        $('#nav-side-header')
            .css('margin-left', this.WIDTH + 'px')
            .find('.navbar-header').hide();
        $('#nav-side-overlay').show();
        $('.nav-side')
            .css('width', this.WIDTH + 'px')
            .find('.container').delay(500).show(0);
        $('#nav-side-opener').addClass('open');
        return true;
    },
    close: function() {
        if(this.closed) return false;
        this.closed = true;
        $('#nav-side-header')
            .css('margin-left', '0')
            .find('.navbar-header').delay(500).show(0);
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
                this.close();
                window.location = href;
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
            if(nav_side.hasClass(this.WIDTH_250PX)) this.config = this.WIDTH_250PX;
            else if(nav_side.hasClass(this.WIDTH_FULL)) this.config = this.WIDTH_FULL;
            else this.config = this.WIDTH_MINUS_BTN;
            switch (this.config){
                case this.WIDTH_250PX:
                    this.WIDTH = 250;
                    break;
                case this.WIDTH_FULL:
                    this.WIDTH = $('body').width();
                    break;
                case this.WIDTH_MINUS_BTN:
                default:
                    this.WIDTH = $('body').width() - this.BTN_SIZE;
            }
        }
    }
};
// Init side-nav
$(document).ready(function () {
    NavSide.init();
});
