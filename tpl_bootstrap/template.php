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
$pines->com_bootstrap->load_js_css();
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
	<?php if (!empty($pines->config->tpl_bootstrap->custom_ie_code)) {
		echo $pines->config->tpl_bootstrap->custom_ie_code;
	}  echo $pines->page->render_modules('head', 'module_head'); ?>
	<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/css/pines.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/css/print.css" media="print" rel="stylesheet" type="text/css" />
	<?php if (!empty($pines->config->tpl_bootstrap->font_folder)) { ?>
	<link href="<?php echo htmlspecialchars($pines->config->tpl_bootstrap->font_folder); ?>stylesheet.css" media="all" rel="stylesheet" type="text/css" />
	<?php } 
	if (!empty($pines->config->tpl_bootstrap->link_css)) {
		$link_css = explode(',', $pines->config->tpl_bootstrap->link_css);
		foreach ($link_css as $cur_link) {
			echo '<link type="text/css" rel="stylesheet" href="'.htmlspecialchars($pines->config->location).$cur_link.'" />';
		}
	}
	?>
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
	} ?>
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
		<div id="nav" class="navbar clearfix <?php echo $pines->config->tpl_bootstrap->navbar_fixed ? 'navbar-fixed-top' : ''; ?> <?php echo $pines->config->tpl_bootstrap->alt_navbar ? 'navbar-inverse' : ''; ?>">
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