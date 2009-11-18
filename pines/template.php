<?php
/**
 * Main page of the Pines template.
 *
 * The page which is output to the user is built using this file.
 *
 * @package Pines
 * @subpackage tpl_pines
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
	<link rel="icon" type="image/vnd.microsoft.icon" href="<?php echo $config->rela_location; ?>favicon.ico" />

	<link href="<?php echo $config->rela_location; ?>system/css/pform.css" media="all" rel="stylesheet" type="text/css" />
	<!--[if lt IE 8]>
	<link href="<?php echo $config->rela_location; ?>system/css/pform-ie-lt-8.css" media="all" rel="stylesheet" type="text/css" />
	<![endif]-->
	<!--[if lt IE 7]>
	<link href="<?php echo $config->rela_location; ?>system/css/pform-ie-lt-7.css" media="all" rel="stylesheet" type="text/css" />
	<![endif]-->

	<link href="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/css/pines.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/css/print.css" media="print" rel="stylesheet" type="text/css" />
	<?php if ($config->template->header_image) { ?>
	<link href="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/css/header-image.css" media="all" rel="stylesheet" type="text/css" />
	<?php } ?>

	<link href="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/css/dropdown/dropdown.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/css/dropdown/dropdown.vertical.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/css/dropdown/themes/jqueryui/jqueryui.css" media="all" rel="stylesheet" type="text/css" />

	<script type="text/javascript" src="<?php echo $config->rela_location; ?>system/js/common.js"></script>
	<script type="text/javascript" src="<?php echo $config->rela_location; ?>system/js/jquery.min.js"></script>
	<script type="text/javascript" src="<?php echo $config->rela_location; ?>system/js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/js/jquery/jquery.timers-1.1.2.js"></script>
	<script type="text/javascript" src="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/js/template.js"></script>

	<?php if ($config->template->theme_switcher) { ?>
	<link type="text/css" rel="stylesheet" href="http://jqueryui.com/themes/base/ui.all.css" />
	<script type="text/javascript" src="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/js/jquery/jquery.themeswitcher.js"></script>
	<script type="text/javascript">
		// <![CDATA[
		$(document).ready(function(){
			$('#switcher').themeswitcher();
		});
		// ]]>
	</script>
	<style type="text/css">
		/* <![CDATA[ */
		#switcher {
			position: absolute;
			right: 20px;
			top: 20px;
		}
		/* ]]> */
	</style>
	<?php } else { ?>
	<link href="<?php echo $config->rela_location; ?>system/css/jquery-ui/<?php echo $config->template->theme; ?>/jquery-ui.css" media="all" rel="stylesheet" type="text/css" />
	<?php } ?>

	<!--[if lt IE 7]>
	<script type="text/javascript" src="<?php echo $config->rela_location; ?>templates/<?php echo $config->current_template; ?>/js/jquery/jquery.dropdown.js"></script>

	<style media="screen" type="text/css">
		.col1 {
			width:100%;
		}
	</style>
	<![endif]-->

	<?php echo $page->get_head(); ?>
	<?php echo $page->render_modules('head', 'module_head'); ?>
</head>

<body>

	<div id="wrapper">
		<?php if ( $config->template->theme_switcher ) { ?>
		<div id="switcher"></div>
		<?php } ?>
		<div id="header" class="ui-widget-header">
			<div class="pagetitle">
				<h1><a href="<?php echo $config->full_location; ?>"><span><?php echo $config->option_title; ?></span></a></h1>
			</div>
			<?php echo $page->render_modules('header'); ?>
			<?php
			$cur_menu = $page->main_menu->render(array('<ul class="dropdown dropdown-horizontal">', '</ul>'),
				array('<li class="ui-state-default" onmouseover="$(this).addClass(\'ui-state-hover\');" onmouseout="$(this).removeClass(\'ui-state-hover\');">', '</li>'),
				array('<ul>', '</ul>'),
				array('<li class="ui-state-default" onmouseover="$(this).addClass(\'ui-state-hover\');" onmouseout="$(this).removeClass(\'ui-state-hover\');">', '</li>'), '<a href="#DATA#">#NAME#</a>', '');
			if ( !empty($cur_menu) )
				echo "<div class=\"mainmenu ui-widget-content\"><div class=\"menuwrap\">\n$cur_menu\n</div></div>\n";
			?>
		</div>
		<div class="colmask holygrail ui-helper-clearfix">
			<div class="colmid">
				<div class="colleft ui-state-default">
					<div class="col1wrap">
						<div class="col1">
							<?php //TODO: Notice and error jQuery plugin (like growl). ?>
							<?php if ( count($page->get_error()) ) { ?>
							<div class="notice ui-state-error ui-corner-all ui-helper-clearfix"><p class="close"><span class="ui-icon ui-icon-circle-close"></span></p>
									<?php
									$error = $page->get_error();
									foreach ($error as $cur_item) {
										echo "<p><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: 0.3em;\"></span><span>$cur_item</span></p>\n";
									}
									?>
							</div>
							<?php } ?>
							<?php if ( count($page->get_notice()) ) { ?>
							<div class="notice ui-state-highlight ui-corner-all ui-helper-clearfix"><p class="close"><span class="ui-icon ui-icon-circle-close"></span></p>
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
					</div>
					<div class="col2">
						<?php echo $page->render_modules('left'); ?>
					</div>
					<div class="col3">
						<?php echo $page->render_modules('right'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="footer" class="ui-widget-header">
		<?php echo $page->render_modules('footer'); ?>
		<p class="copyright">
			<?php echo $config->option_copyright_notice; ?>
		</p>
	</div>
	<div id="pagebg" class="ui-widget ui-widget-content">
		<div class="colleft_ghost ui-state-default"></div>
	</div>

</body>
</html>
