<?php
/**
 * Includes for editing a mailing.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<!-- Skin CSS file -->
<link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.7.0/build/assets/skins/sam/skin.css">

<!-- Utility Dependencies -->
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/yahoo-dom-event/yahoo-dom-event.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/element/element-min.js"></script>

<!-- Needed for Menus, Buttons and Overlays used in the Toolbar -->
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/container/container_core-min.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/menu/menu-min.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/button/button-min.js"></script>

<!-- Source file for Rich Text Editor-->
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/editor/editor-min.js"></script>
<script type="text/javascript" src="http://yui.yahooapis.com/2.7.0/build/connection/connection-min.js"></script>
<script type="text/javascript" src="<?php echo $pines->config->rela_location; ?>components/com_newsletter/includes/yui-image-uploader26.js"></script>

<script type="text/javascript">
	// <![CDATA[
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