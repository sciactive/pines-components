<?php
/**
 * Main page of the Mobile Pines template.
 *
 * The page which is output to the user is built using this file.
 *
 * @package Templates
 * @subpackage mobile
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
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="icon" type="image/vnd.microsoft.icon" href="<?php echo htmlspecialchars($pines->config->location); ?>favicon.ico" />
	<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->rela_location); ?>system/includes/js.php"></script>
	<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/js/template.js"></script>
	<?php echo $pines->page->render_modules('head', 'module_head'); ?>
	<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/css/pines.css" media="all" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="navbar" class="navbar navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container">
			<?php if (!empty($menu)) { ?>
			<button id="menu_link" type="button" class="btn btn-navbar">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<?php } if ($pines->config->tpl_mobile->use_header_image) { ?>
			<a class="brand brand-image" href="<?php echo htmlspecialchars(pines_url()); ?>">
				<img src="<?php echo htmlspecialchars($pines->config->tpl_mobile->header_image); ?>" alt="<?php echo htmlspecialchars($pines->config->page_title); ?>" />
			</a>
			<?php } else { ?>
			<a class="brand" href="<?php echo htmlspecialchars(pines_url()); ?>">
				<?php echo htmlspecialchars($pines->config->page_title); ?>
			</a>
			<?php } ?>
		</div>
	</div>
</div>
<div id="wrapper">
<div id="page">
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
	<?php $header_content = $pines->page->render_modules('header', 'module_header').$pines->page->render_modules('header_right', 'module_header');
	if ($header_content) { ?>
	<div id="header" class="well">
		<?php echo $header_content; ?>
	</div>
	<?php } ?>
	<div id="pre_content"><?php echo $pines->page->render_modules('pre_content', 'module_header'); ?></div>
	<div id="breadcrumbs"><?php echo $pines->page->render_modules('breadcrumbs', 'module_header'); ?></div>
	<div id="content_top_left"><?php echo $pines->page->render_modules('content_top_left'); ?></div>
	<div id="content_top_right"><?php echo $pines->page->render_modules('content_top_right'); ?></div>
	<div id="content"><?php echo $pines->page->render_modules('content'); ?></div>
	<div id="content_bottom_left"><?php echo $pines->page->render_modules('content_bottom_left'); ?></div>
	<div id="content_bottom_right"><?php echo $pines->page->render_modules('content_bottom_right'); ?></div>
	<?php if (in_array($pines->config->tpl_mobile->variant, array('full', 'left'))) { ?>
	<div id="left"><?php echo $pines->page->render_modules('left'); ?></div>
	<?php } if (in_array($pines->config->tpl_mobile->variant, array('full', 'right'))) { ?>
	<div id="right"><?php echo $pines->page->render_modules('right'); ?></div>
	<?php } ?>
	<div id="post_content"><?php echo $pines->page->render_modules('post_content', 'module_header'); ?></div>
	<div id="footer" class="well">
		<div class="modules"><?php echo $pines->page->render_modules('footer', 'module_header'); ?></div>
		<p id="copyright"><?php echo htmlspecialchars($pines->config->copyright_notice, ENT_COMPAT, '', false); ?></p>
	</div>
	<div id="bottom"><?php echo $pines->page->render_modules('bottom', 'module_header'); ?></div>
</div>
<?php if (!empty($menu)) { ?>
<div id="menu">
	<?php echo $menu; ?>
</div>
<?php } ?>
</div>
</body>
</html>