<?php
/**
 * Provides a form for the user to receive inventory.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Receive Inventory';
$this->note = 'Only use this form to receive inventory into your <strong>current</strong> location ('.(is_null($_SESSION['user']->group) ? 'No Location' : $_SESSION['user']->group->name).').';
?>
<form class="pf-form" method="post" id="receive_inventory" action="<?php echo htmlentities(pines_url('com_sales', 'receive')); ?>">
	<script type="text/javascript">
		// <![CDATA[
		var products;
		var products_table;
		var product_dialog;
		var product_button;

		function update_products() {
			var all_rows = products_table.pgrid_get_all_rows().pgrid_export_rows();
			$("#cur_serial").val("");
			$("#cur_quantity").val("1");
			products.val(JSON.stringify(all_rows));
		}
		
		$(function(){
			products = $("#products");
			products_table = $("#products_table");
			product_dialog = $("#product_dialog");
			product_button = $("#add_product");

			products_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Remove',
						extra_class: 'icon picon_16x16_actions_edit-delete',
						click: function(e, rows){
							rows.pgrid_delete();
							update_products();
						}
					}
				]
			});

			// Product Dialog
			product_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				buttons: {
					"Done": function() {
						var cur_product = $("#product_code").val();
						var new_product;
						if ($("#serialized").attr("checked")) {
							var cur_serial = $("#cur_serial").val();
							if (!cur_serial) {
								alert("Please provide a serial number.");
								return;
							}
							new_product = [{
								key: cur_serial,
								values: [
									cur_product,
									cur_serial,
									"1"
								]
							}];
						} else {
							var cur_quantity = $("#cur_quantity").val();
							if (!cur_quantity) {
								alert("Please enter a quantity.");
								return;
							}
							new_product = [{
								key: "null",
								values: [
									cur_product,
									"",
									cur_quantity
								]
							}];
						}
						products_table.pgrid_add(new_product);
						$("#product_code").val("").focus();
						product_dialog.dialog('close');
					}
				},
				close: function(event, ui) {
					update_products();
				}
			});

			product_button.click(function(){
				if (!$("#product_code").val()) {
					alert("Please enter a product code to add a product.");
					return;
				}
				product_dialog.dialog('open');
				if ($("#serialized").attr("checked")) {
					$("#serialized_dialog").show();
					$("#unserialized_dialog").hide();
					$("#cur_serial").focus();
				} else {
					$("#serialized_dialog").hide();
					$("#unserialized_dialog").show();
					$("#cur_quantity").focus().get(0).select();
				}
			});
			$("#product_code").keydown(function(eventObject){
				if (eventObject.keyCode == 13) {
					product_button.click();
					return false;
				}
			});
			$("#cur_serial, #cur_quantity").keydown(function(eventObject){
				if (eventObject.keyCode == 13) {
					product_dialog.dialog('option', 'buttons').Done();
					return false;
				}
			});

			products_table.pgrid_get_all_rows().pgrid_delete();
			update_products();
		});
		// ]]>
	</script>
	<div class="pf-element">
		<label><span class="pf-label">Serialized</span>
			<span class="pf-note">Set before you scan. Serialized items require a serial number.</span>
			<input class="pf-field ui-widget-content" type="checkbox" id="serialized" name="serialized" checked="checked" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Product Code</span>
			<input class="pf-field ui-widget-content" type="text" id="product_code" name="product_code" size="24" /></label>
			<input class="pf-button ui-state-default ui-corner-all" type="button" id="add_product" value="Add" />
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Products</span>
		<div class="pf-group">
			<div class="pf-field">
				<table id="products_table">
					<thead>
						<tr>
							<th>Product Code</th>
							<th>Serial</th>
							<th>Quantity</th>
						</tr>
					</thead>
					<tbody>
						<tr><td>-----------</td><td>-----------</td><td>-----------</td></tr>
					</tbody>
				</table>
			</div>
			<input type="hidden" id="products" name="products" size="24" />
		</div>
	</div>
	<div id="product_dialog" title="Receive Inventory">
		<div id="serialized_dialog">
			<label>
				<span>Serial #</span>
				<input type="text" name="cur_serial" id="cur_serial" />
			</label>
		</div>
		<div id="unserialized_dialog">
			<label>
				<span>Quantity</span>
				<input type="text" name="cur_quantity" id="cur_quantity" value="1" />
			</label>
		</div>
	</div>
	<div class="pf-element pf-buttons">
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url()); ?>');" value="Cancel" />
	</div>
</form>