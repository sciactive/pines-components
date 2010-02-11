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
	<title><?php echo $pines->page->get_title(); ?></title>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
	<link rel="icon" type="image/vnd.microsoft.icon" href="<?php echo $pines->rela_location; ?>favicon.ico" />

	<link href="<?php echo $pines->rela_location; ?>system/css/pform.css" media="all" rel="stylesheet" type="text/css" />
	<!--[if lt IE 8]>
	<link href="<?php echo $pines->rela_location; ?>system/css/pform-ie-lt-8.css" media="all" rel="stylesheet" type="text/css" />
	<![endif]-->
	<!--[if lt IE 7]>
	<link href="<?php echo $pines->rela_location; ?>system/css/pform-ie-lt-7.css" media="all" rel="stylesheet" type="text/css" />
	<![endif]-->
	<link href="<?php echo $pines->rela_location; ?>system/css/jquery.pnotify.default.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo $pines->rela_location; ?>system/css/jquery.pnotify.default.icons.css" media="all" rel="stylesheet" type="text/css" />

	<link href="<?php echo $pines->rela_location; ?>templates/<?php echo $pines->current_template; ?>/css/pines.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo $pines->rela_location; ?>templates/<?php echo $pines->current_template; ?>/css/print.css" media="print" rel="stylesheet" type="text/css" />
<?php if ($pines->tpl_pines->header_image) { ?>
	<link href="<?php echo $pines->rela_location; ?>templates/<?php echo $pines->current_template; ?>/css/header-image.css" media="all" rel="stylesheet" type="text/css" />
<?php } ?>

	<link href="<?php echo $pines->rela_location; ?>templates/<?php echo $pines->current_template; ?>/css/dropdown/dropdown.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo $pines->rela_location; ?>templates/<?php echo $pines->current_template; ?>/css/dropdown/dropdown.vertical.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo $pines->rela_location; ?>templates/<?php echo $pines->current_template; ?>/css/dropdown/themes/jqueryui/jqueryui.css" media="all" rel="stylesheet" type="text/css" />

<?php if ($pines->tpl_pines->google_cdn) { ?>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js"></script>
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js"></script>
	<script type="text/javascript" src="<?php echo $pines->rela_location; ?>system/js/js.php?exclude=jquery.min.js+jquery-ui.min.js"></script>
<?php } else { ?>
	<script type="text/javascript" src="<?php echo $pines->rela_location; ?>system/js/js.php"></script>
<?php } ?>
	<script type="text/javascript" src="<?php echo $pines->rela_location; ?>templates/<?php echo $pines->current_template; ?>/js/template.js"></script>
	
<?php if ($pines->tpl_pines->theme_switcher) {
	if ($pines->tpl_pines->google_cdn) { ?>
	<link type="text/css" rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/base/ui.all.css" />
<?php } else { ?>
	<link type="text/css" rel="stylesheet" href="http://jqueryui.com/themes/base/ui.all.css" />
<?php } ?>
	<script type="text/javascript" src="<?php echo $pines->rela_location; ?>templates/<?php echo $pines->current_template; ?>/js/jquery/jquery.themeswitcher.js"></script>
	<script type="text/javascript">
		// <![CDATA[
		$(function(){
			if (!($.browser.msie && $.browser.version == "6.0"))
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
<?php } else {
	if ($pines->tpl_pines->google_cdn) { ?>
	<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/<?php echo $pines->tpl_pines->theme; ?>/jquery-ui.css" media="all" rel="stylesheet" type="text/css" />
<?php } else { ?>
	<link href="<?php echo $pines->rela_location; ?>system/css/jquery-ui/<?php echo $pines->tpl_pines->theme; ?>/jquery-ui.css" media="all" rel="stylesheet" type="text/css" />
<?php } } ?>

	<!--[if lt IE 7]>
	<script type="text/javascript" src="<?php echo $pines->rela_location; ?>templates/<?php echo $pines->current_template; ?>/js/jquery/jquery.dropdown.js"></script>
	<![endif]-->

	<?php echo $pines->page->render_modules('head', 'module_head'); ?>
</head>

<body class="ui-widget-content">

	<div id="top">
		<?php echo $pines->page->render_modules('top', 'module_header'); ?>
	</div>
	<?php if ( $pines->tpl_pines->theme_switcher ) { ?>
	<div id="switcher"></div>
	<?php } ?>
	<div id="header" class="ui-widget-header">
		<div class="pagetitle">
			<h1><a href="<?php echo $pines->full_location; ?>"><span><?php echo $pines->option_title; ?></span></a></h1>
		</div>
		<?php echo $pines->page->render_modules('header', 'module_header'); ?>
		<?php echo $pines->page->render_modules('header_right', 'module_header_right'); ?>
		<?php
		$cur_menu = $pines->page->main_menu->render(array('<ul class="dropdown dropdown-horizontal">', '</ul>'),
			array('<li class="ui-state-default">', '</li>'),
			array('<ul>', '</ul>'),
			array('<li class="ui-state-default">', '</li>'), '<a href="#DATA#">#NAME#</a>', '');
		if ( !empty($cur_menu) )
			echo "<div class=\"mainmenu ui-widget-content\"><div class=\"menuwrap\">\n$cur_menu\n</div></div>\n";
		?>
	</div>
	<div class="colmask holygrail">
		<div class="colmid">
			<div class="colleft ui-state-default">
				<div class="col1wrap">
					<div class="col1">
						<?php if ( count($pines->page->get_error()) ) { ?>
						<div class="notice ui-state-error ui-corner-all ui-helper-clearfix"><p class="close"><span class="ui-icon ui-icon-circle-close"></span></p>
								<?php
								$error = $pines->page->get_error();
								foreach ($error as $cur_item) {
									echo "<p class=\"entry\"><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: 0.3em;\"></span><span class=\"text\">$cur_item</span></p>\n";
								}
								?>
						</div>
						<?php } ?>
						<?php if ( count($pines->page->get_notice()) ) { ?>
						<div class="notice ui-state-highlight ui-corner-all ui-helper-clearfix"><p class="close"><span class="ui-icon ui-icon-circle-close"></span></p>
								<?php
								$notice = $pines->page->get_notice();
								foreach ($notice as $cur_item) {
									echo "<p class=\"entry\"><span class=\"ui-icon ui-icon-info\" style=\"float: left; margin-right: 0.3em;\"></span><span class=\"text\">$cur_item</span></p>\n";
								}
								?>
						</div>
						<?php } ?>
						<div class="user1">
							<?php echo $pines->page->render_modules('user1'); ?>
						</div>
						<div class="user2">
							<?php echo $pines->page->render_modules('user2'); ?>
						</div>
						<div class="content">
							<?php echo $pines->page->render_modules('content', 'module_content'); ?>
						</div>
						<div class="user3">
							<?php echo $pines->page->render_modules('user3'); ?>
						</div>
						<div class="user4">
							<?php echo $pines->page->render_modules('user4'); ?>
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
	<div id="footer" class="ui-widget-header">
		<?php echo $pines->page->render_modules('footer', 'module_header'); ?>
		<p class="copyright">
			<?php echo $pines->option_copyright_notice; ?>
		</p>
	</div>
	<div id="bottom">
		<?php echo $pines->page->render_modules('bottom', 'module_header'); ?>
	</div>

</body>
</html>
