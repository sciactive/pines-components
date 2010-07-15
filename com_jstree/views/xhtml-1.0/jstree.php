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
	pines.loadjs("<?php echo htmlentities($pines->config->rela_location); ?>components/com_jstree/includes/<?php echo $pines->config->debug_mode ? 'jquery.jstree.js' : 'jquery.jstree.min.js'; ?>");
	pines(function(){
		$.jstree.defaults.core.animation = 100;
		$.jstree._themes = "<?php echo htmlentities($pines->config->rela_location); ?>components/com_jstree/includes/themes/";
	});
	// ]]>
</script>