<?php
/**
 * Main page of the Print template.
 *
 * The page which is output to the user is built using this file.
 *
 * @package Pines
 * @subpackage tpl_print
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title><?php echo htmlspecialchars($pines->page->get_title()); ?></title>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
	<link rel="icon" type="image/vnd.microsoft.icon" href="<?php echo htmlspecialchars($pines->config->rela_location); ?>favicon.ico" />

	<link href="<?php echo htmlspecialchars($pines->config->rela_location); ?>system/css/pform.css" media="all" rel="stylesheet" type="text/css" />
	<!--[if lt IE 8]>
	<link href="<?php echo htmlspecialchars($pines->config->rela_location); ?>system/css/pform-ie-lt-8.css" media="all" rel="stylesheet" type="text/css" />
	<![endif]-->

	<link href="<?php echo htmlspecialchars($pines->config->rela_location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/css/style.css" media="all" rel="stylesheet" type="text/css" />

	<link href="<?php echo htmlspecialchars($pines->config->rela_location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/css/dropdown/dropdown.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo htmlspecialchars($pines->config->rela_location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/css/dropdown/dropdown.vertical.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo htmlspecialchars($pines->config->rela_location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/css/dropdown/themes/jqueryui/jqueryui.css" media="all" rel="stylesheet" type="text/css" />

	<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->rela_location); ?>system/js/js.php"></script>

	<!--[if lt IE 7]>
	<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->rela_location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/js/jquery/jquery.dropdown.js"></script>

	<style media="screen" type="text/css">
	.col1 {
		width:100%;
	}
	</style>
	<![endif]-->

	<?php echo $pines->page->render_modules('head', 'module_head'); ?>
</head>

<body class="ui-widget-content">
	<div class="col1">
		<?php if ( count($pines->page->get_error()) ) { ?>
		<div class="notice ui-state-error ui-corner-all ui-helper-clearfix">
				<?php
				$error = $pines->page->get_error();
				foreach ($error as $cur_item) {
					echo "<p><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: 0.3em;\"></span><span>".htmlspecialchars($cur_item)."</span></p>\n";
				}
				?>
		</div>
		<?php } ?>
		<?php if ( count($pines->page->get_notice()) ) { ?>
		<div class="notice ui-state-highlight ui-corner-all ui-helper-clearfix">
				<?php
				$notice = $pines->page->get_notice();
				foreach ($notice as $cur_item) {
					echo "<p><span class=\"ui-icon ui-icon-info\" style=\"float: left; margin-right: 0.3em;\"></span><span>".htmlspecialchars($cur_item)."</span></p>\n";
				}
				?>
		</div>
		<?php } ?>
		<?php echo $pines->page->render_modules('content'); ?>
	</div>
</body>
</html>