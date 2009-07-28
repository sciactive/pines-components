<?php
/**
 * Retrieve the required options to send a newsletter.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

if ( !gatekeeper('com_newsletter/send') ) {
	$config->user_manager->punt_user("You don't have necessary permission.", $config->template->url('com_newsletter', 'list', null, false));
	return;
}

$com_newsletter_sendprep = new module('com_newsletter', 'sendprep', 'content');

if ( empty($_REQUEST['mail_id']) ) {
	display_error('Mail ID not valid!');
	return;
}

$mail = new entity;
$mail = $config->entity_manager->get_entity($_REQUEST['mail_id']);
if ( !$mail->has_tag('com_newsletter', 'mail') ) {
	display_error('Invalid mail specified!');
	return;
}

$page->head("<script src=\"components/com_newsletter/js/jquery.js\" type=\"text/javascript\"></script>");
$page->head("<script src=\"components/com_newsletter/js/jquery.checkboxtree.js\" type=\"text/javascript\"></script>");
$page->head("<script type=\"text/javascript\">
jQuery(document).ready(function(){
	jQuery(\".unorderedlisttree\").checkboxTree({
			collapsedarrow: \"components/com_newsletter/images/img-arrow-collapsed.gif\",
			expandedarrow: \"components/com_newsletter/images/img-arrow-expanded.gif\",
			blankarrow: \"components/com_newsletter/images/img-arrow-blank.gif\",
			checkchildren: true
	});
});
</script>
");
$page->head("<link href=\"components/com_newsletter/css/checktree.css\" media=\"all\" rel=\"stylesheet\" type=\"text/css\" />");

$com_newsletter_sendprep->content("<form method=\"post\" action=\"".$config->template->url()."\">\n");
$com_newsletter_sendprep->content("<div class=\"stylized stdform\">\n");
$com_newsletter_sendprep->content("<h2>Sending ".$mail->name."</h2>\n");
$com_newsletter_sendprep->content("<label>From Email<input type=\"text\" name=\"from\" value=\"".htmlentities($config->com_newsletter->default_from)."\" /></label>\n");
$com_newsletter_sendprep->content("<label>Reply to Email<input type=\"text\" name=\"replyto\" value=\"".htmlentities($config->com_newsletter->default_reply_to)."\" /></label>\n");
$com_newsletter_sendprep->content("<label>Subject<input type=\"text\" name=\"subject\" value=\"".$mail->subject."\" /></label>\n");
$com_newsletter_sendprep->content("<label>Select Users<span class=\"small\">Click on name to select entire group.</span></label>\n");
$user_select_menu = new menu;
$config->user_manager->get_user_menu(NULL, $user_select_menu);
$com_newsletter_sendprep->content($user_select_menu->render(array('<ul class="unorderedlisttree">', '</ul>'),
		array('<li>', '</li>'),
		array('<ul>', '</ul>'),
		array('<li>', '</li>'),
		"<input type=\"checkbox\" name=\"user[]\" value=\"#DATA#\" /><label>#NAME#</label>\n",
		'<hr style="visibility: hidden; clear: both;" />'));
/*$com_newsletter_sendprep->content(
	$config->user_manager->get_user_tree("<label><input type=\"checkbox\" name=\"#guid#\" />#mark##name# [#username#]</label>\n", $config->user_manager->get_user_array())
); */
$com_newsletter_sendprep->content("<br />");
$com_newsletter_sendprep->content("<label>Options</label>\n");
//$com_newsletter_sendprep->content("<label><input type=\"checkbox\" name=\"recurse_children\" checked />&nbsp;Send to child users as well. (Don't check child users, or they will be mailed twice.)</label>\n");
$com_newsletter_sendprep->content("<label><input type=\"checkbox\" name=\"include_permalink\" checked />&nbsp;Include a link to the mail's web address, for online viewing.</label>\n");
$com_newsletter_sendprep->content("<input type=\"hidden\" name=\"option\" value=\"com_newsletter\" />\n");
$com_newsletter_sendprep->content("<input type=\"hidden\" name=\"action\" value=\"send\" />\n");
$com_newsletter_sendprep->content("<input type=\"hidden\" name=\"mail_id\" value=\"".$_REQUEST['mail_id']."\" />\n");
$com_newsletter_sendprep->content("<div class=\"spacer\"></div>\n");
$com_newsletter_sendprep->content("<input type=\"submit\" value=\"Submit\" />\n");
$com_newsletter_sendprep->content("<input type=\"button\" onclick=\"window.location='".$config->template->url('com_newsletter', 'list')."';\" value=\"Cancel\" />\n");
$com_newsletter_sendprep->content("<div class=\"spacer\"></div>\n");
$com_newsletter_sendprep->content("</div>\n");
$com_newsletter_sendprep->content("</form><br />\n");
?>
