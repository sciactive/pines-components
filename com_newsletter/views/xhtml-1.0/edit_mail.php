<?php
/**
 * Provides a form for the user to edit a mailing.
 *
 * @package Pines
 * @subpackage com_newsletter
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$this->title = (!isset($this->entity->guid)) ? 'Editing New Mail' : 'Editing ['.htmlentities($this->entity->name).']';
$pines->editor->load();
$pines->uploader->load();
?>
<form class="pf-form" name="editingmail" method="post" action="<?php echo htmlentities(pines_url($this->new_option, $this->new_action)); ?>">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
		<?php if (isset($this->entity->user)) { ?>
		<div>User: <span class="date"><?php echo "{$this->entity->user->name} [{$this->entity->user->username}]"; ?></span></div>
		<div>Group: <span class="date"><?php echo "{$this->entity->group->name} [{$this->entity->group->groupname}]"; ?></span></div>
		<?php } ?>
		<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
		<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
	</div>
	<?php } ?>
	<div class="pf-element">
		<label><span class="pf-label">Name</span>
		<input class="pf-field ui-widget-content" type="text" name="name" size="24" value="<?php echo htmlentities($this->entity->name); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Subject</span>
		<input class="pf-field ui-widget-content" type="text" name="subject" size="24" value="<?php echo htmlentities($this->entity->subject); ?>" /></label>
	</div>
	<div class="pf-element pf-heading">
		<h1>Message</h1>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-full-width"><textarea rows="3" cols="35" class="ui-widget-content peditor" name="data" style="width: 100%;"><?php echo htmlentities($this->entity->message); ?></textarea></div>
	</div>
	<div class="pf-element pf-heading">
		<h1>Attachments</h1>
	</div>
	<div class="pf-element">
		<span class="pf-label">Current Attachments</span>
		<?php if ( !empty($this->entity->attachments) ) {
			echo '<div class="pf-group">';
			foreach ($this->entity->attachments as $cur_attachment) { ?>
		<label><input class="pf-field ui-widget-content" type="checkbox" name="attach_<?php echo clean_checkbox($cur_attachment); ?>" checked="checked" /><?php echo htmlentities($cur_attachment); ?></label><br />
		<?php }
		echo '</div>';
		} ?>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Add an Attachment</span>
		<input class="pf-field ui-widget-content puploader" name="attachment" type="input" /></label>
	</div>
	<div class="pf-element pf-buttons">
		<input type="hidden" name="update" value="yes" />
		<input type="hidden" name="mail_id" value="<?php echo $this->entity->guid; ?>" />
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Save Mail" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url($this->close_option, $this->close_action)); ?>');" value="Close" /> <small>(Closing will lose any unsaved changes!)</small>
	</div>
</form>