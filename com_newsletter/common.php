<?php
/**
 * com_newsletter's common file.
 *
 * @package XROOM
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('X_RUN') or die('Direct access prohibited');

if ( isset($config->ability_manager) ) {
	$config->ability_manager->add('com_newsletter', 'managemails', 'Manage Mails', 'Let users create, edit, and delete mailings.');
	$config->ability_manager->add('com_newsletter', 'send', 'Send', 'Let users send out mailings.');
}

/**
 * com_newsletter main class.
 *
 * Manages newsletters to send out to users.
 *
 * @package XROOM
 * @subpackage com_newsletter
 */
class com_newsletter extends component {
	function delete_attachment(&$mail, $name) {
		global $config;
		if ( unlink($config->setting_upload . 'attachments/' . clean_filename($name)) ) {
			if ( in_array($name, $mail->attachments) )
				unset($mail->attachments[array_search($name, $mail->attachments)]);
			return true;
		} else {
			display_error('File removal failed!');
			return false;
		}
	}
	
	function edit_mail($heading = '', $mail = NULL, $new_option = '', $new_action = '', $close_option = "com_newsletter", $close_action = "list") {
		global $config, $page;

		if ( !is_null($mail) ) {
			if ( !$mail->has_tag('com_newsletter', 'mail') ) {
				display_error('Invalid mail!');
				return false;
			}
		} else {
			$mail = new entity;
		}

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
		$page->head("yuiImgUploader(editor, 'data', '".$config->template->url()."upload.php','image');\n");
		$page->head("editor.render();\n");
		$page->head("</script>\n");

		$module = new module('com_newsletter', 'edit_mail', 'content');
		$module->title = $heading;
		$module->content("<div class=\"yui-skin-sam\">\n");
		$module->content("<form enctype=\"multipart/form-data\" name=\"editingmail\" method=\"post\">\n");
		$module->content("<div class=\"stylized stdform\">\n");
		$module->content("<input type=\"submit\" value=\"Save Mail\" />\n");
		$module->content("<input type=\"button\" onclick=\"window.location='".$config->template->url($close_option, $close_action)."';\" value=\"Close\" /> <small>(Closing will lose any unsaved changes!)</small>\n");
		$module->content("<br /><br />\n");
		$module->content("<label>Name<input type=\"test\" name=\"name\" value=\"".$mail->name."\"></label>\n");
		$module->content("<label>Subject<input type=\"text\" name=\"subject\" value=\"".$mail->subject."\" /></label>\n");
		$module->content("<label>Message<textarea rows=\"30\" name=\"data\" id=\"data\" style=\"width: 99%;\">".$mail->message."</textarea></label><br />\n");
		$module->content("<input type=\"hidden\" name=\"option\" value=\"$new_option\" />\n");
		$module->content("<input type=\"hidden\" name=\"action\" value=\"$new_action\" />\n");
		$module->content("<input type=\"hidden\" name=\"update\" value=\"yes\" />\n");
		$module->content("<input type=\"hidden\" name=\"mail_id\" value=\"".$mail->guid."\" />\n");
		$module->content("<div class=\"spacer\"></div>\n");
		$module->content("<label>Attachments:</label>\n");
		if ( !empty($mail->attachments) ) {
			foreach ($mail->attachments as $cur_attachment) {
				$module->content("<label><input type=\"checkbox\" name=\"attach_".clean_checkbox($cur_attachment)."\" checked />$cur_attachment</label>\n");
			}
		}
		$module->content("<label>Upload <input name=\"attachment\" type=\"file\" /></label>\n");
		$module->content("<div class=\"spacer\"></div>\n");
		$module->content("<input type=\"submit\" value=\"Save Mail\" />\n");
		$module->content("<input type=\"button\" onclick=\"window.location='".$config->template->url($close_option, $close_action)."';\" value=\"Close\" /> <small>(Closing will lose any unsaved changes!)</small>\n");
		$module->content("<div class=\"spacer\"></div>\n");
		$module->content("</div>\n");
		$module->content("</form>\n");
		$module->content("</div>\n");
	}

	function list_mails($line_header, $line_footer) {
		global $config;
		$entities = array();
		$entity = new entity;

		$entities = $config->entity_manager->get_entities_by_tags('com_newsletter', 'mail');

		$module = new module('com_newsletter', 'listmails', 'content');
		$module->title = "Mails";

		foreach($entities as $entity) {
			$cur_mail = $entity->name;
			$cur_mail_id = $entity->guid;
			$module->content($line_header . "<strong>$cur_mail</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;");
			$module->content("<input type=\"button\" onclick=\"window.location='".$config->template->url('com_newsletter', 'edit', array('mail_id' => urlencode($cur_mail_id)))."';\" value=\"Edit\" /> | ");
			$module->content("<input type=\"button\" onclick=\"window.location='".$config->template->url('com_newsletter', 'sendprep', array('mail_id' => urlencode($cur_mail_id)))."';\" value=\"Send\" /> | ");
			$module->content("<input type=\"button\" onclick=\"if(confirm('Are you sure you want to delete \\'$cur_mail\\'?')) {window.location='".$config->template->url('com_newsletter', 'delete', array('mail_id' => urlencode($cur_mail_id)))."';}\" value=\"Delete\" />");
			$module->content($line_footer . "<br /><br />\n");
		}

		if ( empty($entities) )
			display_notice("There are no mails.");
	}
}

$config->com_newsletter = new com_newsletter;

?>