<?php
/**
 * Main page of the Bamboo template.
 *
 * The page which is output to the user is built using this file.
 *
 * @package Pines
 * @subpackage tpl_bamboo
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<!--
Design by Free CSS Templates
http://www.freecsstemplates.org
Released for free under a Creative Commons Attribution 2.5 License

Name       : Bamboo
Description: A two-column, fixed-width design with dark color scheme.
Version    : 1.0
Released   : 20090820

-->
<html>
	<head>
		<meta charset="utf-8" />
		<title><?php echo htmlspecialchars($pines->page->get_title()); ?></title>
		<link rel="icon" type="image/vnd.microsoft.icon" href="<?php echo htmlspecialchars($pines->config->location); ?>favicon.ico" />

		<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/css/style.css" media="all" rel="stylesheet" type="text/css" />

		<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/css/dropdown/dropdown.css" media="all" rel="stylesheet" type="text/css" />
		<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/css/dropdown/dropdown.vertical.css" media="all" rel="stylesheet" type="text/css" />
		<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/css/dropdown/dropdown.vertical.rtl.css" media="all" rel="stylesheet" type="text/css" />
		<link href="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/css/dropdown/themes/default/default.ultimate.css" media="all" rel="stylesheet" type="text/css" />

		<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->rela_location); ?>system/includes/js.php"></script>

		<?php echo $pines->page->render_modules('head', 'module_head'); ?>

		<!--[if lt IE 7]>
		<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->location); ?>templates/<?php echo htmlspecialchars($pines->current_template); ?>/js/jquery/jquery.dropdown.js"></script>
		<![endif]-->
	</head>
	<body>
		<?php
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
		<?php } ?>
		<?php echo $pines->page->render_modules('top', 'module_header'); ?>
		<div id="wrapper">
			<div id="header-wrapper">
				<div id="header">
					<div id="logo">
						<h1><a href="<?php echo htmlspecialchars(pines_url()); ?>"><?php echo htmlspecialchars($pines->config->page_title); ?></a></h1>
						<p><?php echo htmlspecialchars($pines->config->tpl_bamboo->slogan); ?></p>
						<br style="clear: left;" /><br />
						<?php echo $pines->page->render_modules('header', 'module_header'); ?>
					</div>
					<div id="header-right">
						<?php echo $pines->page->render_modules('header_right', 'module_header_right'); ?>
					</div>
				</div>
			</div>
			<!-- end #header -->
			<div id="menu">
				<?php echo $pines->page->render_modules('main_menu', 'module_head'); ?>
			</div>
			<!-- end #menu -->
			<div id="page">
				<div id="page-bgtop">
					<div id="page-bgbtm">
						<div id="breadcrumbs">
							<?php echo $pines->page->render_modules('breadcrumbs', 'module_head'); ?>
							<div style="clear: both;">&nbsp;</div>
						</div>
						<div id="content">
							<?php echo $pines->page->render_modules('content', 'module_content'); ?>
							<div style="clear: both;">&nbsp;</div>
						</div>
						<!-- end #content -->
						<div id="sidebar">
							<ul>
								<?php echo $pines->page->render_modules('left', 'module_sidebar'); ?>
								<?php echo $pines->page->render_modules('right', 'module_sidebar'); ?>
							</ul>
						</div>
						<!-- end #sidebar -->
						<div style="clear: both;">&nbsp;</div>
					</div>
				</div>
			</div>
			<!-- end #page -->
		</div>
		<div id="footer-wrapper">
			<div id="footer">
				<?php echo $pines->page->render_modules('footer', 'module_header'); ?>
				<p class="copyright"><?php echo htmlspecialchars($pines->config->copyright_notice, ENT_COMPAT, '', false); ?></p>
			</div>
			<br style="clear: both; height: 0;" />
			<!-- end #footer -->
		</div>
		<?php echo $pines->page->render_modules('bottom', 'module_header'); ?>
	</body>
</html>