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
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
if (is_array($this->entities)) {
	$this->title = 'Editing Multiple Stock Entries';
} else {
	$this->title = htmlspecialchars("Editing Stock Entry of \"{$this->entity->product->name}\"");
	if (isset($this->entity->serial))
		$this->title .= htmlspecialchars(" (Serial: {$this->entity->serial})");
	if (isset($this->entity->location)) {
		$this->title .= htmlspecialchars(" at \"{$this->entity->location->name}\"");
	} else {
		$this->title .= ' Not in Inventory';
	}
}
$this->note = 'Provide stock entry details in this form.';
$pines->com_jstree->load();
?>
<form class="pf-form" id="p_muid_form" method="post" action="<?php echo htmlspecialchars(pines_url('com_sales', 'stock/save')); ?>">
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
		<span class="pf-label">Product</span>
		<?php if ( isset($this->entity->guid) ) { ?>
		<span class="pf-field"><?php echo htmlspecialchars($this->entity->product->name); ?></span>
		<?php } elseif ( is_array($this->entities) ) {
			$names = array();
			foreach ($this->entities as $cur_entity) {
				$names[] = $cur_entity->product->name;
			}
			?>
		<span class="pf-field"><?php echo htmlspecialchars(implode(', ', $names)); ?></span>
		<?php } ?>
	</div>
	<div class="pf-element">
		<span class="pf-label">Product Sku</span>
		<?php if ( isset($this->entity->guid) ) { ?>
		<span class="pf-field"><?php echo htmlspecialchars($this->entity->product->sku); ?></span>
		<?php } elseif ( is_array($this->entities) ) {
			$skus = array();
			foreach ($this->entities as $cur_entity) {
				$skus[] = $cur_entity->product->sku;
			}
			?>
		<span class="pf-field"><?php echo htmlspecialchars(implode(', ', $skus)); ?></span>
		<?php } ?>
	</div>
	<?php if ( isset($this->entity->guid) ) { ?>
	<div class="pf-element">
		<span class="pf-label">Last Transaction</span>
		<span class="pf-field"><?php echo isset($this->entity) ? htmlspecialchars($this->entity->last_reason()) : ''; ?></span>
	</div>
	<?php } ?>
	<script type="text/javascript">
		pines(function(){
			$(".p_muid_option_accordian", "#p_muid_form").on("shown", function(){
				$(this).find(".p_muid_change_this").val("1").end()
				.find(".accordion-toggle").removeClass("alert-info").addClass("alert-success");
			}).on("hide", function(){
				$(this).find(".p_muid_change_this").val("").end()
				.find(".accordion-toggle").removeClass("alert-success").addClass("alert-info");
			});
			// Location Tree
			var location = $("#p_muid_form [name=location]");
			$("#p_muid_form .location_tree")
			.bind("select_node.jstree", function(e, data){
				location.val(data.inst.get_selected().attr("id").replace("p_muid_", ""));
			})
			.bind("before.jstree", function (e, data){
				if (data.func == "parse_json" && "args" in data && 0 in data.args && "attr" in data.args[0] && "id" in data.args[0].attr)
					data.args[0].attr.id = "p_muid_"+data.args[0].attr.id;
			})
			.bind("loaded.jstree", function(e, data){
				var path = data.inst.get_path("#"+data.inst.get_settings().ui.initially_select, true);
				if (!path.length) return;
				data.inst.open_node("#"+path.join(", #"), false, true);
			})
			.jstree({
				"plugins" : [ "themes", "json_data", "ui" ],
				"json_data" : {
					"ajax" : {
						"dataType" : "json",
						"url" : <?php echo json_encode(pines_url('com_jstree', 'groupjson')); ?>
					}
				},
				"ui" : {
					"select_limit" : 1,
					"initially_select" : ["<?php echo (int) $this->entity->location->guid; ?>"]
				}
			});
		});
	</script>
	<br class="pf-clearing" />
	<div class="p_muid_option_accordian accordion">
		<div class="accordion-group">
			<a class="accordion-heading" href="javascript:void(0);" data-toggle="collapse" data-target=":focus + .collapse" tabindex="0">
				<big class="accordion-toggle alert-info">Change Availability</big>
			</a>
			<div class="accordion-body collapse">
				<div class="accordion-inner">
					<input class="p_muid_change_this" type="hidden" name="available_change" value="" />
					<div class="pf-element">
						<label><span class="pf-label">Available</span>
							<input class="pf-field" type="checkbox" name="available" value="ON"<?php echo $this->entity->available ? ' checked="checked"' : ''; ?> /></label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Reason</span>
							<select class="pf-field" name="available_reason">
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
		</div>
	</div>
	<?php if (isset($this->entity) && $this->entity->product->serialized) { ?>
	<div class="p_muid_option_accordian accordion">
		<div class="accordion-group">
			<a class="accordion-heading" href="javascript:void(0);" data-toggle="collapse" data-target=":focus + .collapse" tabindex="0">
				<big class="accordion-toggle alert-info">Change Serial</big>
			</a>
			<div class="accordion-body collapse">
				<div class="accordion-inner">
					<input class="p_muid_change_this" type="hidden" name="serial_change" value="" />
					<div class="pf-element">
						<label><span class="pf-label">Serial</span>
							<input class="pf-field" type="text" name="serial" size="24" value="<?php echo htmlspecialchars($this->entity->serial); ?>" /></label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Reason</span>
							<select class="pf-field" name="serial_reason">
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
		</div>
	</div>
	<?php } ?>
	<div class="p_muid_option_accordian accordion">
		<div class="accordion-group">
			<a class="accordion-heading" href="javascript:void(0);" data-toggle="collapse" data-target=":focus + .collapse" tabindex="0">
				<big class="accordion-toggle alert-info">Change Location</big>
			</a>
			<div class="accordion-body collapse">
				<div class="accordion-inner">
					<input class="p_muid_change_this" type="hidden" name="location_change" value="" />
					<div class="pf-element">
						<span class="pf-label">Location</span>
						<script type="text/javascript">
							pines(function(){
								$("#p_muid_location_null").change(function(){
									if ($(this).is(":checked"))
										$("#p_muid_location").slideUp();
									else
										$("#p_muid_location").slideDown();
								}).each(function(){
									// Slide up doesn't work on load, because it's hidden.
									if ($(this).is(":checked"))
										$("#p_muid_location").hide();
								});
							});
						</script>
						<label><input class="pf-field" type="checkbox" id="p_muid_location_null" name="location_null" value="ON"<?php echo !isset($this->entity->location) ? ' checked="checked"' : ''; ?> /> Not in inventory. (Sold, trashed, etc.)</label>
						<br />
						<div id="p_muid_location" class="pf-group">
							<div class="pf-field location_tree ui-widget-content ui-corner-all" style="height: 180px; width: 200px; overflow: auto;"></div>
						</div>
						<input type="hidden" name="location" />
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Reason</span>
							<select class="pf-field" name="location_reason">
								<?php if (!isset($this->entity) || isset($this->entity->location)) { ?>
								<option value="location_changed_shipped">Item is Shipped</option>
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
		</div>
	</div>
	<div class="p_muid_option_accordian accordion">
		<div class="accordion-group">
			<a class="accordion-heading" href="javascript:void(0);" data-toggle="collapse" data-target=":focus + .collapse" tabindex="0">
				<big class="accordion-toggle alert-info">Change Vendor</big>
			</a>
			<div class="accordion-body collapse">
				<div class="accordion-inner">
					<input class="p_muid_change_this" type="hidden" name="vendor_change" value="" />
					<div class="pf-element">
						<label>
							<span class="pf-label">Vendor</span>
							<select class="pf-field" name="vendor">
								<option value="null">-- None --</option>
								<?php
								$pines->entity_manager->sort($this->vendors, 'name');
								foreach ($this->vendors as $cur_vendor) { ?>
								<option value="<?php echo (int) $cur_vendor->guid; ?>"<?php echo $this->entity->vendor->guid == $cur_vendor->guid ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_vendor->name); ?></option>
								<?php } ?>
							</select>
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Reason</span>
							<select class="pf-field" name="vendor_reason">
								<option value="vendor_changed_error_po">PO Receiving Error</option>
								<option value="vendor_changed_error_adjustment">Previous Adjustment Error</option>
								<option value="vendor_changed_error">Other Error</option>
							</select>
						</label>
					</div>
					<br class="pf-clearing" />
				</div>
			</div>
		</div>
	</div>
	<div class="p_muid_option_accordian accordion">
		<div class="accordion-group">
			<a class="accordion-heading" href="javascript:void(0);" data-toggle="collapse" data-target=":focus + .collapse" tabindex="0">
				<big class="accordion-toggle alert-info">Change Cost</big>
			</a>
			<div class="accordion-body collapse">
				<div class="accordion-inner">
					<input class="p_muid_change_this" type="hidden" name="cost_change" value="" />
					<div class="pf-element">
						<label><span class="pf-label">Cost</span>
							<span class="pf-field">$</span><input class="pf-field" style="text-align: right;" type="text" name="cost" size="10" value="<?php echo htmlspecialchars($this->entity->cost); ?>" /></label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Reason</span>
							<select class="pf-field" name="cost_reason">
								<option value="cost_changed_error_po">PO Receiving Error</option>
								<option value="cost_changed_error_adjustment">Previous Adjustment Error</option>
								<option value="cost_changed_error">Other Error</option>
							</select>
						</label>
					</div>
					<br class="pf-clearing" />
				</div>
			</div>
		</div>
	</div>
	<br class="pf-clearing" />
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } elseif ( is_array($this->entities) ) {
			$guids = array();
			foreach ($this->entities as $cur_entity) {
				$guids[] = $cur_entity->guid;
			}
			?>
		<input type="hidden" name="id" value="<?php echo htmlspecialchars(implode(',', $guids)); ?>" />
		<?php } ?>
		<input class="pf-button btn btn-primary" type="submit" name="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_sales', 'stock/list'))); ?>);" value="Cancel" />
	</div>
</form>