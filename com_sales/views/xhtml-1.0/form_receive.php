<?php
/**
 * Provides a form for the user to receive inventory.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Receive Inventory';
$this->note = 'Only use this form to receive inventory into your <strong>current</strong> location.';
?>
<form class="pform" method="post" id="receive_inventory" action="<?php echo pines_url($this->new_option, $this->new_action); ?>">
	<script type="text/javascript">
		// <![CDATA[
		var products;
		var products_table;

		function update_products() {
			var all_rows = products_table.pgrid_get_all_rows().pgrid_export_rows();
			$("#cur_serial").val("");
			products.val(JSON.stringify(all_rows));
		}
		
		$(document).ready(function(){
			products = $("#products");
			products_table = $("#products_table");
			product_dialog = $("#product_dialog");

			products_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Add',
						extra_class: 'icon picon_16x16_actions_document-new',
						selection_optional: true,
						click: function(){
							if (!cur_vendor) {
								$("<div title=\"Alert\">Please select a vendor.</div>").dialog({
									bgiframe: true,
									modal: true,
									buttons: {
										Ok: function(){
											$(this).dialog("close");
										}
									}
								});
								return;
							}
							product_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Edit',
						extra_class: 'icon picon_16x16_actions_document-open',
						double_click: true,
						click: function(e, rows){
							var row_data = products_table.pgrid_export_rows(rows);
							available_products_table.pgrid_select_rows([row_data[0].key]);
							$("#cur_product_quantity").val(row_data[0].values[2]);
							$("#cur_product_cost").val(row_data[0].values[3]);
							product_dialog.dialog('open');
							rows.pgrid_delete();
						}
					},
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
				width: 600,
				buttons: {
					"Done": function() {
						var cur_product_quantity = parseInt($("#cur_product_quantity").val());
						var cur_product_cost = parseFloat($("#cur_product_cost").val());
						var cur_product = available_products_table.pgrid_get_selected_rows().pgrid_export_rows();
						if (!cur_product[0]) {
							$("<div title=\"Alert\">Please select a product.</div>").dialog({
								bgiframe: true,
								modal: true,
								buttons: {
									Ok: function(){
										$(this).dialog("close");
									}
								}
							});
							return;
						}
						if (isNaN(cur_product_quantity) || isNaN(cur_product_cost)) {
							$("<div title=\"Alert\">Please provide both a quantity and a cost for this product.</div>").dialog({
								bgiframe: true,
								modal: true,
								buttons: {
									Ok: function(){
										$(this).dialog("close");
									}
								}
							});
							return;
						}
						var new_product = [{
							key: cur_product[0].key,
							values: [
								cur_product[0].values[0],
								cur_product[0].values[1],
								cur_product_quantity,
								cur_product_cost,
								round_to_dec(cur_product_quantity * cur_product_cost)
							]
						}];
						products_table.pgrid_add(new_product);
						$(this).dialog('close');
					}
				},
				close: function(event, ui) {
					update_products();
				}
			});

			products_table.pgrid_get_all_rows().pgrid_delete();
			update_products();
		});
		// ]]>
	</script>
	<div class="element">
		<label><span class="label">UPC</span>
		<input class="field" type="text" name="upc" size="20" /></label>
	</div>
	<div class="element full_width">
		<span class="label">Products</span>
		<div class="group">
			<div class="field">
				<table id="products_table">
					<thead>
						<tr>
							<th>UPC</th>
							<th>Serial</th>
						</tr>
					</thead>
					<tbody>
						<tr><td>-----------</td><td>-----------</td></tr>
					</tbody>
				</table>
			</div>
			<input class="field" type="hidden" id="products" name="products" size="20" />
		</div>
	</div>
	<div class="element buttons">
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url(); ?>';" value="Cancel" />
	</div>
</form>