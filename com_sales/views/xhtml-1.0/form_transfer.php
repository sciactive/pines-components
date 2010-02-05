<?php
/**
 * Provides a form for the user to edit a transfer.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'Editing New Transfer' : 'Editing ['.htmlentities($this->entity->guid).']';
$this->note = 'Use this form to transfer inventory to another location.';
?>
<form class="pform" method="post" id="transfer_details" action="<?php echo pines_url('com_sales', 'savetransfer'); ?>">
	<script type="text/javascript">
		// <![CDATA[
		var stock;
		var stock_table;
		var available_stock_table;
		var stock_dialog;
		var available_stock = <?php
		$all_stock = array();
		foreach ($this->stock as $stock) {
			if (in_array($stock->guid, $this->entity->stock))
				continue;
			$export_stock = (object) array(
				'key' => (string) $stock->guid,
				'classes' => '',
				'values' => array(
					(string) $stock->product->name,
					(string) $stock->serial,
					(string) $stock->vendor->name,
					(string) "{$stock->location->name} [{$stock->location->groupname}]",
					(string) $stock->cost,
					(string) $stock->status
				)
				
			);
			array_push($all_stock, $export_stock);
		}
		echo json_encode($all_stock);
		?>;

		function update_stock() {
			var all_rows = stock_table.pgrid_get_all_rows().pgrid_export_rows();
			available_stock_table.pgrid_get_selected_rows().pgrid_deselect_rows();
			// Save the data into a hidden form element.
			stock.val(JSON.stringify(all_rows));
		}
		
		$(function(){
			stock = $("#stock");
			stock_table = $("#stock_table");
			available_stock_table = $("#available_stock_table");
			stock_dialog = $("#stock_dialog");

			<?php if (empty($this->entity->received)) { ?>
			stock_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Add',
						extra_class: 'icon picon_16x16_actions_document-new',
						selection_optional: true,
						click: function(){
							stock_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Remove',
						extra_class: 'icon picon_16x16_actions_edit-delete',
						click: function(e, rows){
							available_stock_table.pgrid_add(rows.pgrid_export_rows());
							rows.pgrid_delete();
							update_stock();
						}
					}
				]
			});
			<?php } else { ?>
			stock_table.pgrid({
				pgrid_paginate: false
			});
			<?php } ?>
			// Needs to be gridified before it's hidden.
			available_stock_table.pgrid({
				pgrid_paginate: false,
				pgrid_height: '400px;'
			}).pgrid_get_all_rows().pgrid_delete();
			available_stock_table.pgrid_add(available_stock);

			// Stock Dialog
			stock_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 600,
				buttons: {
					"Done": function() {
						var cur_stock_rows = available_stock_table.pgrid_get_selected_rows();
						var cur_stock = cur_stock_rows.pgrid_export_rows();
						if (!cur_stock[0]) {
							alert("Please select stock.");
							return;
						}
						stock_table.pgrid_add(cur_stock);
						cur_stock_rows.pgrid_delete();
						$(this).dialog('close');
					}
				},
				close: function(event, ui) {
					update_stock();
				}
			});
			
			update_stock();
		});
		// ]]>
	</script>
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
		<label><span class="label">Reference #</span>
			<input class="field ui-widget-content" type="text" name="reference_number" size="24" value="<?php echo $this->entity->reference_number; ?>" /></label>
	</div>
	<div class="element">
		<label><span class="label">Destination</span>
			<?php if (!empty($this->entity->received)) { ?>
				<span class="note">Destination cannot be changed after items have been received.</span>
			<?php } ?>
			<select class="field ui-widget-content" name="destination"<?php echo (empty($this->entity->received) ? '' : ' disabled="disabled"'); ?>>
				<?php echo $pines->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->locations, $this->entity->destination->guid); ?>
			</select></label>
	</div>
	<div class="element">
		<label><span class="label">Shipper</span>
			<select class="field ui-widget-content" name="shipper">
				<option value="null">-- None --</option>
				<?php foreach ($this->shippers as $cur_shipper) { ?>
				<option value="<?php echo $cur_shipper->guid; ?>"<?php echo $this->entity->shipper->guid == $cur_shipper->guid ? ' selected="selected"' : ''; ?>><?php echo $cur_shipper->name; ?></option>
				<?php } ?>
			</select></label>
	</div>
	<div class="element">
		<script type="text/javascript">
			// <![CDATA[
			$(function(){
				$("#eta").datepicker({
					dateFormat: "yy-mm-dd"
				});
			});
			// ]]>
		</script>
		<label><span class="label">ETA</span>
			<input class="field ui-widget-content" type="text" id="eta" name="eta" size="24" value="<?php echo ($this->entity->eta ? date('Y-m-d', $this->entity->eta) : ''); ?>" /></label>
	</div>
	<div class="element full_width">
		<span class="label">Stock</span>
		<div class="group">
			<div class="field">
				<table id="stock_table">
					<thead>
						<tr>
							<th>Product</th>
							<th>Serial</th>
							<th>Vendor</th>
							<th>Location</th>
							<th>Cost</th>
							<th>Status</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->entity->stock as $cur_stock) {
								if (is_null($cur_stock->guid))
									continue;
								?>
						<tr title="<?php echo $cur_stock->guid; ?>">
							<td><?php echo $cur_stock->product->name; ?></td>
							<td><?php echo $cur_stock->serial; ?></td>
							<td><?php echo $cur_stock->vendor->name; ?></td>
							<td><?php echo "{$cur_stock->location->name} [{$cur_stock->location->groupname}]"; ?></td>
							<td><?php echo $cur_stock->cost; ?></td>
							<td><?php echo $cur_stock->status; ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<input type="hidden" id="stock" name="stock" size="24" />
		</div>
	</div>
	<div id="stock_dialog" title="Add Stock">
		<table id="available_stock_table">
			<thead>
				<tr>
					<th>Product</th>
					<th>Serial</th>
					<th>Vendor</th>
					<th>Location</th>
					<th>Cost</th>
					<th>Status</th>
				</tr>
			</thead>
			<tbody>
				<tr><td>-----------</td><td>-----------</td><td>-----------</td><td>-----------</td><td>-----------</td><td>-----------</td></tr>
			</tbody>
		</table>
	</div>
	<?php if (!empty($this->entity->received)) { ?>
	<div class="element">
		<span class="label">Received Inventory</span>
		<div class="group">
			<?php foreach ($this->entity->received as $cur_entity) { ?>
			<div class="field" style="margin-bottom: 5px;">
				Product: <?php echo $cur_entity->product->name; ?><br />
				Serial: <?php echo $cur_entity->serial; ?>
			</div>
			<?php } ?>
		</div>
	</div>
	<?php } ?>
	<br class="spacer" />
	<div class="element buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url('com_sales', 'listtransfers'); ?>';" value="Cancel" />
	</div>
</form>