<?php
/**
 * Provides a form for the user to edit a tax/fee.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Tax/Fee' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide tax/fee details in this form.';
?>
<form class="pf-form" method="post" action="<?php echo htmlentities(pines_url('com_sales', 'taxfee/save')); ?>">
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
		<input class="pf-field ui-widget-content" type="text" name="name" size="24" value="<?php echo $this->entity->name; ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Enabled</span>
		<input class="pf-field ui-widget-content" type="checkbox" name="enabled" size="24" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Type</span>
		<span class="pf-note">This determines how the rate is applied to the price of items.</span>
		<select class="pf-field ui-widget-content" name="type">
			<option value="percentage"<?php echo $this->entity->type == 'percentage' ? ' selected="selected"' : ''; ?>>Percentage</option>
			<option value="flat_rate"<?php echo $this->entity->type == 'flat_rate' ? ' selected="selected"' : ''; ?>>Flat Rate</option>
		</select></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Rate</span>
		<span class="pf-note">Enter a percentage (5 for 5%) or a flat rate in dollars (5 for $5).</span>
		<input class="pf-field ui-widget-content" type="text" name="rate" size="24" value="<?php echo $this->entity->rate; ?>" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Locations</span>
		<span class="pf-note">This tax will be applied to sales by users in these groups.</span>
		<span class="pf-note">Hold Ctrl (Command on Mac) to select multiple groups.</span>
		<select class="pf-field ui-widget-content" name="locations[]" multiple="multiple" size="6">
			<?php echo $pines->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->locations, $this->entity->locations); ?>
		</select></label>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_sales', 'taxfee/list')); ?>');" value="Cancel" />
	</div>
</form>