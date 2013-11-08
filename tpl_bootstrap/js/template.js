/*
 * Pines Framework Bootstrap Template
 *
 * http://pinesframework.org/
 * Copyright (c) 2013 SciActive
 * Author: Angela Murrell <angela@sciactive.com>
 *
 * Triple license under the GPL, LGPL, and MPL:
 *	  http://www.gnu.org/licenses/gpl.html
 *	  http://www.gnu.org/licenses/lgpl.html
 *	  http://www.mozilla.org/MPL/MPL-1.1.html
 */
var rgb_to_hex = function(rgb) {
        var r = rgb[0],
                g = rgb[1],
                b = rgb[2];
        return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
};

var color_lum = function(hex, lum) {
        // validate hex string  
        hex = String(hex).replace(/[^0-9a-f]/gi, '');  
        if (hex.length < 6) {  
                hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];  
        }  
        lum = lum || 0;  
        // convert to decimal and change luminosity  
        var rgb = "#", c, i;  
        for (i = 0; i < 3; i++) {  
                c = parseInt(hex.substr(i*2,2), 16);  
                c = Math.round(Math.min(Math.max(0, c + (c * lum)), 255)).toString(16);  
                rgb += ("00"+c).substr(c.length);  
        }  
        return rgb; 
};

var hex_to_rgb = function(hex) {
        var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
                r: parseInt(result[1], 16),
                g: parseInt(result[2], 16),
                b: parseInt(result[3], 16)
        } : null;
};

var hsl_to_rgb = function(hsl) {
        var h = hsl[0],
                s = hsl[1],
                l = hsl[2];
        var r, g, b;

        if(s == 0){
                r = g = b = l; // achromatic
        }else{
                function hue2rgb(p, q, t){
                        if(t < 0) t += 1;
                        if(t > 1) t -= 1;
                        if(t < 1/6) return p + (q - p) * 6 * t;
                        if(t < 1/2) return q;
                        if(t < 2/3) return p + (q - p) * (2/3 - t) * 6;
                        return p;
                }

                var q = l < 0.5 ? l * (1 + s) : l + s - l * s;
                var p = 2 * l - q;
                r = hue2rgb(p, q, h + 1/3);
                g = hue2rgb(p, q, h);
                b = hue2rgb(p, q, h - 1/3);
        }

        return [r * 255, g * 255, b * 255];
}

var rgb_to_hsl = function(rgb){
        var r1 = rgb[0] / 255;
        var g1 = rgb[1] / 255;
        var b1 = rgb[2] / 255;

        var maxColor = Math.max(r1,g1,b1);
        var minColor = Math.min(r1,g1,b1);
        //Calculate L:
        var L = (maxColor + minColor) / 2 ;
        var S = 0;
        var H = 0;
        if(maxColor != minColor){
                //Calculate S:
                if(L < 0.5){
                        S = (maxColor - minColor) / (maxColor + minColor);
                }else{
                        S = (maxColor - minColor) / (2.0 - maxColor - minColor);
                }
                //Calculate H:
                if(r1 == maxColor){
                        H = (g1-b1) / (maxColor - minColor);
                }else if(g1 == maxColor){
                        H = 2.0 + (b1 - r1) / (maxColor - minColor);
                }else{
                        H = 4.0 + (r1 - g1) / (maxColor - minColor);
                }
        }

        L = L * 100;
        S = S * 100;
        H = H * 60;
        if(H<0){
                H += 360;
        }
        var result = [H, S, L];
        return result;
};

var get_text_shadow = function(color) {
        if (color.match(/^#/)) {
                // It's a hex
                var rgb = new Array();
                rgb[0] = hex_to_rgb(color).r;
                rgb[1] = hex_to_rgb(color).g;
                rgb[2] = hex_to_rgb(color).b;
                var hsl = rgb_to_hsl(rgb);
        } else if (color.match(/^r/)) {
                // It's an rgb color
                var rgb_colors = color.replace(/^rgba?\( ?([\d.]+%?,) ?([\d.]+%?,) ?([\d.]+%?)(?:,? ?[\d.]+)?\)$/i, '$1$2$3');
                var rgb = rgb_colors.split(',');
                var hsl = rgb_to_hsl(rgb);
        } else {
                // It's already in hsl
                var hsl_colors = color.replace(/^hsla?\( ?([\d.]+%?,) ?([\d.]+%?,) ?([\d.]+%?)(?:,? ?[\d.]+)?\)$/i, '$1$2$3');
                var hsl = hsl_colors.split(',');
                var rgb = hsl_to_rgb(hsl);
        }

        if (hsl[2] == 100) {
                //  Use black
                var text_shadow = "0 -1px 0 rgba(0, 0, 0, 0.25)";
        } else if (hsl[2] == 0) {
                // Use White
                var text_shadow = "0 -1px 0 rgba(255, 255, 255, .2)";
        } else if (hsl[2] > 50) {
                // Make this color darker
                var hex = rgb_to_hex(rgb);
                var text_shadow_color = color_lum(hex, -.5);
        } else if (hsl[2] < 50) {
                // Make this color brighter
                var hex = rgb_to_hex(rgb);
                var text_shadow_color = color_lum(hex, .5);
        }

        if (hex) {
                var text_shadow = "0 -1px 0 "+text_shadow_color;
        }
        return text_shadow;
}
// Let the template load up to 4 style sheets that use the pines relative location.
if (load_css1.length)
	pines.loadcss(pines.rela_location+load_css1);
if (load_css2.length)
	pines.loadcss(pines.rela_location+load_css2);
if (load_css3.length)
	pines.loadcss(pines.rela_location+load_css3);
if (load_css4.length)
	pines.loadcss(pines.rela_location+load_css4);
// Let the template load up to 4 style sheets that use the pines relative location.
if (load_js1.length)
	pines.loadjs(pines.rela_location+load_js1);
if (load_js2.length)
	pines.loadjs(pines.rela_location+load_js2);
if (load_js3.length)
	pines.loadjs(pines.rela_location+load_js3);
if (load_js4.length)
	pines.loadjs(pines.rela_location+load_js4);

if (navbar_trigger == 'hover') {
	pines(function(){
        $('#nav').on('mouseenter', 'ul.nav > li.dropdown > a', function(){
                var item = $(this);
                item.siblings('ul.dropdown-menu').addClass('dropdown-opened');
                if (!item.parent().hasClass('open'))
                        item.dropdown('toggle');
                setTimeout(function(){
                        item.siblings('ul.dropdown-menu').removeClass('dropdown-opened');
                }, 800);
        }).on('mouseleave', 'ul.nav', function(e){
                var nav = $(this);
                var mouse_top = e.pageY - $(window).scrollTop();
                var navbar_height = $('.navbar-inner').height();
                if (mouse_top > (navbar_height + 5)) {
                        nav.find('li.dropdown.open > a').dropdown('toggle');
                }
        });

        // To ensure that the menu closes
        $('#page').on('hover', function(){
                $('ul.nav').find('li.dropdown.open > a').dropdown('toggle');
        });

        // To enable clicks on dropdowns that have links too
        $('#nav').on('mousedown', 'ul.nav > li.dropdown > a', function(e){
                var href = $(this).attr('href');
                switch(e.which)
                {
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
}
if (mobile_menu == 'adjusted') {
	pines(function(){
        $(window).resize(function(){
                if ($(this).width() < 800) {
                        $('ul.nav li ul', '#nav').hide();
                        $('.nav-helper, .sub-nav-helper', '#nav').remove();
                        $('ul.nav > li.dropdown > a', '#nav').after('<span class="btn btn-navbar nav-helper">::</span>')

                        $('ul.nav > li.dropdown > span.nav-helper', '#nav').on('click', function(){
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

                        $('li.dropdown-submenu span.sub-nav-helper', '#nav').on('click', function(){
                                var dropdown = $(this).closest('li.dropdown-submenu');
                                if (dropdown.hasClass('submenu-show'))
                                        dropdown.removeClass('submenu-show').children('ul').hide();
                                else
                                        dropdown.addClass('submenu-show').children('ul').show();
                        });

//					$('li.dropdown-submenu > a', '#nav').click(function(){
//						var dropdown = $(this).closest('li.dropdown-submenu');
//						if (dropdown.hasClass('submenu-show'))
//							dropdown.removeClass('submenu-show').children('ul').hide();
//						else
//							dropdown.addClass('submenu-show').children('ul').show();
//					});
                } else {
                        $('ul.nav li ul', '#nav').removeAttr('style');
                        $('ul.nav li.dropdown', '#nav').unbind('mouseenter mouseleave');
                        $('li.dropdown-submenu', '#nav').unbind('mouseenter mouseleave');
                }
		}).resize();
	});
}
if (footer_height == 'adjusted' && footer_type == 'fixed') {
        setTimeout(function(){
                var el = $("#footer");
                el.height($(document).height() - el.offset().top - 1);
        }, 2000);
}

pines(function(){
        if (verified_brand_colors) {
			// Get brand color text shadow.
			var text_shadow = get_text_shadow(brand_color);
			$('#nav.brand-color a.brand').css('text-shadow', text_shadow);
		}
        if (verified_font_colors) {
                // Get font color text shadow.
                var text_shadow = get_text_shadow(font_color);
                $('#nav ul.nav > li.dropdown > a').css('text-shadow', text_shadow);
        } if (navbar_menu_height > 0) {
                var navbar_menu_height = navbar_menu_height;
                var min_height = $('.navbar-inner').css('min-height');
                if (navbar_menu_height >= parseInt(min_height)) {
                        // Do stuff
                        var li = $('.navbar .nav > li > a'),
                                li_outer = li.outerHeight(),
                                li_height = li.height();
                        var padding = li_outer - li_height;
                        var leftover = (navbar_menu_height - padding) / 2;
                        li.css({
                                'padding-top': leftover+'px',
                                'padding-bottom': leftover+'px'
                        });
                        $('#nav a.brand').css({
                                'padding-top': leftover+'px',
                                'padding-bottom': leftover+'px'
                        });
                        $('.navbar-inner').css('min-height', navbar_menu_height+'px');
                }
        }
});