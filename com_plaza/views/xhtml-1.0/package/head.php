<?php
/**
 * Some common JavaScript functions.
 *
 * @package Pines
 * @subpackage com_plaza
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	pines.com_plaza = {
		ajax_box: null,
		ajax_show: function(){
			if (!pines.com_plaza.ajax_box)
				pines.com_plaza.ajax_box = $("<div style=\"display: none; position: absolute; top: 0; left: 0; right: 0; z-index: 2000; text-align: center;\"><img src=\"<?php echo addslashes($pines->config->location); ?>components/com_plaza/includes/ajax-loader.gif\" alt=\"\" /></div>").prependTo("body");
			pines.com_plaza.ajax_box.show();
		},
		ajax_hide: function(){
			pines.com_plaza.ajax_box.hide();
		}
	};
	// ]]>
</script>