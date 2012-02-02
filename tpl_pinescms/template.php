<?php
/**
 * Main page of the Pines CMS template.
 *
 * The page which is output to the user is built using this file.
 *
 * @package Pines
 * @subpackage tpl_pinescms
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title><?php echo htmlspecialchars($pines->page->get_title()); ?></title>
	<link rel="icon" type="image/vnd.microsoft.icon" href="<?php echo htmlspecialchars($pines->config->location); ?>favicon.ico" />
	<link href="http://fonts.googleapis.com/css?family=EB+Garamond" rel="stylesheet" type="text/css" />
	<link href="http://fonts.googleapis.com/css?family=Droid+Sans:400,700" rel="stylesheet" type="text/css" />

	<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/tpl_pinescms/css/dropdown/dropdown.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/tpl_pinescms/css/dropdown/dropdown.vertical.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/tpl_pinescms/css/dropdown/default.css" media="all" rel="stylesheet" type="text/css" />
	<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/tpl_pinescms/css/dropdown/default.ultimate.css" media="all" rel="stylesheet" type="text/css" />

	<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->rela_location); ?>system/includes/js.php"></script>
	<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->location); ?>templates/tpl_pinescms/js/template.js"></script>

	<?php echo $pines->page->render_modules('head', 'module_head'); ?>
	
	<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/tpl_pinescms/css/style.css" media="all" rel="stylesheet" type="text/css" />
</head>
<body>
	<div id="top"><?php
		echo $pines->page->render_modules('top');
		$error = $pines->page->get_error();
		$notice = $pines->page->get_notice();
		if ( $error || $notice ) { ?>
		<script type="text/javascript">
			// <![CDATA[
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
			// ]]>
		</script>
		<?php
		}
	?></div>
	<div class="background_shadow">
		<div id="pines_navigation">
			<div id="pines_navlist_container"><?php echo $pines->page->render_modules('main_menu', 'module_head'); ?></div>
		</div>

		<div id="pines_header">
			<a id="logo" href="<?php echo htmlspecialchars(pines_url()); ?>">
				<img src="<?php echo htmlspecialchars($pines->config->location); ?>templates/tpl_pinescms/images/logo.png" alt="<?php echo htmlspecialchars($pines->config->page_title); ?>" />
			</a>
			<div id="header_right">
				<img src="<?php echo htmlspecialchars($pines->config->location); ?>templates/tpl_pinescms/images/intro-<?php echo htmlspecialchars($pines->config->tpl_pinescms->variant); ?>-1-0.png" alt="Introducing Pines version 1.0" />
				<?php echo $pines->page->render_modules('header_right', 'module_head'); ?>
			</div>
			<div id="header">
				<?php echo $pines->page->render_modules('header', 'module_head'); ?>
			</div>
		</div>
		<div id="breadcrumbs"><?php echo $pines->page->render_modules('breadcrumbs', 'module_simple'); ?></div>

		<div id="pines_pre_content"><?php echo $pines->page->render_modules('pre_content'); ?></div>
		<div id="pines_content">
			<div class="modules">
				<?php echo $pines->page->render_modules('content'); ?>
				<?php echo $pines->page->render_modules('left'); ?>
				<?php echo $pines->page->render_modules('right'); ?>
			</div>
			<div class="headline">
				<img src="<?php echo htmlspecialchars($pines->config->location); ?>templates/tpl_pinescms/images/other-pines-projects.png" alt="Check Out Some Other Projects Pines Offers" />
			</div>
			<div id="project_content" class="textured_block">
				<img id="project_list" src="<?php echo htmlspecialchars($pines->config->location); ?>templates/tpl_pinescms/images/<?php echo htmlspecialchars($pines->config->tpl_pinescms->variant); ?>-other-projects.png" usemap="#projects" />
				<map name="projects">
					<?php if ($pines->config->tpl_pinescms->variant == 'cms') { ?>
					<area shape="rect" coords="0,0,139,139" href="http://pinesframework.org/" alt="Pines Framework" />
					<?php } else { ?>
					<area shape="rect" coords="0,0,139,139" href="http://pinescms.org" alt="Pines CMS" />
					<?php } ?>
					<area shape="rect" coords="164,0,303,139" href="http://pinesframework.org/pnotify/" target="_blank" alt="Pines Notify" />
					<area shape="rect" coords="328,0,467,139" href="http://pinesframework.org/pgrid/" target="_blank" alt="Pines Grid" />
					<area shape="rect" coords="492,0,631,139" href="http://pinesframework.org/ptags/" target="_blank" alt="Pines Tags" />
					<area shape="rect" coords="653,0,793,139" href="http://pinesframework.org/pform/" target="_blank" alt="Pines Form" />
				</map>
			</div>
		</div>
		<div id="pines_post_content"><?php echo $pines->page->render_modules('post_content'); ?></div>

		<div id="pines_footer">
			<div class="modules"><?php echo $pines->page->render_modules('footer'); ?></div>
			<p class="copyright"><?php echo htmlspecialchars($pines->config->copyright_notice, ENT_COMPAT, '', false); ?></p>
		</div>
	</div>
	<div id="footer-line">&nbsp;</div>
	<div id="bottom"><?php echo $pines->page->render_modules('bottom'); ?></div>
</body>
</html>