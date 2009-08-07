<?php
/**
 * Provides a form for the user to edit a mailing.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$page->head("<!-- Skin CSS file -->\n");
$page->head("<link rel=\"stylesheet\" type=\"text/css\" href=\"http://yui.yahooapis.com/2.7.0/build/assets/skins/sam/skin.css\">\n");
$page->head("<!-- Utility Dependencies -->\n");
$page->head("<script type=\"text/javascript\" src=\"http://yui.yahooapis.com/2.7.0/build/yahoo-dom-event/yahoo-dom-event.js\"></script>\n");
$page->head("<script type=\"text/javascript\" src=\"http://yui.yahooapis.com/2.7.0/build/element/element-min.js\"></script>\n");
$page->head("<!-- Needed for Menus, Buttons and Overlays used in the Toolbar -->\n");
$page->head("<script src=\"http://yui.yahooapis.com/2.7.0/build/container/container_core-min.js\"></script>\n");
$page->head("<script src=\"http://yui.yahooapis.com/2.7.0/build/menu/menu-min.js\"></script>\n");
$page->head("<script src=\"http://yui.yahooapis.com/2.7.0/build/button/button-min.js\"></script>\n");
$page->head("<!-- Source file for Rich Text Editor-->\n");
$page->head("<script src=\"http://yui.yahooapis.com/2.7.0/build/editor/editor-min.js\"></script>\n");
$page->head("<script src=\"http://yui.yahooapis.com/2.7.0/build/connection/connection-min.js\"></script>\n");
$page->head("<script src=\"".$config->template->url()."components/com_newsletter/js/yui-image-uploader26.js\"></script>\n");
$page->head("<script type=\"text/javascript\">\n");
$page->head("var editor = new YAHOO.widget.Editor('data', {\n");
$page->head("	handleSubmit: true,\n");
$page->head("	dompath: true,\n");
$page->head("	animate: true\n");
$page->head("});\n");
$page->head("editor._defaultToolbar.titlebar = false;\n");
$page->head("editor._defaultToolbar.buttonType = 'advanced';\n");
$page->head("yuiImgUploader(editor, 'data', '".$config->template->url('com_newsletter', 'upload')."','image');\n");
$page->head("editor.render();\n");
$page->head("</script>\n");
?>
<div class="yui-skin-sam">
<form enctype="multipart/form-data" name="editingmail" method="post" action="<?php echo $config->template->url(); ?>">
<div class="stylized stdform">
<input type="submit" value="Save Mail" />
<input type="button" onclick="window.location='<?php echo $config->template->url($this->close_option, $this->close_action); ?>';" value="Close" /> <small>(Closing will lose any unsaved changes!)</small>
<br /><br />
<label>Name<input type="text" name="name" value="<?php echo $this->mail->name; ?>" /></label>
<label>Subject<input type="text" name="subject" value="<?php echo $this->mail->subject; ?>" /></label>
<label>Message<textarea rows="30" name="data" id="data" style="width: 99%;"><?php echo $this->mail->message; ?></textarea></label><br />
<input type="hidden" name="option" value="<?php echo $this->new_option; ?>" />
<input type="hidden" name="action" value="<?php echo $this->new_action; ?>" />
<input type="hidden" name="update" value="yes" />
<input type="hidden" name="mail_id" value="<?php echo $this->mail->guid; ?>" />
<div class="spacer"></div>
<label>Attachments:</label>
<?php if ( !empty($this->mail->attachments) ) {
    foreach ($this->mail->attachments as $cur_attachment) { ?>
        <label><input type="checkbox" name="attach_<?php echo clean_checkbox($cur_attachment); ?>" checked="checked" /><?php echo $cur_attachment; ?></label>
<?php }
} ?>
<label>Upload <input name="attachment" type="file" /></label>
<div class="spacer"></div>
<input type="submit" value="Save Mail" />
<input type="button" onclick="window.location='<?php echo $config->template->url($this->close_option, $this->close_action); ?>';" value="Close" /> <small>(Closing will lose any unsaved changes!)</small>
<div class="spacer"></div>
</div>
</form>
</div>