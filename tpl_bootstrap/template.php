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

	<!--[if lt IE 7]>
	<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/js/jquery/jquery.dropdown.js"></script>
	<![endif]-->
	<?php echo $pines->page->render_modules('head', 'module_head'); ?>
	<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/css/pines.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/css/print.css" media="print" rel="stylesheet" type="text/css" />
	<?php if ($pines->config->tpl_bootstrap->mobile_menu == "adjusted") { ?>
	<style type="text/css">
		@media (max-width: 800px) {
			.nav-helper {
				float:right !important;
				position:relative;
				cursor: pointer;
			}
			.sub-nav-helper {
				float:right !important;
				position:relative;
				cursor: pointer;
			}
			#nav ul.nav > li.dropdown > a {
				float:left !important;
				width: 60% !important;
				position:relative;
				clear:both;
			}
			#nav ul.dropdown-menu {
				width: 60% !important;
			}
			#nav ul.dropdown-menu a, ul.dropdown-menu > li.dropdown-submenu {
				clear:both !important;
			}
			
			#nav li.dropdown-submenu > a {
				float: left !important;
				position:relative;
			}
			
			#nav li.dropdown-submenu > a:after {
				display:none;
			}
			
		}
	</style>
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
		<?php if ($pines->config->tpl_bootstrap->navbar_trigger == "hover") { ?>
		pines(function(){
			$('#nav').on('mouseenter', 'ul.nav > li.dropdown > a', function(){
				var item = $(this);
				item.siblings('ul.dropdown-menu').addClass('dropdown-opened');
				if (!item.parent().hasClass('open'))
					item.dropdown('toggle');
				setTimeout(function(){
					item.siblings('ul.dropdown-menu').removeClass('dropdown-opened');
				}, 1000)
			}).on('mouseleave', 'ul.nav', function(){
				if ($(this).find('.dropdown-opened').length < 1)
					$(this).find('li.dropdown.open > a').dropdown('toggle');
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
			}, 1000);
		<?php } ?>
	</script>
	<?php if ($pines->template->verify_color($pines->config->tpl_bootstrap->lighter_color) && $pines->template->verify_color($pines->config->tpl_bootstrap->darker_color) && $pines->template->verify_color($pines->config->tpl_bootstrap->border_color)) { ?>
	<style type="text/css">
		#nav > div.navbar-inner {
			background-color: <?php echo $pines->config->tpl_bootstrap->darker_color; ?> !important;
			background-image: -moz-linear-gradient(to bottom, <?php echo $pines->config->tpl_bootstrap->lighter_color; ?>, <?php echo $pines->config->tpl_bootstrap->darker_color; ?>) !important;
			background-image: -webkit-gradient(linear, 0 0, 0 100%, from(<?php echo $pines->config->tpl_bootstrap->lighter_color; ?>), to(<?php echo $pines->config->tpl_bootstrap->darker_color; ?>)) !important;
			background-image: -webkit-linear-gradient(to bottom, <?php echo $pines->config->tpl_bootstrap->lighter_color; ?>, <?php echo $pines->config->tpl_bootstrap->darker_color; ?>) !important;
			background-image: -o-linear-gradient(to bottom, <?php echo $pines->config->tpl_bootstrap->lighter_color; ?>, <?php echo $pines->config->tpl_bootstrap->darker_color; ?>) !important;
			background-image: linear-gradient(to bottom, <?php echo $pines->config->tpl_bootstrap->lighter_color; ?>, <?php echo $pines->config->tpl_bootstrap->darker_color; ?>) !important;
			border-color: <?php echo $pines->config->tpl_bootstrap->border_color; ?> !important;
		}
		#nav ul.nav li > a:hover, #nav ul.nav > li.dropdown.open > a, #nav ul.nav > li.dropdown.active > a, #nav ul.nav > li.active > a {
			background-color: <?php echo $pines->config->tpl_bootstrap->darker_color; ?> !important;
		}
		
		#nav .dropdown-menu li > a:hover, .dropdown-menu li > a:focus, .dropdown-submenu:hover > a {
			background-color: <?php echo $pines->config->tpl_bootstrap->darker_color; ?> !important;
			background-image: -moz-linear-gradient(to bottom, <?php echo $pines->config->tpl_bootstrap->lighter_color; ?>, <?php echo $pines->config->tpl_bootstrap->darker_color; ?>) !important;
			background-image: -webkit-gradient(linear, 0 0, 0 100%, from(<?php echo $pines->config->tpl_bootstrap->lighter_color; ?>), to(<?php echo $pines->config->tpl_bootstrap->darker_color; ?>)) !important;
			background-image: -webkit-linear-gradient(to bottom, <?php echo $pines->config->tpl_bootstrap->lighter_color; ?>, <?php echo $pines->config->tpl_bootstrap->darker_color; ?>) !important;
			background-image: -o-linear-gradient(to bottom, <?php echo $pines->config->tpl_bootstrap->lighter_color; ?>, <?php echo $pines->config->tpl_bootstrap->darker_color; ?>) !important;
			background-image: linear-gradient(to bottom, <?php echo $pines->config->tpl_bootstrap->lighter_color; ?>, <?php echo $pines->config->tpl_bootstrap->darker_color; ?>) !important;
		}
		
		.navbar-inverse .btn-navbar {
			background-color: <?php echo $pines->config->tpl_bootstrap->darker_color; ?> !important;
			background-image: -moz-linear-gradient(to bottom, <?php echo $pines->config->tpl_bootstrap->lighter_color; ?>, <?php echo $pines->config->tpl_bootstrap->darker_color; ?>) !important;
			background-image: -webkit-gradient(linear, 0 0, 0 100%, from(<?php echo $pines->config->tpl_bootstrap->lighter_color; ?>), to(<?php echo $pines->config->tpl_bootstrap->darker_color; ?>)) !important;
			background-image: -webkit-linear-gradient(to bottom, <?php echo $pines->config->tpl_bootstrap->lighter_color; ?>, <?php echo $pines->config->tpl_bootstrap->darker_color; ?>) !important;
			background-image: -o-linear-gradient(to bottom, <?php echo $pines->config->tpl_bootstrap->lighter_color; ?>, <?php echo $pines->config->tpl_bootstrap->darker_color; ?>) !important;
			background-image: linear-gradient(to bottom, <?php echo $pines->config->tpl_bootstrap->lighter_color; ?>, <?php echo $pines->config->tpl_bootstrap->darker_color; ?>) !important;
			border-color: <?php echo $pines->config->tpl_bootstrap->border_color; ?> !important;
		}
	</style>	
	<?php } if ($pines->template->verify_color($pines->config->tpl_bootstrap->caret_color) && $pines->template->verify_color($pines->config->tpl_bootstrap->caret_hover_color)) { ?>
	<style type="text/css">
		#nav ul.nav > li.dropdown > .dropdown-toggle .caret {
			border-top-color: <?php echo $pines->config->tpl_bootstrap->caret_color; ?> !important;
		}
		#nav ul.nav > li.dropdown.active > .dropdown-toggle .caret {
			border-top-color: <?php echo $pines->config->tpl_bootstrap->caret_hover_color; ?> !important;
		}
		#nav ul.nav > li.dropdown.open > a:hover > .caret, ul.nav > li.dropdown.open > a:focus > .caret, #nav ul.nav > li.dropdown.open > a > .caret {
			border-top-color: <?php echo $pines->config->tpl_bootstrap->caret_hover_color; ?> !important;
		}
	</style>
	<?php } if ($pines->template->verify_color($pines->config->tpl_bootstrap->brand_color) && $pines->template->verify_color($pines->config->tpl_bootstrap->brand_hover_color)) { ?>
	<style type="text/css">
		#nav a.brand {
			color: <?php echo $pines->config->tpl_bootstrap->brand_color; ?> !important;
		}
		#nav a.brand:hover {
			color: <?php echo $pines->config->tpl_bootstrap->brand_hover_color; ?> !important;
		}
	</style>
	<script type="text/javascript">
		pines(function(){
			// Get brand color text shadow.
			var brand_color = "<?php echo $pines->config->tpl_bootstrap->brand_color; ?>";
			var text_shadow = get_text_shadow(brand_color)
			$('#nav a.brand').css('text-shadow', text_shadow);
		});
	</script>
	<?php } if ($pines->template->verify_color($pines->config->tpl_bootstrap->font_color) && $pines->template->verify_color($pines->config->tpl_bootstrap->font_hover_color)) { ?>
	<style type="text/css">
		#nav ul.nav > li.dropdown > a, #nav ul.nav > li > a {
			color: <?php echo $pines->config->tpl_bootstrap->font_color; ?> !important;
			text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
		}
		#nav .nav-collapse.collapse .nav > li > a, #nav .nav-collapse.collapse .dropdown-menu a {
			color: <?php echo $pines->config->tpl_bootstrap->font_color; ?> !important;
			text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
		}
		#nav .dropdown-menu li > a:hover, .dropdown-menu li > a:focus, .dropdown-submenu:hover > a {
			color: <?php echo $pines->config->tpl_bootstrap->font_hover_color; ?> !important;
		}
		#nav.navbar .btn-navbar .icon-bar {
			background-color: <?php echo $pines->config->tpl_bootstrap->font_color; ?> !important;
		}
	</style>
	<script type="text/javascript">
		pines(function(){
			// Get font color text shadow.
			var font_color = "<?php echo $pines->config->tpl_bootstrap->font_color; ?>";
			var text_shadow = get_text_shadow(font_color)
			$('#nav ul.nav > li.dropdown > a').css('text-shadow', text_shadow);
		});
	</script>
	<?php } if ($pines->config->tpl_bootstrap->navbar_menu_height > 0) { ?>
	<style type="text/css">
		.navbar-inner {
			min-height: navbar_menu_height;
		}
	</style>
	<script type="text/javascript">
		pines(function(){
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
			
			console.log(navbar_menu_height);
		});
	</script>
	<?php } if ($pines->template->verify_color($pines->config->tpl_bootstrap->footer_background) && $pines->template->verify_color($pines->config->tpl_bootstrap->footer_border)) { ?>
	<style type="text/css">
		#footer {
			background-color: <?php echo $pines->config->tpl_bootstrap->footer_background; ?> !important;
			border-color: <?php echo $pines->config->tpl_bootstrap->footer_border; ?> !important;
		}
	</style>
	<?php } if ($pines->template->verify_color($pines->config->tpl_bootstrap->footer_font_color)) { ?>
	<style type="text/css">
		#footer {
			color: <?php echo $pines->config->tpl_bootstrap->footer_font_color; ?> !important;
		}
	</style>
	<?php } if ($pines->config->tpl_bootstrap->footer_type == "fixed") { ?>
	<style type="text/css">
		#footer {
			position: fixed !important;
			bottom: 0 !important;
		}
		#page {
			margin-bottom: 4em;
		}
		
		@media (max-width: 800px) {
		#footer {
			padding: 0 20px;
			margin-left: -20px;
			position: relative !important;
		}
	}
	</style>
	<?php } ?>
</head>
<body class="<?php echo in_array('printfix', $pines->config->tpl_bootstrap->fancy_style) ? ' printfix' : ''; echo in_array('printheader', $pines->config->tpl_bootstrap->fancy_style) ? ' printheader' : ''; echo in_array('nosidegutters', $pines->config->tpl_bootstrap->fancy_style) ? ' nosidegutters' : ''; ?>">
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
					<span><?php echo htmlspecialchars($pines->config->page_title); ?></span>
					<?php } ?>
				</a>
				<div class="nav-collapse">
					<?php echo $pines->page->render_modules('main_menu', 'module_head'); ?>
				</div>
			</div>
		</div>
	</div>
	<div id="header" class="well well-large">
		<div class="container<?php echo $width; ?>">
			<div class="row-fluid">
				<div class="span12 positions">
					<div id="header_position"><?php echo $pines->page->render_modules('header', 'module_header'); ?></div>
					<div id="header_right"><?php echo $pines->page->render_modules('header_right', 'module_header_right'); ?></div>
				</div>
			</div>
		</div>
	</div>
	<div id="page" class="container<?php echo $width; ?>">
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
	<div id="footer" class="well">
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