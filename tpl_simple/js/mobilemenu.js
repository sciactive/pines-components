/**
 * Simple JS Option.
 *
 * Mobile Menu - Adjusted Bootstrap
 * 
 * Toggle in Configuration
 *
 * @package Templates\simple
 * @license http://opensource.org/licenses/MIT
 * @author Angela Murrell <amasiell.g@gmail.com>
 * @copyright Angela Murrell
 */
pines(function() {
    $(window).resize(function() {
        if ($(this).width() < 800) {
            $('ul.nav li ul', '#nav').hide();
            $('.nav-helper, .sub-nav-helper', '#nav').remove();
            $('ul.nav > li.dropdown > a', '#nav').after('<span class="btn btn-navbar nav-helper">::</span>')

            $('ul.nav > li.dropdown > span.nav-helper', '#nav').on('click', function() {
                var dropdown = $(this).closest('li.dropdown');
                if (dropdown.hasClass('helper-show')) {
                    dropdown.removeClass('helper-show').children('ul').hide();
                    $('.nav-collapse', '#nav').css('height', 'auto');
                } else {
                    dropdown.addClass('helper-show').children('ul').show();
                    $('.nav-collapse', '#nav').css('height', 'auto');
                }
            });

            $('li.dropdown-submenu > a', '#nav').after('<span class="btn btn-navbar sub-nav-helper">::</span>')

            $('li.dropdown-submenu span.sub-nav-helper', '#nav').on('click', function() {
                var dropdown = $(this).closest('li.dropdown-submenu');
                if (dropdown.hasClass('submenu-show'))
                    dropdown.removeClass('submenu-show').children('ul').hide();
                else
                    dropdown.addClass('submenu-show').children('ul').show();
            });
        } else {
            $('ul.nav li ul', '#nav').removeAttr('style');
            $('ul.nav li.dropdown', '#nav').unbind('mouseenter mouseleave');
            $('li.dropdown-submenu', '#nav').unbind('mouseenter mouseleave');
        }
    }).resize();
});