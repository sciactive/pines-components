<?php
/**
 * Main page of the Pines template.
 *
 * The page which is output to the user is built using this file.
 *
 * @package Pines
 * @subpackage tpl_pines
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
// Experimental AJAX code.
if ($pines->config->tpl_pines->ajax && ($_REQUEST['tpl_pines_ajax'] == 1 || strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
	$return = array(
		'notices' => $pines->page->get_notice(),
		'errors' => $pines->page->get_error(),
		'main_menu' => $pines->page->render_modules('main_menu', 'module_head'),
		'pos_head' => $pines->page->render_modules('head', 'module_head'),
		'pos_top' => $pines->page->render_modules('top', 'module_header'),
		'pos_header' => $pines->page->render_modules('header', 'module_header'),
		'pos_header_right' => $pines->page->render_modules('header_right', 'module_header_right'),
		'pos_pre_content' => $pines->page->render_modules('pre_content', 'module_header'),
		'pos_content_top_left' => $pines->page->render_modules('content_top_left'),
		'pos_content_top_right' => $pines->page->render_modules('content_top_right'),
		'pos_content' => $pines->page->render_modules('content', 'module_content'),
		'pos_content_bottom_left' => $pines->page->render_modules('content_bottom_left'),
		'pos_content_bottom_right' => $pines->page->render_modules('content_bottom_right'),
		'pos_post_content' => $pines->page->render_modules('post_content', 'module_header'),
		'pos_left' => $pines->page->render_modules('left'),
		'pos_right' => $pines->page->render_modules('right', 'module_right'),
		'pos_footer' => $pines->page->render_modules('footer', 'module_header'),
		'pos_bottom' => $pines->page->render_modules('bottom', 'module_header')
	);
	echo json_encode($return);
	return;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php echo $pines->page->get_title(); ?></title>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
	<link rel="icon" type="image/vnd.microsoft.icon" href="<?php echo $pines->config->rela_location; ?>favicon.ico" />

	<link href="<?php echo $pines->config->rela_location; ?>system/css/pform.css" media="all" rel="stylesheet" type="text/css" />
	<!--[if lt IE 8]>
	<link href="<?php echo $pines->config->rela_location; ?>system/css/pform-ie-lt-8.css" media="all" rel="stylesheet" type="text/css" />
	<![endif]-->

	<link href="<?php echo $pines->config->rela_location; ?>templates/<?php echo $pines->current_template; ?>/css/pines.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo $pines->config->rela_location; ?>templates/<?php echo $pines->current_template; ?>/css/print.css" media="print" rel="stylesheet" type="text/css" />

	<link href="<?php echo $pines->config->rela_location; ?>templates/<?php echo $pines->current_template; ?>/css/dropdown/dropdown.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo $pines->config->rela_location; ?>templates/<?php echo $pines->current_template; ?>/css/dropdown/dropdown.vertical.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo $pines->config->rela_location; ?>templates/<?php echo $pines->current_template; ?>/css/dropdown/themes/jqueryui/jqueryui.css" media="all" rel="stylesheet" type="text/css" />

	<script type="text/javascript" src="<?php echo $pines->config->rela_location; ?>system/js/js.php"></script>
	<?php if ($pines->config->tpl_pines->menu_delay) { ?>
	<script type="text/javascript">// <![CDATA[
		pines.tpl_pines_menu_delay = true;
	// ]]></script>
	<?php } ?>
	<script type="text/javascript" src="<?php echo $pines->config->rela_location; ?>templates/<?php echo $pines->current_template; ?>/js/template.js"></script>
	<?php if ($pines->config->tpl_pines->ajax) { ?>
	<script type="text/javascript" src="<?php echo $pines->config->rela_location; ?>templates/<?php echo $pines->current_template; ?>/js/ajax.js"></script>
	<?php } ?>

	<!--[if lt IE 7]>
	<script type="text/javascript" src="<?php echo $pines->config->rela_location; ?>templates/<?php echo $pines->current_template; ?>/js/jquery/jquery.dropdown.js"></script>
	<![endif]-->

	<?php echo $pines->page->render_modules('head', 'module_head'); ?>
</head>
<body class="ui-widget ui-widget-content">
	<div id="top">
		<?php echo $pines->page->render_modules('top', 'module_header');
		$error = $pines->page->get_error();
		$notice = $pines->page->get_notice();
		if ( $error || $notice ) { ?>
		<script type="text/javascript">
			// <![CDATA[
			pines(function(){
				<?php
				if ( $error ) { foreach ($error as $cur_item) {
					echo 'pines.error("'.addslashes($cur_item)."\", \"Error\");\n";
				} }
				if ( $notice ) { foreach ($notice as $cur_item) {
					echo 'pines.notice("'.addslashes($cur_item)."\", \"Notice\");\n";
				} }
				?>
			});
			// ]]>
		</script>
		<?php } ?>
	</div>
	<div id="header" class="ui-widget-header">
		<h1 class="pagetitle">
			<a href="<?php echo $pines->config->full_location; ?>">
				<?php if ($pines->config->tpl_pines->use_header_image) { ?>
				<img src="<?php echo $pines->config->tpl_pines->header_image; ?>" alt="<?php echo $pines->config->page_title; ?>" />
				<?php } else { ?>
				<span><?php echo $pines->config->page_title; ?></span>
				<?php } ?>
			</a>
		</h1>
		<?php echo $pines->page->render_modules('header', 'module_header'); ?>
		<?php echo $pines->page->render_modules('header_right', 'module_header_right'); ?>
		<div class="menuwrap ui-widget-content">
			<div id="main_menu"><?php echo $pines->page->render_modules('main_menu', 'module_head'); ?></div>
		</div>
	</div>
	<div id="pre_content">
		<?php echo $pines->page->render_modules('pre_content', 'module_header'); ?>
	</div>
	<div class="colmask holygrail">
		<div class="colmid">
			<div class="colleft">
				<div class="colleftcolor ui-state-default ui-state-disabled"></div>
				<div class="col1wrap">
					<div class="col1">
						<div id="content_top_left">
							<?php echo $pines->page->render_modules('content_top_left'); ?>
						</div>
						<div id="content_top_right">
							<?php echo $pines->page->render_modules('content_top_right'); ?>
						</div>
						<div id="content">
							<?php echo $pines->page->render_modules('content', 'module_content'); ?>
						</div>
						<div id="content_bottom_left">
							<?php echo $pines->page->render_modules('content_bottom_left'); ?>
						</div>
						<div id="content_bottom_right">
							<?php echo $pines->page->render_modules('content_bottom_right'); ?>
						</div>
					</div>
				</div>
				<div id="left" class="col2">
					<?php echo $pines->page->render_modules('left', 'module_left'); ?>
				</div>
				<div id="right" class="col3">
					<?php echo $pines->page->render_modules('right', 'module_right'); ?>
				</div>
			</div>
		</div>
	</div>
	<div id="post_content">
		<?php echo $pines->page->render_modules('post_content', 'module_header'); ?>
	</div>
	<div id="footer" class="ui-widget-header">
		<div class="modules">
			<?php echo $pines->page->render_modules('footer', 'module_header'); ?>
		</div>
		<p class="copyright">
			<?php echo $pines->config->copyright_notice; ?>
		</p>
	</div>
	<div id="bottom">
		<?php echo $pines->page->render_modules('bottom', 'module_header'); ?>
	</div>
</body>
</html>