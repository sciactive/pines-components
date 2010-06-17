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
if (is_array($this->entities)) {
	$this->title = 'Editing Multiple Stock Entries';
} else {
	$this->title = htmlentities("Editing Stock Entry of \"{$this->entity->product->name}\"");
	if (isset($this->entity->serial))
		$this->title .= htmlentities(" (Serial: {$this->entity->serial})");
	if (isset($this->entity->location)) {
		$this->title .= htmlentities(" at \"{$this->entity->location->name}\"");
	} else {
		$this->title .= ' Not in Inventory';
	}
}
$this->note = 'Provide stock entry details in this form.';
?>
<form class="pf-form" method="post" action="<?php echo htmlentities(pines_url('com_sales', 'stock/save')); ?>">
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
		<?php if ( isset($this->entity->guid) ) { ?>
		<span class="pf-field"><?php echo $this->entity->product->name; ?></span>
		<?php } elseif ( is_array($this->entities) ) {
			$names = array();
			foreach ($this->entities as $cur_entity) {
				$names[] = $cur_entity->product->name;
			}
			?>
		<span class="pf-field"><?php echo implode(', ', $names); ?></span>
		<?php } ?>
	</div>
	<div class="pf-element">
		<span class="pf-label">Product Sku</span>
		<?php if ( isset($this->entity->guid) ) { ?>
		<span class="pf-field"><?php echo $this->entity->product->sku; ?></span>
		<?php } elseif ( is_array($this->entities) ) {
			$skus = array();
			foreach ($this->entities as $cur_entity) {
				$skus[] = $cur_entity->product->sku;
			}
			?>
		<span class="pf-field"><?php echo implode(', ', $skus); ?></span>
		<?php } ?>
	</div>
	<?php if ( isset($this->entity->guid) ) { ?>
	<div class="pf-element">
		<span class="pf-label">Last Transaction</span>
		<span class="pf-field"><?php echo isset($this->entity) ? $this->entity->last_reason() : ''; ?></span>
	</div>
	<?php } ?>
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			$(".p_muid_option_accordian").accordion({
				autoHeight: false,
				collapsible: true,
				active: false,
				change: function(event, ui){
					// Change to a 1 when the accordion is active
					if (ui.newHeader.length) {
						ui.newContent.find(".p_muid_change_this").val("1");
						ui.newHeader.removeClass("ui-priority-secondary");
					} else {
						ui.oldContent.find(".p_muid_change_this").val("");
						ui.oldHeader.addClass("ui-priority-secondary");
					}
				}
			});
		});
		// ]]>
	</script>
	<br class="pf-clearing" />
	<div class="p_muid_option_accordian">
		<h3 class="ui-priority-secondary"><a href="#">Change Availability</a></h3>
		<div>
			<input class="p_muid_change_this" type="hidden" name="available_change" value="" />
			<div class="pf-element">
				<label><span class="pf-label">Available</span>
					<input class="pf-field ui-widget-content" type="checkbox" name="available" size="24" value="ON"<?php echo $this->entity->available ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Reason</span>
					<select class="pf-field ui-widget-content" name="available_reason">
						<?php if (!isset($this->entity) || $this->entity->available) { ?>
						<option value="unavailable_on_hold">Item is on Hold</option>
						<option value="unavailable_damaged">Item is Damaged</option>
						<option value="unavailable_destroyed">Item is Destroyed/Trashed</option>
						<option value="unavailable_missing">Item is Missing</option>
						<option value="unavailable_theft">Item is Stolen</option>
						<option value="unavailable_display">Item is a Display</option>
						<option value="unavailable_promotion">Promotional Giveaway</option>
						<option value="unavailable_gift">Gift Giveaway</option>
						<option value="unavailable_error_sale">Sale Error</option>
						<option value="unavailable_error_return">Return Error</option>
						<?php /* <option value="unavailable_error_rma">RMA Error</option> */ ?>
						<option value="unavailable_error_po">PO Receiving Error</option>
						<option value="unavailable_error_transfer">Transfer Error</option>
						<option value="unavailable_error_adjustment">Previous Adjustment Error</option>
						<option value="unavailable_error">Other Error</option>
						<?php } if (!isset($this->entity) || !$this->entity->available) { ?>
						<option value="available_not_on_hold">Item is No Longer on Hold</option>
						<option value="available_repaired">Item is Repaired</option>
						<option value="available_not_destroyed">Item is Not Destroyed/Trashed</option>
						<option value="available_found">Item is Found</option>
						<option value="available_recovered">Stolen Item is Recovered</option>
						<option value="available_not_display">Item is No Longer a Display</option>
						<option value="available_not_promotion">Returned Promotional Giveaway</option>
						<option value="available_not_gift">Returned Gift Giveaway</option>
						<option value="available_error_sale">Sale Error</option>
						<option value="available_error_return">Return Error</option>
						<?php /* <option value="available_error_rma">RMA Error</option> */ ?>
						<option value="available_error_po">PO Receiving Error</option>
						<option value="available_error_transfer">Transfer Error</option>
						<option value="available_error_adjustment">Previous Adjustment Error</option>
						<option value="available_error">Other Error</option>
						<?php } ?>
					</select>
				</label>
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<?php if (isset($this->entity) && $this->entity->product->serialized) { ?>
	<div class="p_muid_option_accordian">
		<h3 class="ui-priority-secondary"><a href="#">Change Serial</a></h3>
		<div>
			<input class="p_muid_change_this" type="hidden" name="serial_change" value="" />
			<div class="pf-element">
				<label><span class="pf-label">Serial</span>
					<input class="pf-field ui-widget-content" type="text" name="serial" size="24" value="<?php echo $this->entity->serial; ?>" /></label>
			</div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Reason</span>
					<select class="pf-field ui-widget-content" name="serial_reason">
						<option value="serial_changed_reserialize">Item is Being Reserialized</option>
						<option value="serial_changed_damaged">Serial Number is Damaged</option>
						<option value="serial_changed_error_po">PO Receiving Error</option>
						<option value="serial_changed_error_adjustment">Previous Adjustment Error</option>
						<option value="serial_changed_error">Other Error</option>
					</select>
				</label>
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<?php } ?>
	<div class="p_muid_option_accordian">
		<h3 class="ui-priority-secondary"><a href="#">Change Location</a></h3>
		<div>
			<input class="p_muid_change_this" type="hidden" name="location_change" value="" />
			<div class="pf-element">
				<label>
					<span class="pf-label">Location</span>
					<select class="pf-field ui-widget-content" name="location">
						<option value="null">-- Not in Inventory --</option>
						<?php echo $pines->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->locations, $this->entity->location->guid); ?>
					</select>
				</label>
			</div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Reason</span>
					<select class="pf-field ui-widget-content" name="location_reason">
						<?php if (!isset($this->entity) || isset($this->entity->location)) { ?>
						<option value="location_changed_picked_up">Item is Picked Up</option>
						<option value="location_changed_trashed">Item is Destroyed/Trashed</option>
						<option value="location_changed_missing">Item is Missing</option>
						<option value="location_changed_theft">Item is Stolen</option>
						<?php } if (!isset($this->entity) || !isset($this->entity->location)) { ?>
						<option value="location_changed_not_trashed">Item is Not Destroyed/Trashed</option>
						<option value="location_changed_found">Item is Found</option>
						<option value="location_changed_recovered">Stolen Item is Recovered</option>
						<?php } ?>
						<option value="location_changed_promotion">Promotional Giveaway</option>
						<option value="location_changed_gift">Gift Giveaway</option>
						<option value="location_changed_error_sale">Sale Error</option>
						<option value="location_changed_error_return">Return Error</option>
						<option value="location_changed_error_po">PO Receiving Error</option>
						<option value="location_changed_error_transfer">Transfer Error</option>
						<option value="location_changed_error_adjustment">Previous Adjustment Error</option>
						<option value="location_changed_error">Other Error</option>
					</select>
				</label>
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<div class="p_muid_option_accordian">
		<h3 class="ui-priority-secondary"><a href="#">Change Vendor</a></h3>
		<div>
			<input class="p_muid_change_this" type="hidden" name="vendor_change" value="" />
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
			<div class="pf-element">
				<label>
					<span class="pf-label">Reason</span>
					<select class="pf-field ui-widget-content" name="vendor_reason">
						<option value="vendor_changed_error_po">PO Receiving Error</option>
						<option value="vendor_changed_error_adjustment">Previous Adjustment Error</option>
						<option value="vendor_changed_error">Other Error</option>
					</select>
				</label>
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<div class="p_muid_option_accordian">
		<h3 class="ui-priority-secondary"><a href="#">Change Cost</a></h3>
		<div>
			<input class="p_muid_change_this" type="hidden" name="cost_change" value="" />
			<div class="pf-element">
				<label><span class="pf-label">Cost</span>
					<span class="pf-field">$<input class="ui-widget-content" style="text-align: right;" type="text" name="cost" size="10" value="<?php echo $this->entity->cost; ?>" /></span></label>
			</div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Reason</span>
					<select class="pf-field ui-widget-content" name="cost_reason">
						<option value="cost_changed_error_po">PO Receiving Error</option>
						<option value="cost_changed_error_adjustment">Previous Adjustment Error</option>
						<option value="cost_changed_error">Other Error</option>
					</select>
				</label>
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<br class="pf-clearing" />
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } elseif ( is_array($this->entities) ) {
			$guids = array();
			foreach ($this->entities as $cur_entity) {
				$guids[] = $cur_entity->guid;
			}
			?>
		<input type="hidden" name="id" value="<?php echo implode(',', $guids); ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_sales', 'stock/list')); ?>');" value="Cancel" />
	</div>
</form>