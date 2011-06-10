<?php
/**
 * A view to load the elFinder file manager.
 *
 * @package Pines
 * @subpackage com_elfinder
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	pines.loadjs("<?php echo htmlspecialchars($pines->config->location); ?>components/com_elfinder/includes/js/<?php echo $pines->config->debug_mode ? 'elfinder.full.js' : 'elfinder.min.js'; ?>");
	pines.loadcss("<?php echo htmlspecialchars($pines->config->location); ?>components/com_elfinder/includes/css/elfinder.default.css");
	pines(function(){
		elFinder.prototype.options.cookie = {expires: <?php $params = session_get_cookie_params(); echo (int) $params['lifetime']; ?>, domain: '', path: '/', secure: false};
	});
	// ]]>
</script>