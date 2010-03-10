<?php
/**
 * Provides a form for the user to edit a user template.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'Editing New User Template' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide user template details in this form.';
?>
<form class="pform" method="post" id="user_template_details" action="<?php echo pines_url('com_hrm', 'saveusertemplate'); ?>">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
			<?php if (isset($this->entity->uid)) { ?>
		<span>Created By: <span class="date"><?php echo $pines->user_manager->get_username($this->entity->uid); ?></span></span>
		<br />
			<?php } ?>
		<span>Created On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_cdate); ?></span></span>
		<br />
		<span>Modified On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_mdate); ?></span></span>
	</div>
	<?php } ?>
	<div class="element">
		<label><span class="label">Name</span>
			<input class="field ui-widget-content" type="text" name="name" size="24" value="<?php echo $this->entity->name; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Default Component</span>
			<span class="note">This component will be responsible for the user's home page.</span>
			<select class="field ui-widget-content" name="default_component">
					<?php foreach ($this->default_components as $cur_component) { ?>
				<option value="<?php echo $cur_component; ?>"<?php echo (($this->entity->default_component == $cur_component) ? ' selected="selected"' : ''); ?>><?php echo $cur_component; ?></option>
					<?php } ?>
			</select></label>
	</div>
	<div class="element">
		<label><span class="label">Primary Group</span>
			<span class="note">The primary group when using this template can be set to this group, or any group beneath it.</span>
			<select class="field ui-widget-content" name="group" size="1">
				<option value="null">-- No Primary Group --</option>
						<?php echo $pines->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->group_array, $this->entity->group); ?>
			</select></label>
	</div>
	<div class="element">
		<label><span class="label">Groups</span>
			<span class="note">Hold Ctrl (Command on Mac) to select multiple groups.</span>
			<select class="field ui-widget-content" name="groups[]" multiple="multiple" size="6">
						<?php echo $pines->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->group_array, $this->entity->groups); ?>
			</select></label>
	</div>
	<div class="element buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo pines_url('com_hrm', 'listusertemplates'); ?>');" value="Cancel" />
	</div>
</form>