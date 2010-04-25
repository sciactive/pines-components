<?php
/**
 * Includes for editing a mailing.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<script type="text/javascript">
	// <![CDATA[
	pines.loadcss("http://yui.yahooapis.com/2.7.0/build/assets/skins/sam/skin.css");
	pines.loadjs("http://yui.yahooapis.com/2.7.0/build/yahoo-dom-event/yahoo-dom-event.js");
	pines.loadjs("http://yui.yahooapis.com/2.7.0/build/element/element-min.js");
	pines.loadjs("http://yui.yahooapis.com/2.7.0/build/container/container_core-min.js");
	pines.loadjs("http://yui.yahooapis.com/2.7.0/build/menu/menu-min.js");
	pines.loadjs("http://yui.yahooapis.com/2.7.0/build/button/button-min.js");
	pines.loadjs("http://yui.yahooapis.com/2.7.0/build/editor/editor-min.js");
	pines.loadjs("http://yui.yahooapis.com/2.7.0/build/connection/connection-min.js");
	pines.loadjs("<?php echo $pines->config->rela_location; ?>components/com_newsletter/includes/yui-image-uploader26.js");

	var editor = new YAHOO.widget.Editor('data', {
		handleSubmit: true,
		dompath: true,
		animate: true
	});
	editor._defaultToolbar.titlebar = false;
	editor._defaultToolbar.buttonType = 'advanced';
	yuiImgUploader(editor, 'data', '<?php echo pines_url('com_newsletter', 'upload'); ?>','image');
	editor.render();
	// ]]>
</script>