<?php
/**
 * Main page of the Bootstrap template.
 *
 * The page which is output to the user is built using this file.
 *
 * @package Templates\bootstrap
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
// Experimental AJAX code.
if ($pines->config->tpl_bootstrap->ajax && ($_REQUEST['tpl_bootstrap_ajax'] == 1 && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
	$return = array(
		'notices' => $pines->page->get_notice(),
		'errors' => $pines->page->get_error(),
		'main_menu' => $pines->page->render_modules('main_menu', 'module_head'),
		'pos_head' => $pines->page->render_modules('head', 'module_head'),
		'pos_top' => $pines->page->render_modules('top', 'module_header'),
		'pos_header' => $pines->page->render_modules('header', 'module_header').'&nbsp;',
		'pos_header_right' => $pines->page->render_modules('header_right', 'module_header_right'),
		'pos_pre_content' => $pines->page->render_modules('pre_content', 'module_header'),
		'pos_breadcrumbs' => $pines->page->render_modules('breadcrumbs'),
		'pos_content_top_left' => $pines->page->render_modules('content_top_left'),
		'pos_content_top_right' => $pines->page->render_modules('content_top_right'),
		'pos_content' => $pines->page->render_modules('content', 'module_content'),
		'pos_content_bottom_left' => $pines->page->render_modules('content_bottom_left'),
		'pos_content_bottom_right' => $pines->page->render_modules('content_bottom_right'),
		'pos_post_content' => $pines->page->render_modules('post_content', 'module_header'),
		'pos_left' => $pines->page->render_modules('left', 'module_side'),
		'pos_right' => $pines->page->render_modules('right', 'module_side'),
		'pos_footer' => $pines->page->render_modules('footer', 'module_header'),
		'pos_bottom' => $pines->page->render_modules('bottom', 'module_header')
	);
	echo json_encode($return);
	return;
}
header('Content-Type: text/html');

$width = ($pines->config->template->width == 'fluid') ? '-fluid' : '';

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title><?php echo htmlspecialchars($pines->page->get_title()); ?></title>
	<link rel="icon" type="image/vnd.microsoft.icon" href="<?php echo htmlspecialchars($pines->config->location); ?>favicon.ico" />
	<meta name="HandheldFriendly" content="true" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->rela_location); ?>system/includes/js.php"></script>
	<script type="text/javascript">pines(function(){if ($.pnotify) {
		$.pnotify.defaults.opacity = .9;
		$.pnotify.defaults.delay = 15000;
	}});</script>
	<?php if ($pines->config->tpl_bootstrap->ajax) { ?>
	<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/js/ajax.js"></script>
	<?php } ?>
	<?php if ($pines->template->verify_color($pines->config->tpl_bootstrap->lighter_color) && $pines->template->verify_color($pines->config->tpl_bootstrap->darker_color) && $pines->template->verify_color($pines->config->tpl_bootstrap->border_color)) { ?>
	<!--[if lt IE 8]>
	<style type="text/css">
	.navbar-inner {
		filter: progid:DXImageTransform.Microsoft.gradient(enabled = false) !important;
	}
	</style>
	<![endif]-->
	<?php } ?>
	<!--[if lt IE 8]>
	<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/js/jquery/jquery.dropdown.js"></script>
	<![endif]-->
	<?php echo $pines->page->render_modules('head', 'module_head'); ?>
	<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/css/pines.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/css/print.css" media="print" rel="stylesheet" type="text/css" />
	<?php if (!empty($pines->config->tpl_bootstrap->font_folder)) { ?>
	<link href="<?php echo htmlspecialchars($pines->config->tpl_bootstrap->font_folder); ?>stylesheet.css" media="all" rel="stylesheet" type="text/css" />
	<?php } ?>
	<script type="text/javascript">
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
		<?php if (isset($pines->config->tpl_bootstrap->load_css1)) { ?>
			pines.loadcss(pines.rela_location+"<?php echo $pines->config->tpl_bootstrap->load_css1; ?>");
		<?php } if (isset($pines->config->tpl_bootstrap->load_css2)) { ?>
			pines.loadcss(pines.rela_location+"<?php echo $pines->config->tpl_bootstrap->load_css2; ?>");
		<?php } if (isset($pines->config->tpl_bootstrap->load_css2)) { ?>
			pines.loadcss(pines.rela_location+"<?php echo $pines->config->tpl_bootstrap->load_css3; ?>");
		<?php } if (isset($pines->config->tpl_bootstrap->load_css2)) { ?>
			pines.loadcss(pines.rela_location+"<?php echo $pines->config->tpl_bootstrap->load_css4; ?>");
		<?php } ?>
			
		// Let the template load up to 4 style sheets that use the pines relative location.
		<?php if (isset($pines->config->tpl_bootstrap->load_js1)) { ?>
			pines.loadjs(pines.rela_location+"<?php echo $pines->config->tpl_bootstrap->load_js1; ?>");
		<?php } if (isset($pines->config->tpl_bootstrap->load_js2)) { ?>
			pines.loadjs(pines.rela_location+"<?php echo $pines->config->tpl_bootstrap->load_js2; ?>");
		<?php } if (isset($pines->config->tpl_bootstrap->load_js3)) { ?>
			pines.loadjs(pines.rela_location+"<?php echo $pines->config->tpl_bootstrap->load_js3; ?>");
		<?php } if (isset($pines->config->tpl_bootstrap->load_js4)) { ?>
			pines.loadjs(pines.rela_location+"<?php echo $pines->config->tpl_bootstrap->load_js4; ?>");
		<?php } ?>
		
		
		<?php if ($pines->config->tpl_bootstrap->navbar_trigger == "hover") { ?>
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
		<?php } if ($pines->config->tpl_bootstrap->mobile_menu == "adjusted") { ?>
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
		<?php } if ($pines->config->tpl_bootstrap->footer_height == "adjusted" && $pines->config->tpl_bootstrap->footer_type != "fixed") { ?>
			setTimeout(function(){
				var el = $("#footer");
				el.height($(document).height() - el.offset().top - 1);
			}, 2000);
		<?php } ?>
	</script>
	<script type="text/javascript">
		pines(function(){
			<?php if ($pines->template->verify_color($pines->config->tpl_bootstrap->brand_color) && $pines->template->verify_color($pines->config->tpl_bootstrap->brand_hover_color)) { ?>
				// Get brand color text shadow.
				var brand_color = "<?php echo $pines->config->tpl_bootstrap->brand_color; ?>";
				var text_shadow = get_text_shadow(brand_color)
				$('#nav.brand-color a.brand').css('text-shadow', text_shadow);
			<?php } if ($pines->template->verify_color($pines->config->tpl_bootstrap->font_color) && $pines->template->verify_color($pines->config->tpl_bootstrap->font_hover_color)) { ?>
				// Get font color text shadow.
				var font_color = "<?php echo $pines->config->tpl_bootstrap->font_color; ?>";
				var text_shadow = get_text_shadow(font_color)
				$('#nav ul.nav > li.dropdown > a').css('text-shadow', text_shadow);
			<?php } if ($pines->config->tpl_bootstrap->navbar_menu_height > 0) { ?>
				var navbar_menu_height = "<?php echo $pines->config->tpl_bootstrap->navbar_menu_height; ?>";
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
			<?php } ?>
		});
	</script>
	<style type="text/css">
		/* Media Queries */
		@media (max-width: 767px) {
			#footer {
				margin-left: -20px !important;
				margin-right: -20px !important;
				padding-left: 20px !important;
				padding-right: 20px !important;
			}
		}
		/* Conditional Classes template CSS */
		/* Body */
		body.body-font {
			font-family: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->body_fontface); ?>;
		}
		body.body-custom {
			<?php echo htmlspecialchars($pines->config->tpl_bootstrap->body_css); ?>;
		}
		/* Navbar Configurations */
		#nav-configure.bar-colors #nav > div.navbar-inner {
			filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='<?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>', endColorstr='<?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>');
			background-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?> !important;
			background-image: -moz-linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
			background-image: -webkit-gradient(linear, 0 0, 0 100%, from(<?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>), to(<?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>)) !important;
			background-image: -webkit-linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
			background-image: -o-linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
			background-image: linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
			border-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->border_color); ?> !important;
		}
		#nav-configure.bar-colors #nav ul.nav li > a:hover, #nav ul.nav > li.dropdown.open > a, #nav-configure.bar-colors #nav ul.nav > li.dropdown.active > a, #nav-configure.bar-colors #nav ul.nav > li.active > a {
			filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='<?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>', endColorstr='<?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>');
			background-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?> !important;
		}
		
		#nav-configure.bar-colors #nav .dropdown-menu li > a:hover, #nav-configure.bar-colors #nav .dropdown-menu li > a:focus, #nav-configure.bar-colors #nav .dropdown-submenu:hover > a, #nav-configure.bar-colors #nav .dropdown-menu .active > a, #nav-configure.bar-colors #nav .dropdown-menu .active > a:hover {
			filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='<?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>', endColorstr='<?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>');
			background-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?> !important;
			background-image: -moz-linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
			background-image: -webkit-gradient(linear, 0 0, 0 100%, from(<?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>), to(<?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>)) !important;
			background-image: -webkit-linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
			background-image: -o-linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
			background-image: linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
			border-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->border_color); ?> !important;
		}
		
		#nav-configure.bar-colors #nav.navbar-inverse .btn-navbar, #nav-configure.bar-colors #nav .btn-navbar  {
			filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='<?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>', endColorstr='<?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>');
			background-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?> !important;
			background-image: -moz-linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
			background-image: -webkit-gradient(linear, 0 0, 0 100%, from(<?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>), to(<?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>)) !important;
			background-image: -webkit-linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
			background-image: -o-linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
			background-image: linear-gradient(to bottom, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->lighter_color); ?>, <?php echo htmlspecialchars($pines->config->tpl_bootstrap->darker_color); ?>) !important;
			border-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->border_color); ?> !important;
		}
		
		#nav-configure.caret-color #nav ul.nav > li.dropdown > .dropdown-toggle .caret {
			border-top-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->caret_color); ?> !important;
		}
		#nav-configure.caret-color #nav ul.nav > li.dropdown.active > .dropdown-toggle .caret {
			border-top-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->caret_hover_color); ?> !important;
		}
		#nav-configure.caret-color #nav ul.nav > li.dropdown.open > a:hover > .caret, #nav-configure.caret-color #nav ul.nav > li.dropdown.open > a:focus > .caret, #nav-configure.caret-color #nav ul.nav > li.dropdown.open > a > .caret {
			border-top-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->caret_hover_color); ?> !important;
		}
		#nav-configure.caret-color #nav.navbar-inverse .nav li.dropdown > .dropdown-toggle .caret {
			border-top-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->caret_color); ?> !important;
		}
		
		#nav-configure.caret-color #nav.navbar-inverse .nav li.dropdown.open > .dropdown-toggle .caret, #nav-configure.caret-color #nav.navbar-inverse .nav li.dropdown.active > .dropdown-toggle .caret, #nav-configure.caret-color #nav.navbar-inverse .nav li.dropdown.open.active > .dropdown-toggle .caret {
			border-top-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->caret_hover_color); ?> !important;
		}
		
		#nav-configure.brand-color #nav a.brand {
			color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->brand_color); ?> !important;
		}
		#nav-configure.brand-color #nav a.brand:hover {
			color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->brand_hover_color); ?> !important;
		}
		
		#nav-configure.font-color #nav ul.nav > li.dropdown > a, #nav-configure.font-color #nav ul.nav > li > a {
			color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->font_color); ?> !important;
			text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
		}
		#nav-configure.font-color #nav .nav-collapse.collapse .nav > li > a, #nav-configure.font-color #nav .nav-collapse.collapse .dropdown-menu a {
			color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->font_color); ?> !important;
			text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
		}
		#nav-configure.font-color #nav .dropdown-menu li > a:hover, #nav-configure.font-color #nav .dropdown-menu li > a:focus, #nav-configure.font-color #nav .dropdown-submenu:hover > a {
			color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->font_hover_color); ?> !important;
		}
		#nav-configure.font-color #nav.navbar .btn-navbar .icon-bar {
			background-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->font_color); ?> !important;
		}
		#nav-configure.menu-height #nav .navbar-inner {
			min-height: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->navbar_menu_height); ?>;
		}
		#nav-configure.brand-font #nav a.brand {
			font-family: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->brand_fontface); ?> !important;
		}
		#nav-configure.menu-font #nav .nav a {
			font-family: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->menu_fontface); ?>;
		}
		#nav-configure.menu-custom #nav .nav a {
			<?php echo htmlspecialchars($pines->config->tpl_bootstrap->menu_css); ?>;
		}
		#nav-configure.brand-custom #nav a.brand {
			<?php echo htmlspecialchars($pines->config->tpl_bootstrap->brand_css); ?>;
		}
		#nav-configure.nav-bar-custom #nav .navbar-inner {
			<?php echo htmlspecialchars($pines->config->tpl_bootstrap->nav_bar_css); ?>;
		}
		
		/* Footer Configurations */
		#footer.footer-fixed {
			position: fixed !important;
			bottom: 0 !important;
		}
		#page.footer-fixed {
			margin-bottom: 4em;
		}
		@media (max-width: 800px) {
			#footer.footer-fixed {
				padding: 0 20px;
				margin-left: -20px;
				position: relative !important;
			}
		}
		#footer.bg-color {
			background-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->footer_background); ?> !important;
			border-color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->footer_border); ?> !important;
		}
		#footer.font-color {
			color: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->footer_font_color); ?> !important;
		}
		#footer.footer-custom {
			<?php echo htmlspecialchars($pines->config->tpl_bootstrap->footer_css); ?>;
		}
		
		/* Conditional Media Queries */
		@media (min-width: 800px){
			#nav-configure.nav-list-custom #nav .nav {
				<?php echo htmlspecialchars($pines->config->tpl_bootstrap->nav_list_css); ?>;
			}
		}
		@media (max-width: 800px) {
			#nav-configure.adjusted #nav li {
				clear:both;
			}
			#nav-configure.adjusted #nav .nav-helper {
				float:right !important;
				position:relative;
				cursor: pointer;
				zoom: 1;
				z-index: 200;
			}
			#nav-configure.adjusted #nav .sub-nav-helper {
				float:right !important;
				position:relative;
				cursor: pointer;
			}
			#nav-configure.adjusted #nav ul.nav > li.dropdown > a {
				float:left !important;
				width: 60% !important;
				position:relative;
				clear:both;
			}
			#nav-configure.adjusted #nav ul.dropdown-menu {
				width: 60% !important;
			}
			#nav-configure.adjusted #nav ul.dropdown-menu a, #nav-configure.adjusted #nav ul.dropdown-menu > li.dropdown-submenu {
				clear:both !important;
			}
			
			#nav-configure.adjusted #nav li.dropdown-submenu > a {
				float: left !important;
				position:relative;
			}
			
			#nav-configure.adjusted #nav li.dropdown-submenu > a:after {
				display:none;
			}
		}
	</style>
	<?php  
		// The following creates strings of classes to be added into certain elements based on configuration options.
		// Body Configuration 
		if (!empty($pines->config->tpl_bootstrap->body_fontface)) {
			// body font face has been set
			if (empty($body))
				$body = "body-font";
			else
				$body .= " body-font";
		}
		
		if (!empty($pines->config->tpl_bootstrap->body_css)) {
			// body custom css has been set
			if (empty($body))
				$body = "body-custom";
			else
				$body .= " body-custom";
		}
		// Navigation Bar Configuration
		if ($pines->config->tpl_bootstrap->mobile_menu == "adjusted") { 
			// The mobile menu option is chosen
			if (empty($nav))
				$nav = "adjusted";
			else
				$nav .= " adjusted";
		} 
		if ($pines->template->verify_color($pines->config->tpl_bootstrap->lighter_color) && $pines->template->verify_color($pines->config->tpl_bootstrap->darker_color) && $pines->template->verify_color($pines->config->tpl_bootstrap->border_color)) {
			// all the required fields for changing the nav bar colors have been filled out
			if (empty($nav))
				$nav = "bar-colors";
			else
				$nav .= " bar-colors";
		}
		if ($pines->template->verify_color($pines->config->tpl_bootstrap->caret_color) && $pines->template->verify_color($pines->config->tpl_bootstrap->caret_hover_color)) {
			// caret color has been set
			if (empty($nav))
				$nav = "caret-color";
			else
				$nav .= " caret-color";
		}
		if ($pines->template->verify_color($pines->config->tpl_bootstrap->brand_color) && $pines->template->verify_color($pines->config->tpl_bootstrap->brand_hover_color)) {
			// brand color has been set
			if (empty($nav))
				$nav = "brand-color";
			else
				$nav .= " brand-color";
		}
		if ($pines->template->verify_color($pines->config->tpl_bootstrap->font_color) && $pines->template->verify_color($pines->config->tpl_bootstrap->font_hover_color)) {
			// font color has been set
			if (empty($nav))
				$nav = "font-color";
			else
				$nav .= " font-color";
		}
		if (!empty($pines->config->tpl_bootstrap->brand_fontface)) {
			// brand font face has been set
			if (empty($nav))
				$nav = "brand-font";
			else
				$nav .= " brand-font";
		}
		if ($pines->config->tpl_bootstrap->navbar_menu_height > 0) {
			// Menu Height has been set
			if (empty($nav))
				$nav = "menu-height";
			else
				$nav .= " menu-height";
		}
		if (!empty($pines->config->tpl_bootstrap->menu_fontface)) {
			// Menu Font face has been set
			if (empty($nav))
				$nav = "menu-font";
			else
				$nav .= " menu-font";
		}
		if (!empty($pines->config->tpl_bootstrap->menu_css)) {
			// Custom Menu CSS 
			if (empty($nav))
				$nav = "menu-custom";
			else
				$nav .= " menu-custom";
		}
		if (!empty($pines->config->tpl_bootstrap->brand_css)) {
			// Custom Brand CSS 
			if (empty($nav))
				$nav = "brand-custom";
			else
				$nav .= " brand-custom";
		}
		if (!empty($pines->config->tpl_bootstrap->nav_list_css)) {
			// Custom Navbar CSS 
			if (empty($nav))
				$nav = "nav-list-custom";
			else
				$nav .= " nav-list-custom";
		}
		if (!empty($pines->config->tpl_bootstrap->nav_bar_css)) {
			// Custom Navbar CSS 
			if (empty($nav))
				$nav = "nav-bar-custom";
			else
				$nav .= " nav-bar-custom";
		}
		
		// Footer Configuration Options
		if ($pines->template->verify_color($pines->config->tpl_bootstrap->footer_background) && $pines->template->verify_color($pines->config->tpl_bootstrap->footer_border)) {
			// footer background and footer border colors
			if (empty($footer))
				$footer = "bg-color";
			else
				$footer .= " bg-color";
		}
		if ($pines->template->verify_color($pines->config->tpl_bootstrap->footer_font_color)) {
			// footer font color
			if (empty($footer))
				$footer = "font-color";
			else
				$footer .= " font-color";
		}
		if (!empty($pines->config->tpl_bootstrap->footer_css)) {
			// footer fixed
			if (empty($footer))
				$footer = "footer-custom";
			else
				$footer .= " footer-custom";
		}
		if ($pines->config->tpl_bootstrap->footer_type == "fixed") {
			// footer fixed
			if (empty($footer))
				$footer = "footer-fixed";
			else
				$footer .= " footer-fixed";
		}// The page variable below still affects the footer so I put it in here.
		if ($pines->config->tpl_bootstrap->footer_type == "fixed") {
			// footer fixed
			if (empty($page))
				$page = "footer-fixed";
			else
				$page .= " footer-fixed";
		}
	
		// Stuff that  couldn't be added by classes (like headings)
		if (!empty($pines->config->tpl_bootstrap->headings_fontface)) { ?>
			<style type="text/css">
			h1, h2, h3, h4, h5, h6 {
				font-family: <?php echo htmlspecialchars($pines->config->tpl_bootstrap->headings_fontface); ?>;
			}
			</style>
	<?php } if (!empty($pines->config->tpl_bootstrap->headings_css)) { ?>
			<style type="text/css">
			h1, h2, h3, h4, h5, h6 {
				<?php echo htmlspecialchars($pines->config->tpl_bootstrap->headings_css); ?>;
			}
			</style>
	<?php } ?>
</head>
<body class="<?php echo $body; echo in_array('printfix', $pines->config->tpl_bootstrap->fancy_style) ? ' printfix' : ''; echo in_array('printheader', $pines->config->tpl_bootstrap->fancy_style) ? ' printheader' : ''; echo in_array('nosidegutters', $pines->config->tpl_bootstrap->fancy_style) ? ' nosidegutters' : '';?>">
	<div id="top"><?php
		echo $pines->page->render_modules('top', 'module_header');
		$error = $pines->page->get_error();
		$notice = $pines->page->get_notice();
		if ( $error || $notice ) { ?>
		<script type="text/javascript">
			pines(function(){
				<?php
				if ( $error ) { foreach ($error as $cur_item) {
					echo 'pines.error('.json_encode(htmlspecialchars($cur_item)).", \"Error\");\n";
				} }
				if ( $notice ) { foreach ($notice as $cur_item) {
					echo 'pines.notice('.json_encode(htmlspecialchars($cur_item)).", \"Notice\");\n";
				} }
				?>
			});
		</script>
		<?php
		}
	?></div>
	<div id="nav-configure" class="<?php echo $nav; ?>">
		<div id="nav" class="navbar clearfix navbar-fixed-top<?php echo $pines->config->tpl_bootstrap->alt_navbar ? ' navbar-inverse' : ''; ?>">
			<div class="navbar-inner">
				<div class="container<?php echo $width; ?>">
					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</a>
					<a class="brand" href="<?php echo htmlspecialchars($pines->config->full_location); ?>">
						<?php if ($pines->config->tpl_bootstrap->use_header_image) { ?>
						<img src="<?php echo htmlspecialchars($pines->config->tpl_bootstrap->header_image); ?>" alt="<?php echo htmlspecialchars($pines->config->page_title); ?>" />
						<?php } else { ?>
							<span>
						<?php	switch ($pines->config->tpl_bootstrap->brand_type) {
								case "System Name":
									echo htmlspecialchars($pines->config->system_name);
									break;
								case "Page Title":
									echo htmlspecialchars($pines->config->page_title);
									break;
								case "Custom":
									echo htmlspecialchars($pines->config->tpl_bootstrap->brand_name);
									break;
								} ?>
							</span>	
					<?php } ?>
					</a>
					<div class="nav-collapse">
						<?php echo $pines->page->render_modules('main_menu', 'module_head'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="header">
		<div class="container<?php echo $width; ?>">
			<div class="row-fluid">
				<div class="span12 positions">
					<div id="header_position"><?php echo $pines->page->render_modules('header', 'module_header'); ?></div>
					<div id="header_right"><?php echo $pines->page->render_modules('header_right', 'module_header_right'); ?></div>
				</div>
			</div>
		</div>
	</div>
	<div id="page" class="container<?php echo $width; ?> <?php echo $page; ?>">
		<div class="row-fluid">
			<div id="breadcrumbs" class="span12"><?php echo $pines->page->render_modules('breadcrumbs', 'module_header'); ?></div>
		</div>
		<div class="row-fluid">
			<div id="pre_content" class="span12"><?php echo $pines->page->render_modules('pre_content', 'module_header'); ?></div>
		</div>
		<div id="column_container">
			<div class="row-fluid">
				<?php if (in_array($pines->config->tpl_bootstrap->variant, array('threecol', 'twocol-sideleft'))) { ?>
				<div id="left" class="span3">
					<?php echo $pines->page->render_modules('left', 'module_side'); ?>
					<?php if ($pines->config->tpl_bootstrap->variant == 'twocol-sideleft') { echo $pines->page->render_modules('right', 'module_side'); } ?>&nbsp;
				</div>
				<?php } ?>
				<div class="<?php echo $pines->config->tpl_bootstrap->variant == 'full-page' ? 'span12' : ($pines->config->tpl_bootstrap->variant == 'threecol' ? 'span6' : 'span9'); ?>">
					<div id="content_container">
						<div class="row-fluid">
							<div id="content_top_left" class="span6"><?php echo $pines->page->render_modules('content_top_left'); ?></div>
							<div id="content_top_right" class="span6"><?php echo $pines->page->render_modules('content_top_right'); ?></div>
						</div>
						<div id="content"><?php echo $pines->page->render_modules('content', 'module_content'); ?></div>
						<div class="row-fluid">
							<div id="content_bottom_left" class="span6"><?php echo $pines->page->render_modules('content_bottom_left'); ?></div>
							<div id="content_bottom_right" class="span6"><?php echo $pines->page->render_modules('content_bottom_right'); ?></div>
						</div>
					</div>
				</div>
				<?php if (in_array($pines->config->tpl_bootstrap->variant, array('threecol', 'twocol-sideright'))) { ?>
				<div id="right" class="span3">
					<?php if ($pines->config->tpl_bootstrap->variant == 'twocol-sideright') { echo $pines->page->render_modules('left', 'module_side'); } ?>
					<?php echo $pines->page->render_modules('right', 'module_side'); ?>&nbsp;
				</div>
				<?php } ?>
			</div>
		</div>
		<div class="row-fluid">
			<div id="post_content" class="span12"><?php echo $pines->page->render_modules('post_content', 'module_header'); ?></div>
		</div>
	</div>
	<div id="footer" class="clearfix <?php echo $footer;?>">
		<div class="container<?php echo $width; ?>">
			<div class="row-fluid">
				<div class="span12 positions">
					<div id="footer_position"><?php echo $pines->page->render_modules('footer', 'module_header'); ?></div>
					<p id="copyright"><?php echo htmlspecialchars($pines->config->copyright_notice, ENT_COMPAT, '', false); ?></p>
				</div>
			</div>
		</div>
	</div>
	<div id="bottom"><?php echo $pines->page->render_modules('bottom', 'module_header'); ?></div>
</body>
</html>