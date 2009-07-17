<?php
/**
 * Main page of the XROOM template.
 *
 * The page which is output to the user is built using this file.
 *
 * @package XROOM
 * @subpackage tpl_xroom
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('D_RUN') or die('Direct access prohibited');

echo '<?xml version="1.0" encoding="UTF-8" standalone="no" ?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title><?php echo $page->get_title(); ?></title>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
	<meta name="author" content="Hunter Perrin" />
	<link href="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/css/xroom.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/css/dropdown/dropdown.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/css/dropdown/dropdown.vertical.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/css/dropdown/themes/default/default.css" media="all" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/js/jquery/jquery.js"></script>
	<script type="text/javascript" src="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/js/jquery/jquery.timers-1.1.2.js"></script>
	<script type="text/javascript" src="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/js/template.js"></script>

	<!--[if lt IE 7]>
	<script type="text/javascript" src="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/js/jquery/jquery.dropdown.js"></script>
	<![endif]-->
	<?php echo $page->get_head(); ?>
</head>

<body>

<div id="content_area">
	<div class="pagetitle">
	<span><?php echo $page->get_title(); ?></span>
	</div>
	<?php
	$new_menu = $page->main_menu->render();
	if ( !empty($new_menu) )
		echo "<div class=\"mainmenu\">\n$new_menu\n</div>\n";
	?>
	<div style="clear: both; height: 1px;">&nbsp;</div>
	<?php if ( isset($page->modules['header']) ) {?>
	<div class="mainpage">
		<?php
		foreach ($page->modules['header'] as $cur_module) {
			echo $cur_module->render() . "\n";
		}
		?>
	</div>
	<?php } if ( count($page->get_error()) ) { ?>
	<div class="notice error"><span class="close">[X] Close</span>
	<?php
	$error = $page->get_error();
	foreach ($error as $cur_item) {
		echo "$cur_item<br />\n";
	}
	?>
	</div>
	<?php } if ( count($page->get_notice()) ) { ?>
	<div class="notice"><span class="close">[x] Close</span>
	<?php
	$notice = $page->get_notice();
	foreach ($notice as $cur_item) {
		echo "$cur_item<br />\n";
	}
	?>
	</div>
	<?php } ?>
	<div id="wrapper">
		<?php if ( isset($page->modules['content']) ) {?>
		<div class="content middle">
			<?php
			echo $page->get_content();
			foreach ($page->modules['content'] as $cur_module) {
				echo $cur_module->render() . "\n";
			}
			?>
		</div>
		<?php } if ( isset($page->modules['left']) ) {?>
		<div class="content left">
			<?php
			echo $page->get_content();
			foreach ($page->modules['left'] as $cur_module) {
				echo $cur_module->render() . "\n";
			}
			?>
		</div>
		<?php } if ( isset($page->modules['right']) ) {?>
		<div class="content right">
			<?php
			echo $page->get_content();
			foreach ($page->modules['right'] as $cur_module) {
				echo $cur_module->render() . "\n";
			}
			?>
		</div>
		<?php } ?>
		<div style="clear: both; height: 1px;">&nbsp;</div>
	</div>
	<?php if ( isset($page->modules['footer']) ) {?>
	<div class="footer">
		<?php
		foreach ($page->modules['footer'] as $cur_module) {
			echo $cur_module->render() . "\n";
		}
		?>
	</div>
	<?php } ?>
	<div style="text-align: center;"><small>
	<?php echo $config->option_copyright_notice; ?>
	</small></div>
</div>

</body>
</html>