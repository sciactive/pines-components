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
	//pines.loadjs("http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js");
	//pines.loadjs("http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js");
	//pines.loadcss("http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/<?php echo $pines->config->tpl_pines->theme;?>/jquery-ui.css");
	pines.loadjs("<?php echo $pines->config->rela_location; ?>components/com_jquery/includes/<?php echo $pines->config->debug_mode ? 'jquery.js' : 'jquery.min.js'; ?>");
	// ]]>
</script>