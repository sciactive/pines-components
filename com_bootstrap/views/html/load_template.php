<?php
/**
 * A view to load Bootstrap CSS.
 *
 * @package Components\bootstrap
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

?>
<link rel="stylesheet" type="text/css" href="<?php echo pines_url('com_bootstrap', 'template/template_css'); ?>">
<script type="text/javascript">
	var load_css1 = <?php echo json_encode($pines->config->tpl_bootstrap->load_css1); ?>;
	var load_css2 = <?php echo json_encode($pines->config->tpl_bootstrap->load_css2); ?>;
	var load_css3 = <?php echo json_encode($pines->config->tpl_bootstrap->load_css3); ?>;
	var load_css4 = <?php echo json_encode($pines->config->tpl_bootstrap->load_css4); ?>;
	
	var load_js1 = <?php echo json_encode($pines->config->tpl_bootstrap->load_js1); ?>;
	var load_js2 = <?php echo json_encode($pines->config->tpl_bootstrap->load_js2); ?>;
	var load_js3 = <?php echo json_encode($pines->config->tpl_bootstrap->load_js3); ?>;
	var load_js4 = <?php echo json_encode($pines->config->tpl_bootstrap->load_js4); ?>;
	
	var navbar_trigger = <?php echo json_encode($pines->config->tpl_bootstrap->navbar_trigger); ?>;
	var mobile_menu = <?php echo json_encode($pines->config->tpl_bootstrap->mobile_menu); ?>;
	var footer_height = <?php echo json_encode($pines->config->tpl_bootstrap->footer_height); ?>;
	var footer_type = <?php echo json_encode($pines->config->tpl_bootstrap->footer_type); ?>;
	
	var verified_brand_colors = <?php echo json_encode($pines->template->verify_color($pines->config->tpl_bootstrap->brand_color) && $pines->template->verify_color($pines->config->tpl_bootstrap->brand_hover_color)); ?>;
	var verified_font_colors = <?php echo json_encode($pines->template->verify_color($pines->config->tpl_bootstrap->font_color) && $pines->template->verify_color($pines->config->tpl_bootstrap->font_hover_color)); ?>;
	var navbar_menu_height = <?php echo json_encode($pines->config->tpl_bootstrap->navbar_menu_height); ?>;
	
	var brand_color = <?php echo json_encode($pines->config->tpl_bootstrap->brand_color); ?>;
	var font_color = <?php echo json_encode($pines->config->tpl_bootstrap->font_color); ?>;
</script>
<script type="text/javascript" src="<?php echo htmlspecialchars($pines->config->location); ?>templates/tpl_bootstrap/js/template.js"></script>
