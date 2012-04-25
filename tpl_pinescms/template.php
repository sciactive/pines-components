<?php
/**
 * Main page of the Pines CMS template.
 *
 * The page which is output to the user is built using this file.
 *
 * @package Templates\pinescms
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
header('Content-Type: text/html');

if (strpos($pines->config->tpl_pinescms->variant, 'fluid') === 0)
	$layout_type = 'fluid';
if (substr($pines->config->tpl_pinescms->variant, -4) === 'left')
	$sidebar = 'left';
elseif (substr($pines->config->tpl_pinescms->variant, -5) === 'right')
	$sidebar = 'right';
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title><?php echo htmlspecialchars($pines->page->get_title()); ?></title>
	<link rel="icon" type="image/vnd.microsoft.icon" href="<?php echo htmlspecialchars($pines->config->location); ?>favicon.ico" />
	<link href="http://fonts.googleapis.com/css?family=EB+Garamond" rel="stylesheet" type="text/css" />
	<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/tpl_pinescms/css/dropdown/dropdown.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/tpl_pinescms/css/dropdown/dropdown.vertical.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/tpl_pinescms/css/dropdown/default.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/tpl_pinescms/css/dropdown/default.pines.css" media="all" rel="stylesheet" type="text/css" />
	<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->rela_location); ?>system/includes/js.php"></script>
	<?php echo $pines->page->render_modules('head', 'module_head'); ?>
	<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/tpl_pinescms/css/style.css" media="all" rel="stylesheet" type="text/css" />
	<script type="text/javascript">pines(function(){ if ($.pnotify) {
		$.pnotify.defaults.pnotify_opacity = .9;
		$.pnotify.defaults.pnotify_delay = 15000;
		$.pnotify.defaults.pnotify_nonblock = false;
	}});</script>
</head>
<body>
	<div id="top"><?php
		echo $pines->page->render_modules('top');
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
	<div class="navbar navbar-fixed-top <?php echo ($pines->config->tpl_pinescms->navigation_fixed) ? '': 'nav_not_fixed' ;?>">
		<div class="navbar-inner">
			<div <?php echo ($pines->config->tpl_pinescms->navigation_orientation == "nav-right") ? 'id="nav-right"' : '';?> class="container">
				<?php if ($pines->config->tpl_pinescms->use_nav_logo) { ?>
				<a href="<?php echo htmlspecialchars(pines_url()); ?>"><img id="nav-logo" class="<?php echo htmlspecialchars($pines->config->tpl_pinescms->navigation_orientation) ?>" src="<?php echo htmlspecialchars($pines->config->tpl_pinescms->nav_logo_image); ?>" alt="<?php echo htmlspecialchars($pines->config->page_title); ?>"/></a>
				<?php } ?>
				<?php echo $pines->page->render_modules('main_menu', 'module_head'); ?>
			</div>
		</div>
	</div>
	<div id="shadow_container" class="container<?php echo ($layout_type) ? '-fluid': ''; ?> <?php echo ($pines->config->tpl_pinescms->navigation_fixed) ? 'fixed_nav': '' ;?>">
		<div id="shadow_box">
			<?php if ($pines->config->tpl_pinescms->display_header) { ?>
			<div id="pines_header" class="clearfix">
				<a id="logo" href="<?php echo htmlspecialchars(pines_url()); ?>">
					<?php if ($pines->config->tpl_pinescms->use_header_image) { ?>
					<img src="<?php echo htmlspecialchars($pines->config->tpl_pinescms->header_image); ?>" alt="<?php echo htmlspecialchars($pines->config->page_title); ?>" />
					<?php } else { ?>
					<span><?php echo htmlspecialchars($pines->config->page_title); ?></span>
					<?php } ?>
				</a>
				<div id="header_search"><?php echo $pines->page->render_modules('search', 'module_head'); ?></div>
				<div id="header"><?php echo $pines->page->render_modules('header'); ?></div>
				<div id="header-right"><?php echo $pines->page->render_modules('header_right'); ?></div>
			</div>
			<?php } ?>
			<div id="pines_pre_content"><?php echo $pines->page->render_modules('pre_content'); ?></div>
			<div id="breadcrumbs"><?php echo $pines->page->render_modules('breadcrumbs', 'module_simple'); ?></div>
			<div id="pines_content">
				<div class="modules">
					<?php if ($sidebar) { if ($sidebar == 'left') { ?>
					<div class="row<?php echo ($layout_type) ? '-fluid': ''; ?>">
						<div id="sidebar" class="span3">
							<div class="content_padding"><?php echo $pines->page->render_modules('left'); echo $pines->page->render_modules('right'); ?></div>
						</div>
						<div id="main_content" class="span9">
							<div class="content_padding"><?php echo $pines->page->render_modules('content'); ?></div>
						</div>
					</div>
					<?php } elseif ($sidebar == 'right') { ?>
					<div class="row<?php echo ($layout_type) ? '-fluid': ''; ?>">
						<div id="main_content" class="span9">
							<div class="content_padding"><?php echo $pines->page->render_modules('content'); ?></div>
						</div>
						<div id="sidebar" class="span3">
							<div class="content_padding"><?php echo $pines->page->render_modules('left'); echo $pines->page->render_modules('right'); ?></div>
						</div>
					</div>
					<?php } } else {
						echo $pines->page->render_modules('content');
					} ?>
				</div>
			</div>
			<div id="pines_post_content"><?php echo $pines->page->render_modules('post_content'); ?></div>
			<div id="pines_footer_shadow"></div>
			<div id="pines_footer"><?php echo $pines->page->render_modules('footer'); ?></div>
			<div id="pines_copyright">
				<?php if ($pines->config->tpl_pinescms->show_recycled_bits) { ?>
				<div id="recycled_bits"></div>
				<?php } ?>
				<p><?php echo htmlspecialchars($pines->config->copyright_notice, ENT_COMPAT, '', false); ?></p>
			</div>
		</div>
	</div>
	<div id="copyright-line-container" class="container<?php echo ($layout_type) ? '-fluid': ''; ?>">
		<div id="copyright-line-edges"><div id="copyright-line">&nbsp;</div></div>
	</div>
	<div id="bottom"><?php echo $pines->page->render_modules('bottom'); ?></div>
</body>
</html>