/**
 * Simple JS Option.
 *
 * Hover Navigation
 * 
 * Toggle in Configuration.
 *
 * @package Templates\simple
 * @license http://opensource.org/licenses/MIT
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright Angela Murrell
 */
pines(function() {
    var nav = $('#nav');
    var page = $('#page');
    nav.addClass('hover-menu');
    nav.on('mouseenter', 'ul.nav > li.dropdown > a', function() {
        var item = $(this);
        item.siblings('ul.dropdown-menu').addClass('dropdown-opened');
        if (!item.parent().hasClass('open'))
            item.dropdown('toggle');
        setTimeout(function() {
            item.siblings('ul.dropdown-menu').removeClass('dropdown-opened');
        }, 800);
    }).on('mouseleave', 'ul.nav', function(e) {
        var nav = $(this);
        var mouse_top = e.pageY - $(window).scrollTop();
        var navbar_height = $('.navbar-inner').height();
        if (mouse_top > (navbar_height + 5)) {
            nav.find('li.dropdown.open > a').dropdown('toggle');
        }
    });

    // To ensure that the menu closes
    page.on('hover', function() {
        $('ul.nav').find('li.dropdown.open > a').dropdown('toggle');
    });

    // To enable clicks on dropdowns that have links too
    nav.on('mousedown', 'ul.nav > li.dropdown > a', function(e) {
        var href = $(this).attr('href');
        switch (e.which) {
            case 1:
                //left Click
                window.location = href;
                break;
            case 2:
                //middle Click
                window.open(href);
                break;
            default:
                break;
        }
        return true;// to allow the browser to know that we handled it.
    });
});