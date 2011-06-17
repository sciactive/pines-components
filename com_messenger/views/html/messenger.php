<?php
/**
 * A view to load JAXL Messenger.
 *
 * @package Pines
 * @subpackage com_messenger
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_messenger/includes/jaxl.js");
	//jaxl.pollUrl = "<?php echo htmlspecialchars($pines->config->location); ?>components/com_messenger/includes/jaxl.php";
	// ]]>
</script>