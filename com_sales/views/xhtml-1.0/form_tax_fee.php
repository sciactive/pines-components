<?php
/**
 * Provides a form for the user to edit a tax/fee.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'Editing New Tax/Fee' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide tax/fee details in this form.';
?>
<form class="pform" method="post" id="tax_fee_details" action="<?php echo pines_url('com_sales', 'savetaxfee'); ?>">
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
		<label><span class="label">Name</span>
		<input class="field" type="text" name="name" size="24" value="<?php echo $this->entity->name; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Enabled</span>
		<input class="field" type="checkbox" name="enabled" size="24" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<div class="element">
		<label><span class="label">Type</span>
		<span class="note">This determines how the rate is applied to the price of items.</span>
		<select class="field" name="type">
			<option value="percentage"<?php echo $this->entity->type == 'percentage' ? ' selected="selected"' : ''; ?>>Percentage</option>
			<option value="flat_rate"<?php echo $this->entity->type == 'flat_rate' ? ' selected="selected"' : ''; ?>>Flat Rate</option>
		</select></label>
	</div>
	<div class="element">
		<label><span class="label">Rate</span>
		<span class="note">Enter a percentage (5 for 5%) or a flat rate in dollars (5 for $5).</span>
		<input class="field" type="text" name="rate" size="24" value="<?php echo $this->entity->rate; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Locations</span>
		<span class="note">Sales by users in these groups will be applied this tax.</span>
		<span class="note">Hold Ctrl (Command on Mac) to select multiple groups.</span>
		<select class="field" name="locations[]" multiple="multiple" size="6">
			<?php echo $config->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->locations, $this->entity->locations); ?>
		</select></label>
	</div>
	<div class="element buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url('com_sales', 'listtaxfees'); ?>';" value="Cancel" />
	</div>
</form>