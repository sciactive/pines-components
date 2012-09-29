<?php
/**
 * A view to load jsTree.
 *
 * @package Components\jstree
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_jstree/includes/<?php echo $pines->config->debug_mode ? 'jquery.jstree.js' : 'jquery.jstree.min.js'; ?>");
	pines(function(){
		$.jstree.defaults.core.animation = 100;
		$.jstree._themes = "<?php echo htmlspecialchars($pines->config->location); ?>components/com_jstree/includes/themes/";
		<?php if ($pines->depend->check('component', 'com_uasniffer') && $pines->depend->check('browser', 'mobile')) { ?>
		$.jstree.defaults.themes.theme = "mobile";
		<?php } else { ?>
		$.jstree.defaults.themes.theme = "apple";
		<?php } ?>
	});
</script>