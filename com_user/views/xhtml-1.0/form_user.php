<?php
/**
 * Provides a form for the user to edit a user.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'Editing New User' : 'Editing ['.htmlentities($this->entity->username).']';
$this->note = 'Provide user details in this form.';
?>
<script type="text/javascript">
	// <![CDATA[
	function verify_form(form) {
	var target_form = document.getElementById('user_details');
	if (target_form.password.value != target_form.password2.value) {
		alert('Your passwords do not match!');
		return false;
	} else {
		return true;
	}
	}
	// ]]>
</script>
<form class="pform" method="post" id="user_details" action="<?php echo pines_url($this->new_option, $this->new_action); ?>" onsubmit="return verify_form('user_details');">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
			<?php if (isset($this->entity->uid)) { ?>
		<span>Created By: <span class="date"><?php echo $config->user_manager->get_username($this->entity->uid); ?></span></span>
		<br />
			<?php } ?>
		<span>Created On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_cdate); ?></span></span>
		<br />
		<span>Modified On: <span class="date"><?php echo date('Y-m-d', $this->entity->p_mdate); ?></span></span>
	</div>
	<?php } ?>
	<div class="element">
		<label><span class="label">Username</span>
			<input class="field" type="text" name="username" size="24" value="<?php echo $this->entity->username; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Name</span>
			<input class="field" type="text" name="name" size="24" value="<?php echo $this->entity->name; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Email</span>
			<input class="field" type="text" name="email" size="24" value="<?php echo $this->entity->email; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Timezone</span>
			<span class="note">This overrides the primary group's timezone.</span>
			<select class="field" name="timezone" size="1">
				<option value="">--Inherit From Group--</option>
				<?php $tz = DateTimeZone::listIdentifiers();
				sort($tz);
				foreach ($tz as $cur_tz) { ?>
				<option value="<?php echo $cur_tz; ?>"<?php echo $this->entity->timezone == $cur_tz ? ' selected="selected"' : ''; ?>><?php echo $cur_tz; ?></option>
				<?php } ?>
			</select></label>
	</div>
	<div class="element">
		<label><span class="label"><?php if (!is_null($this->entity->guid)) echo 'Update '; ?>Password</span>
			<?php if (is_null($this->entity->guid)) {
				echo ($config->com_user->empty_pw ? '<span class="note">May be blank.</span>' : '');
			} else {
				echo '<span class="note">Leave blank, if not changing.</span>';
			} ?>
			<input class="field" type="password" name="password" size="24" /></label>
	</div>
	<div class="element">
		<label><span class="label">Repeat Password</span>
			<input class="field" type="password" name="password2" size="24" /></label>
	</div>
	<?php if ( $this->display_default_components ) { ?>
	<div class="element">
		<label><span class="label">Default Component</span>
			<span class="note">This component will be responsible for the user's home page.</span>
			<select class="field" name="default_component">
					<?php foreach ($this->default_components as $cur_component) { ?>
				<option value="<?php echo $cur_component; ?>"<?php echo (($this->entity->default_component == $cur_component) ? ' selected="selected"' : ''); ?>><?php echo $cur_component; ?></option>
					<?php } ?>
			</select></label>
	</div>
	<?php } ?>

	<?php if ( $this->display_groups ) { ?>
	<div class="element heading">
		<h1>Groups</h1>
	</div>
		<?php if (is_null($this->group_array)) { ?>
	<div class="element">
		<span class="label">There are no groups to display.</span>
	</div>
		<?php } else { ?>
	<div class="element">
		<label><span class="label">Primary Group</span>
			<select class="field" name="gid" size="1">
				<option value="null">-- No Primary Group --</option>
						<?php echo $config->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->group_array, $this->entity->gid); ?>
			</select></label>
	</div>
	<div class="element">
		<label><span class="label">Groups</span>
			<span class="note">Hold Ctrl (Command on Mac) to select multiple groups.</span>
			<select class="field" name="groups[]" multiple="multiple" size="6">
						<?php echo $config->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->group_array, $this->entity->groups); ?>
			</select></label>
	</div>
		<?php }
	} ?>

	<?php if ( $this->display_abilities ) { ?>
	<div class="element heading">
		<h1>Abilities</h1>
		<input type="hidden" name="abilities" value="true" />
	</div>
	<div class="element">
		<label><span class="label">Inherit additional abilities from groups.</span>
			<input class="field" type="checkbox" name="inherit_abilities" value="ON" <?php echo ($this->entity->inherit_abilities ? 'checked="checked" ' : ''); ?>/></label>
	</div>
		<?php foreach ($this->sections as $cur_section) {
			$section_abilities = $config->ability_manager->get_abilities($cur_section);
			if ( count($section_abilities) ) { ?>
	<div class="element"><span class="label">Abilities for <em><?php echo $cur_section; ?></em></span>
		<div class="group">
						<?php foreach ($section_abilities as $cur_ability) { ?>
			<label><input class="field" type="checkbox" name="<?php echo $cur_section; ?>[]" value="<?php echo $cur_ability['ability']; ?>"
								<?php if ( array_search($cur_section.'/'.$cur_ability['ability'], $this->entity->abilities) !== false ) { ?>
						  checked="checked"
										  <?php } ?>
						  />&nbsp;<?php echo $cur_ability['title'] . ' <small>(' . $cur_ability['description'] . ')</small>'; ?></label><br />
							<?php } ?>
		</div>
	</div>
			<?php }
		}
	} ?>

	<div class="element buttons">
		<?php if ( !is_null($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url('com_user', 'manageusers'); ?>';" value="Cancel" />
	</div>
</form>