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
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->title = (!isset($this->entity->guid)) ? 'Editing New Mail' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$pines->editor->load();
$pines->uploader->load();
?>
<form class="pf-form" method="post" action="<?php echo htmlspecialchars(pines_url($this->new_option, $this->new_action)); ?>">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
		<?php if (isset($this->entity->user)) { ?>
		<div>User: <span class="date"><?php echo htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
		<div>Group: <span class="date"><?php echo htmlspecialchars("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
		<?php } ?>
		<div>Created: <span class="date"><?php echo htmlspecialchars(format_date($this->entity->p_cdate, 'full_short')); ?></span></div>
		<div>Modified: <span class="date"><?php echo htmlspecialchars(format_date($this->entity->p_mdate, 'full_short')); ?></span></div>
	</div>
	<?php } ?>
	<div class="pf-element">
		<label><span class="pf-label">Name</span>
		<input class="pf-field" type="text" name="name" size="24" value="<?php echo htmlspecialchars($this->entity->name); ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Subject</span>
		<input class="pf-field" type="text" name="subject" size="24" value="<?php echo htmlspecialchars($this->entity->subject); ?>" /></label>
	</div>
	<div class="pf-element pf-heading">
		<h3>Message</h3>
	</div>
	<div class="pf-element pf-full-width">
		<div class="pf-group pf-full-width" style="margin-left: 0;"><textarea rows="3" cols="35" class="peditor" name="data" style="width: 100%;"><?php echo htmlspecialchars($this->entity->message); ?></textarea></div>
	</div>
	<div class="pf-element pf-heading">
		<h3>Attachments</h3>
	</div>
	<div class="pf-element">
		<span class="pf-label">Current Attachments</span>
		<?php if ( !empty($this->entity->attachments) ) {
			echo '<div class="pf-group">';
			foreach ($this->entity->attachments as $cur_attachment) { ?>
		<label><input class="pf-field" type="checkbox" name="attach_<?php echo clean_checkbox($cur_attachment); ?>" checked="checked" /><?php echo htmlspecialchars($cur_attachment); ?></label><br />
		<?php }
		echo '</div>';
		} ?>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Add an Attachment</span>
		<input class="pf-field puploader" name="attachment" type="input" /></label>
	</div>
	<div class="pf-element pf-buttons">
		<input type="hidden" name="update" value="yes" />
		<input type="hidden" name="mail_id" value="<?php echo (int) $this->entity->guid; ?>" />
		<input class="pf-button btn btn-primary" type="submit" value="Save Mail" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url($this->close_option, $this->close_action))); ?>);" value="Close" /> <small>(Closing will lose any unsaved changes!)</small>
	</div>
</form>