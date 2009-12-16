<?php
/**
 * Provides a form for the user to edit a group.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'Editing New Group' : 'Editing ['.htmlentities($this->entity->groupname).']';
$this->note = 'Provide group details in this form.';
?>
<form class="pform" method="post" id="group_details" action="<?php echo pines_url($this->new_option, $this->new_action); ?>">
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
		<label><span class="label">Group Name</span>
			<input class="field" type="text" name="groupname" size="20" value="<?php echo $this->entity->groupname; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Display Name</span>
			<input class="field" type="text" name="name" size="20" value="<?php echo $this->entity->name; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Email</span>
			<input class="field" type="text" name="email" size="20" value="<?php echo $this->entity->email; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Parent</span>
			<select class="field" name="parent" size="1">
				<option value="none">--No Parent--</option>
				<?php echo $config->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->group_array, $this->entity->parent); ?>
			</select></label>
	</div>

	<?php if ( $this->display_abilities ) { ?>
	<div class="element heading">
		<h1>Abilities</h1>
		<input type="hidden" name="abilities" value="true" />
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
		<input class="ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url('com_user', 'managegroups'); ?>';" value="Cancel" />
	</div>
</form>