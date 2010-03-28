<?php
/**
 * A view to load Pines Notify.
 *
 * @package Pines
 * @subpackage com_pnotify
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<link href="<?php echo $pines->config->rela_location; ?>components/com_pnotify/includes/jquery.pnotify.default.css" media="all" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?php echo $pines->config->rela_location; ?>components/com_pnotify/includes/jquery.pnotify.js"></script>
<script type="text/javascript">
	// <![CDATA[
	if (!_alert) {
		var _alert;
		_alert = window.alert;
		window.alert = function(message) {
			$.pnotify({pnotify_title: "Alert", pnotify_text: String(message).replace("\n", "<br />")});
		};
	}
	// ]]>
</script>