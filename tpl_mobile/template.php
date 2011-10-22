<?php
/**
 * Main page of the Mobile Pines template.
 *
 * The page which is output to the user is built using this file.
 *
 * @package Pines
 * @subpackage tpl_mobile
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
header('Content-Type: text/html');

$menu = $pines->page->render_modules('main_menu', 'module_head');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title><?php echo htmlspecialchars($pines->page->get_title()); ?></title>
	<meta name="HandheldFriendly" content="true" />
	<meta name="viewport" content="width=320, initial-scale=1, maximum-scale=1, user-scalable=no" />
	<link rel="icon" type="image/vnd.microsoft.icon" href="<?php echo htmlspecialchars($pines->config->location); ?>favicon.ico" />

	<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/css/pines.css" media="all" rel="stylesheet" type="text/css" />

	<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->rela_location); ?>system/includes/js.php"></script>
	<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/js/template.js"></script>

	<?php echo $pines->page->render_modules('head', 'module_head'); ?>
</head>
<body class="ui-widget ui-widget-content">
<div id="page">
	<div id="top"><?php
		echo $pines->page->render_modules('top', 'module_header');
		$error = $pines->page->get_error();
		$notice = $pines->page->get_notice();
		if ( $error || $notice ) { ?>
		<script type="text/javascript">
			// <![CDATA[
			pines(function(){
				<?php
				if ( $error ) { foreach ($error as $cur_item) {
					echo 'pines.error('.json_encode($cur_item).", \"Error\");\n";
				} }
				if ( $notice ) { foreach ($notice as $cur_item) {
					echo 'pines.notice('.json_encode($cur_item).", \"Notice\");\n";
				} }
				?>
			});
			// ]]>
		</script>
		<?php
		}
	?></div>
	<div id="header" class="ui-widget-header">
		<h1 id="page_title">
			<a href="<?php echo htmlspecialchars(pines_url()); ?>">
				<?php if ($pines->config->tpl_mobile->use_header_image) { ?>
				<img src="<?php echo htmlspecialchars($pines->config->tpl_mobile->header_image); ?>" alt="<?php echo htmlspecialchars($pines->config->page_title); ?>" />
				<?php } else { ?>
				<span><?php echo htmlspecialchars($pines->config->page_title); ?></span>
				<?php } ?>
			</a>
		</h1>
		<?php echo $pines->page->render_modules('header', 'module_header'); ?>
		<?php echo $pines->page->render_modules('header_right', 'module_header'); ?>
		<?php if (!empty($menu)) { ?>
		<div><button id="menu_link" type="button" class="ui-state-default">Main Menu</button></div>
		<?php } ?>
	</div>
	<div id="pre_content"><?php echo $pines->page->render_modules('pre_content', 'module_header'); ?></div>
	<div id="breadcrumbs"><?php echo $pines->page->render_modules('breadcrumbs', 'module_header'); ?></div>
	<div id="content_top_left"><?php echo $pines->page->render_modules('content_top_left'); ?></div>
	<div id="content_top_right"><?php echo $pines->page->render_modules('content_top_right'); ?></div>
	<div id="content"><?php echo $pines->page->render_modules('content', 'module_content'); ?></div>
	<div id="content_bottom_left"><?php echo $pines->page->render_modules('content_bottom_left'); ?></div>
	<div id="content_bottom_right"><?php echo $pines->page->render_modules('content_bottom_right'); ?></div>
	<div id="left"><?php echo $pines->page->render_modules('left'); ?>&nbsp;</div>
	<div id="right"><?php echo $pines->page->render_modules('right'); ?>&nbsp;</div>
	<div id="post_content"><?php echo $pines->page->render_modules('post_content', 'module_header'); ?></div>
	<div id="footer" class="ui-widget-header">
		<div class="modules"><?php echo $pines->page->render_modules('footer', 'module_header'); ?></div>
		<p id="copyright"><?php echo htmlspecialchars($pines->config->copyright_notice, ENT_COMPAT, '', false); ?></p>
	</div>
	<div id="bottom"><?php echo $pines->page->render_modules('bottom', 'module_header'); ?></div>
</div>
<?php if (!empty($menu)) { ?>
<div id="menu" style="display: none;">
	<div><button id="menu_back" type="button" class="ui-state-default">Return to Page</button></div>
	<?php echo $pines->page->render_modules('main_menu', 'module_head'); ?>
</div>
<?php } ?>
</body>
</html>