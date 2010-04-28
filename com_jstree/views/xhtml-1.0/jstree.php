<?php
/**
 * A view to load jsTree.
 *
 * @package Pines
 * @subpackage com_jstree
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	pines.loadjs("<?php echo $pines->config->rela_location; ?>components/com_jstree/includes/jquery.tree.min.js");
	pines.loadjs("<?php echo $pines->config->rela_location; ?>components/com_jstree/includes/plugins/jquery.tree.contextmenu.js");
	pines(function(){
		$.tree.defaults.ui.theme_path = "<?php echo $pines->config->rela_location; ?>components/com_jstree/includes/themes/default/style.css";
	});
	// ]]>
</script>