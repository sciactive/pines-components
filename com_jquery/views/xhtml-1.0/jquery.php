<?php
/**
 * A view to load jQuery.
 *
 * @package Pines
 * @subpackage com_jquery
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	pines.loadjs("<?php echo htmlentities($pines->config->rela_location); ?>components/com_jquery/includes/<?php echo $pines->config->debug_mode ? 'jquery.js' : 'jquery.min.js'; ?>");
	// ]]>
</script>