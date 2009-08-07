<?php
/**
 * Provides a form with options for sending a newsletter.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$page->head("<script src=\"".$config->template->url()."components/com_newsletter/js/jquery.js\" type=\"text/javascript\"></script>");
$page->head("<script src=\"".$config->template->url()."components/com_newsletter/js/jquery.checkboxtree.js\" type=\"text/javascript\"></script>");
$page->head("<script type=\"text/javascript\">
jQuery(document).ready(function(){
	jQuery(\".unorderedlisttree\").checkboxTree({
			collapsedarrow: \"".$config->template->url()."components/com_newsletter/images/img-arrow-collapsed.gif\",
			expandedarrow: \"".$config->template->url()."components/com_newsletter/images/img-arrow-expanded.gif\",
			blankarrow: \"".$config->template->url()."components/com_newsletter/images/img-arrow-blank.gif\",
			checkchildren: true
	});
});
</script>
");
$page->head("<link href=\"".$config->template->url()."components/com_newsletter/css/checktree.css\" media=\"all\" rel=\"stylesheet\" type=\"text/css\" />");
?>
<form method="post" action="<?php echo $config->template->url(); ?>">
<div class="stylized stdform">
<h2>Sending <?php echo $this->mail->name; ?></h2>
<label>From Email<input type="text" name="from" value="<?php echo htmlentities($config->com_newsletter->default_from); ?>" /></label>
<label>Reply to Email<input type="text" name="replyto" value="<?php echo htmlentities($config->com_newsletter->default_reply_to); ?>" /></label>
<label>Subject<input type="text" name="subject" value="<?php echo $this->mail->subject; ?>" /></label>
<label>Select Groups<span class="small">Click group name to select children as well.</span></label>
<?php
$group_select_menu = new menu;
$config->user_manager->get_group_menu($group_select_menu);
echo $group_select_menu->render(array('<ul class="unorderedlisttree">', '</ul>'),
		array('<li>', '</li>'),
		array('<ul>', '</ul>'),
		array('<li>', '</li>'),
		"<input type=\"checkbox\" name=\"group[]\" value=\"#DATA#\" /><label>#NAME#</label>\n",
		'<hr style="visibility: hidden; clear: both;" />');
/*$com_newsletter_sendprep->content(
	$config->user_manager->get_group_tree("<label><input type="checkbox" name="#guid#" />#mark##name# [#groupname#]</label>\n", $config->user_manager->get_group_array())
); */
?>
<br />
<label>Options</label>
<label><input type="checkbox" name="include_permalink" checked />&nbsp;Include a link to the mail's web address, for online viewing.</label>
<input type="hidden" name="option" value="com_newsletter" />
<input type="hidden" name="action" value="send" />
<input type="hidden" name="mail_id" value="<?php echo $_REQUEST['mail_id']; ?>" />
<div class="spacer"></div>
<input type="submit" value="Submit" />
<input type="button" onclick="window.location='<?php echo $config->template->url('com_newsletter', 'list'); ?>';" value="Cancel" />
<div class="spacer"></div>
</div>
</form>