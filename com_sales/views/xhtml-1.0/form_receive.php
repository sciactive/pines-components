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
						var cur_serial = $("#cur_serial").val();
						var cur_product = $("#upc").val();
						if (!cur_serial) {
							$("<div title=\"Alert\">Please provide a serial number.</div>").dialog({
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
							key: cur_serial,
							values: [
								cur_product,
								cur_serial
							]
						}];
						products_table.pgrid_add(new_product);
						$("#upc").val("");
						$(this).dialog('close');
					}
				},
				close: function(event, ui) {
					update_products();
				}
			});

			$("#add_product").click(function(){
				if (!$("#upc").val()) {
					$("<div title=\"Alert\">Please enter a UPC to add a product.</div>").dialog({
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
			});

			products_table.pgrid_get_all_rows().pgrid_delete();
			update_products();
			$("#receive_tabs").tabs();
		});
		// ]]>
	</script>
	<div id="receive_tabs" style="clear: both;">
		<ul>
			<li><a href="#tab_standard">Standard Entry</a></li>
			<li><a href="#tab_bulk">Bulk Entry</a></li>
		</ul>
		<div id="tab_standard">
			<div class="element">
				<label><span class="label">UPC</span>
				<input class="field" type="text" id="upc" name="upc" size="20" /></label>
				<input class="button ui-state-default ui-corner-all" type="button" id="add_product" value="Add" />
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
			<div id="product_dialog" title="Receive Inventory">
				<label>
					<span>Serial #</span>
					<input type="text" name="cur_serial" id="cur_serial" />
				</label>
			</div>
			<br class="spacer" />
		</div>
		<div id="tab_bulk">
			<div class="element full_width">
				<span class="label">UPC/Serial Combos</span>
				<span class="note">Enter one UPC, a new line, the corresponding serial, then a new line, and repeat.</span>
				<div class="group">
					<textarea rows="8" cols="35" style="width: 100%;" name="upc_bulk"></textarea>
				</div>
			</div>
			<br class="spacer" />
		</div>
	</div>
	<br />
	<div class="element buttons">
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url(); ?>';" value="Cancel" />
	</div>
</form>