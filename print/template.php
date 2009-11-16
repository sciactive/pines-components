<?php
/**
 * Main page of the Pines template.
 *
 * The page which is output to the user is built using this file.
 *
 * @package Pines
 * @subpackage tpl_print
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title><?php echo $page->get_title(); ?></title>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
	<meta name="author" content="Hunter Perrin" />
	
	<link href="<?php echo $config->rela_location; ?>system/css/pform.css" media="all" rel="stylesheet" type="text/css" />
	<!--[if lt IE 8]>
	<link href="<?php echo $config->rela_location; ?>system/css/pform-ie-lt-8.css" media="all" rel="stylesheet" type="text/css" />
	<![endif]-->
	<!--[if lt IE 7]>
	<link href="<?php echo $config->rela_location; ?>system/css/pform-ie-lt-7.css" media="all" rel="stylesheet" type="text/css" />
	<![endif]-->

	<link href="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/css/style.css" media="all" rel="stylesheet" type="text/css" />

	<link href="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/css/dropdown/dropdown.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/css/dropdown/dropdown.vertical.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/css/dropdown/themes/jqueryui/jqueryui.css" media="all" rel="stylesheet" type="text/css" />

	<link href="<?php echo $config->rela_location; ?>system/css/jquery-ui/smoothness/jquery-ui.css" media="all" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="<?php echo $config->rela_location; ?>system/js/jquery.min.js"></script>
	<script type="text/javascript" src="<?php echo $config->rela_location; ?>system/js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/js/jquery/jquery.timers-1.1.2.js"></script>

	<!--[if lt IE 7]>
	<script type="text/javascript" src="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/js/jquery/jquery.dropdown.js"></script>

	<style media="screen" type="text/css">
	.col1 {
		width:100%;
	}
	</style>
	<![endif]-->

	<?php echo $page->get_head(); ?>
	<?php echo $page->render_modules('head', 'module_head', 'module_group_head'); ?>
</head>

<body>
	<div class="col1">
		<?php if ( count($page->get_error()) ) { ?>
		<div class="notice ui-state-error ui-corner-all ui-helper-clearfix">
				<?php
				$error = $page->get_error();
				foreach ($error as $cur_item) {
					echo "<p><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: 0.3em;\"></span><span>$cur_item</span></p>\n";
				}
				?>
		</div>
		<?php } ?>
		<?php if ( count($page->get_notice()) ) { ?>
		<div class="notice ui-state-highlight ui-corner-all ui-helper-clearfix">
				<?php
				$notice = $page->get_notice();
				foreach ($notice as $cur_item) {
					echo "<p><span class=\"ui-icon ui-icon-info\" style=\"float: left; margin-right: 0.3em;\"></span><span>$cur_item</span></p>\n";
				}
				?>
		</div>
		<?php } ?>
		<?php echo $page->render_modules('content'); ?>
	</div>
</body>
</html>