<?php
/**
 * Provides a form for the user to edit a stock entry.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Editing Stock Entry of "'.htmlentities($this->entity->product->name).'"'.(isset($this->entity->location) ? ' at "'.htmlentities($this->entity->location->name).'"' : ' Not in Inventory');
$this->note = 'Provide stock entry details in this form.';
?>
<form class="pf-form" method="post" id="stock_details" action="<?php echo htmlentities(pines_url('com_sales', 'stock/save')); ?>">
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
		<span class="pf-label">Product</span>
		<span class="pf-field"><?php echo $this->entity->product->name; ?></span>
	</div>
	<div class="pf-element">
		<span class="pf-label">Product Sku</span>
		<span class="pf-field"><?php echo $this->entity->product->sku; ?></span>
	</div>
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			$("#stock_details .option_accordian").accordion({
				autoHeight: false,
				collapsible: true,
				active: false
			});
		});
		// ]]>
	</script>
	<br class="pf-clearing" />
	<div class="option_accordian">
		<h3><a href="#">Change Availability</a></h3>
		<div>
			<div class="pf-element">
				<label><span class="pf-label">Available</span>
					<input class="pf-field ui-widget-content" type="checkbox" name="available" size="24" value="ON"<?php echo $this->entity->status == 'available' ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Reason</span>
					<select class="pf-field ui-widget-content" name="reason_available">
						<?php if ($this->entity->status == 'available') { ?>
						<option value="removed_error">Inventory Error</option>
						<option value="removed_on_hold">Item is on Hold</option>
						<option value="removed_damaged">Item is Damaged</option>
						<option value="removed_missing">Item is Missing</option>
						<option value="removed_promotion">Promotional Giveaway</option>
						<?php } else { ?>
						<option value="restored_error">Inventory Error</option>
						<option value="restored_not_on_hold">Item is Not on Hold</option>
						<option value="restored_repaired">Item is Repaired</option>
						<option value="restored_found">Item is Found</option>
						<option value="restored_promotion">Returned Promotional Giveaway</option>
						<?php } ?>
					</select>
				</label>
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<?php if ($this->entity->product->serialized) { ?>
	<div class="option_accordian">
		<h3><a href="#">Change Serial</a></h3>
		<div>
			<div class="pf-element">
				<label><span class="pf-label">Serial</span>
					<input class="pf-field ui-widget-content" type="text" name="serial" size="24" value="<?php echo $this->entity->serial; ?>" /></label>
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<?php } ?>
	<div class="option_accordian">
		<h3><a href="#">Change Location</a></h3>
		<div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Location</span>
					<select class="pf-field ui-widget-content" name="location">
						<option value="null">-- Not in Inventory --</option>
						<?php echo $pines->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->locations, $this->entity->location->guid); ?>
					</select>
				</label>
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<div class="option_accordian">
		<h3><a href="#">Change Vendor</a></h3>
		<div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Vendor</span>
					<select class="pf-field ui-widget-content" name="vendor">
						<option value="null">-- None --</option>
						<?php foreach ($this->vendors as $cur_vendor) { ?>
						<option value="<?php echo $cur_vendor->guid; ?>"<?php echo $this->entity->vendor->guid == $cur_vendor->guid ? ' selected="selected"' : ''; ?>><?php echo $cur_vendor->name; ?></option>
						<?php } ?>
					</select>
				</label>
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<div class="option_accordian">
		<h3><a href="#">Change Cost</a></h3>
		<div>
			<div class="pf-element">
				<label><span class="pf-label">Cost</span>
					<span class="pf-field">$<input class="ui-widget-content" style="text-align: right;" type="text" name="cost" size="10" value="<?php echo $this->entity->cost; ?>" /></span></label>
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<br class="pf-clearing" />
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_sales', 'stock/list')); ?>');" value="Cancel" />
	</div>
</form>