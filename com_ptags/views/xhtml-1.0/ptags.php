<?php
/**
 * A view to load Pines Tags.
 *
 * @package Pines
 * @subpackage com_ptags
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	pines.loadcss("<?php echo htmlspecialchars($pines->config->rela_location); ?>components/com_ptags/includes/jquery.ptags.default.css");
	pines.loadjs("<?php echo htmlspecialchars($pines->config->rela_location); ?>components/com_ptags/includes/<?php echo $pines->config->debug_mode ? 'jquery.ptags.js' : 'jquery.ptags.min.js'; ?>");
	// ]]>
</script>