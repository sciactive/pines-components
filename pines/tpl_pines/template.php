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
/* Experimental AJAX code.
if (strpos($_SERVER["HTTP_ACCEPT"], 'application/json') !== false) {
	$return = array(
		'notices' => $pines->page->get_notice(),
		'errors' => $pines->page->get_error(),
		'main_menu' => $pines->page->main_menu->render(array('<ul class="dropdown dropdown-horizontal">', '</ul>'),
			array('<li class="ui-state-default">', '</li>'),
			array('<ul>', '</ul>'),
			array('<li class="ui-state-default">', '</li>'), '<a href="#DATA#">#NAME#</a>', ''),
		'head' => $pines->page->render_modules('head', 'module_head'),
		'top' => $pines->page->render_modules('top', 'module_header'),
		'header' => $pines->page->render_modules('header', 'module_header'),
		'header_right' => $pines->page->render_modules('header_right', 'module_header_right'),
		'user1' => $pines->page->render_modules('user1'),
		'user2' => $pines->page->render_modules('user2'),
		'content' => $pines->page->render_modules('content', 'module_content'),
		'user3' => $pines->page->render_modules('user3'),
		'user4' => $pines->page->render_modules('user4'),
		'left' => $pines->page->render_modules('left'),
		'right' => $pines->page->render_modules('right', 'module_right'),
		'footer' => $pines->page->render_modules('footer', 'module_header'),
		'bottom' => $pines->page->render_modules('bottom', 'module_header')
	);
	echo json_encode($return);
	return;
}
*/
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
	<!--[if lt IE 7]>
	<link href="<?php echo $pines->config->rela_location; ?>system/css/pform-ie-lt-7.css" media="all" rel="stylesheet" type="text/css" />
	<![endif]-->

	<link href="<?php echo $pines->config->rela_location; ?>templates/<?php echo $pines->current_template; ?>/css/pines.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo $pines->config->rela_location; ?>templates/<?php echo $pines->current_template; ?>/css/print.css" media="print" rel="stylesheet" type="text/css" />
<?php if ($pines->config->tpl_pines->header_image) { ?>
	<link href="<?php echo $pines->config->rela_location; ?>templates/<?php echo $pines->current_template; ?>/css/header-image.css" media="all" rel="stylesheet" type="text/css" />
<?php } ?>

	<link href="<?php echo $pines->config->rela_location; ?>templates/<?php echo $pines->current_template; ?>/css/dropdown/dropdown.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo $pines->config->rela_location; ?>templates/<?php echo $pines->current_template; ?>/css/dropdown/dropdown.vertical.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo $pines->config->rela_location; ?>templates/<?php echo $pines->current_template; ?>/css/dropdown/themes/jqueryui/jqueryui.css" media="all" rel="stylesheet" type="text/css" />

	<script type="text/javascript" src="<?php echo $pines->config->rela_location; ?>system/js/js.php"></script>

	<!--[if lt IE 7]>
	<script type="text/javascript" src="<?php echo $pines->config->rela_location; ?>templates/<?php echo $pines->current_template; ?>/js/jquery/jquery.dropdown.js"></script>
	<![endif]-->

	<?php echo $pines->page->render_modules('head', 'module_head'); ?>

	<script type="text/javascript" src="<?php echo $pines->config->rela_location; ?>templates/<?php echo $pines->current_template; ?>/js/template.js"></script>
</head>
<body class="ui-widget-content">
	<div id="top">
		<?php echo $pines->page->render_modules('top', 'module_header');
		$error = $pines->page->get_error();
		$notice = $pines->page->get_notice();
		if ( $error || $notice ) { ?>
		<div class="notices">
			<?php if ( $error ) { ?>
			<div class="notice ui-state-error ui-corner-all ui-helper-clearfix">
				<?php foreach ($error as $cur_item) {
					echo "<p class=\"entry\"><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: 0.3em;\"></span><span class=\"text\">$cur_item</span></p>\n";
				} ?>
			</div>
			<?php }  if ( $notice ) { ?>
			<div class="notice ui-state-highlight ui-corner-all ui-helper-clearfix">
				<?php foreach ($notice as $cur_item) {
					echo "<p class=\"entry\"><span class=\"ui-icon ui-icon-info\" style=\"float: left; margin-right: 0.3em;\"></span><span class=\"text\">$cur_item</span></p>\n";
				} ?>
			</div>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
	<div id="header" class="ui-widget-header">
		<div class="pagetitle">
			<h1><a href="<?php echo $pines->config->full_location; ?>"><span><?php echo $pines->config->option_title; ?></span></a></h1>
		</div>
		<?php echo $pines->page->render_modules('header', 'module_header'); ?>
		<?php echo $pines->page->render_modules('header_right', 'module_header_right'); ?>
		<div class="mainmenu ui-widget-content">
			<div class="menuwrap"><?php echo $pines->page->render_modules('main_menu', 'module_head'); ?></div>
		</div>
	</div>
	<div class="pre_content">
		<?php echo $pines->page->render_modules('pre_content', 'module_header'); ?>
	</div>
	<div class="colmask holygrail">
		<div class="colmid">
			<div class="colleft ui-state-default">
				<div class="col1wrap">
					<div class="col1">
						<div class="content_top_left">
							<?php echo $pines->page->render_modules('content_top_left'); ?>
						</div>
						<div class="content_top_right">
							<?php echo $pines->page->render_modules('content_top_right'); ?>
						</div>
						<div class="content">
							<?php echo $pines->page->render_modules('content', 'module_content'); ?>
						</div>
						<div class="content_bottom_left">
							<?php echo $pines->page->render_modules('content_bottom_left'); ?>
						</div>
						<div class="content_bottom_right">
							<?php echo $pines->page->render_modules('content_bottom_right'); ?>
						</div>
					</div>
				</div>
				<div class="col2">
					<?php echo $pines->page->render_modules('left'); ?>
				</div>
				<div class="col3">
					<?php echo $pines->page->render_modules('right', 'module_right'); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="post_content">
		<?php echo $pines->page->render_modules('post_content', 'module_header'); ?>
	</div>
	<div id="footer" class="ui-widget-header">
		<?php echo $pines->page->render_modules('footer', 'module_header'); ?>
		<p class="copyright">
			<?php echo $pines->config->option_copyright_notice; ?>
		</p>
	</div>
	<div id="bottom">
		<?php echo $pines->page->render_modules('bottom', 'module_header'); ?>
	</div>
</body>
</html>