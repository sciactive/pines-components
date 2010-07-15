<?php
/**
 * A view to load the Oxygen icons.
 *
 * @package Pines
 * @subpackage com_oxygenicons
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	pines.loadcss("<?php echo htmlentities($pines->config->rela_location); ?>components/com_oxygenicons/includes/oxygen/icons.css");
	// ]]>
</script>