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
?>
<form class="pform" method="post" action="<?php echo pines_url('com_newsletter', 'send'); ?>">
<fieldset>
	<legend>Sending <?php echo $this->mail->name; ?></legend>
	<div class="element">
		<label><span class="label">From Email</span>
		<input class="field ui-widget-content" type="text" name="from" size="24" value="<?php echo htmlentities($pines->config->com_newsletter->default_from); ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Reply to Email</span>
		<input class="field ui-widget-content" type="text" name="replyto" size="24" value="<?php echo htmlentities($pines->config->com_newsletter->default_reply_to); ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Subject</span>
		<input class="field ui-widget-content" type="text" name="subject" size="24" value="<?php echo htmlentities($this->mail->subject); ?>" /></label>
	</div>
	<div class="element">
		<span class="label">Select Groups</span>
		<span class="note">Click group name to select children as well.</span>
		<div class="group">
		<?php
		$group_select_menu = new menu;
		$pines->user_manager->get_group_menu($group_select_menu);
		echo $group_select_menu->render(array('<ul class="unorderedlisttree">', '</ul>'),
				array('<li>', '</li>'),
				array('<ul>', '</ul>'),
				array('<li>', '</li>'),
				"<input class=\"field\" type=\"checkbox\" name=\"group[]\" value=\"#DATA#\" /><label>#NAME#</label>\n",
				'<hr style="visibility: hidden; clear: both;" />');
		/*$sendprep->content(
			$pines->user_manager->get_group_tree("<label><input type="checkbox" name="#guid#" />#mark##name# [#groupname#]</label>\n", $pines->user_manager->get_group_array())
		); */
		?>
		</div>
	</div>
	<div class="element heading">
		<h1>Options</h1>
	</div>
	<div class="element">
		<label><span class="label">Include a link to the mail's web address.</span>
		<span class="note">For online viewing.</span>
		<input class="field ui-widget-content" type="checkbox" name="include_permalink" checked /></label>
	</div>
	<div class="element buttons">
		<input type="hidden" name="mail_id" value="<?php echo $_REQUEST['mail_id']; ?>" />
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo pines_url('com_newsletter', 'list'); ?>');" value="Cancel" />
	</div>
</fieldset>
</form>