<?php
/**
 * A function to load the user switcher.
 *
 * @package Pines
 * @subpackage com_su
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	function com_su_load_switcher() {
		pines.com_su_loginpage_url = "<?php echo addslashes(pines_url('com_su', 'loginpage')); ?>";
		pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_su/includes/user_switcher.js", true);
	}
	// ]]>
</script>