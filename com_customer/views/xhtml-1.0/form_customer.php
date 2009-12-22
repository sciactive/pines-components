<?php
/**
 * Provides a form for the user to edit a customer.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide customer account details in this form.';
?>
<form class="pform" method="post" id="customer_details" action="<?php echo pines_url($this->new_option, $this->new_action); ?>">
	<script type="text/javascript">
		// <![CDATA[
		$(document).ready(function(){
			var addresses = $("#addresses");
			var addresses_table = $("#addresses_table");
			var address_dialog = $("#address_dialog");
			var attributes = $("#attributes");
			var attributes_table = $("#attributes_table");
			var attribute_dialog = $("#attribute_dialog");

			addresses_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Add Address',
						extra_class: 'icon picon_16x16_actions_list-add',
						selection_optional: true,
						click: function(){
							address_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Remove Address',
						extra_class: 'icon picon_16x16_actions_list-remove',
						click: function(e, rows){
							rows.pgrid_delete();
							update_address();
						}
					}
				]
			});

			attributes_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Add Attribute',
						extra_class: 'icon picon_16x16_actions_list-add',
						selection_optional: true,
						click: function(){
							attribute_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Remove Attribute',
						extra_class: 'icon picon_16x16_actions_list-remove',
						click: function(e, rows){
							rows.pgrid_delete();
							update_attributes();
						}
					}
				]
			});

			// Address Dialog
			address_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 600,
				buttons: {
					"Done": function() {
						var cur_address_name = $("#cur_address_name").val();
						var cur_address_addr1 = $("#cur_address_addr1").val();
						var cur_address_addr2 = $("#cur_address_addr2").val();
						var cur_address_city = $("#cur_address_city").val();
						var cur_address_state = $("#cur_address_state").val();
						var cur_address_zip = $("#cur_address_zip").val();
						if (cur_address_name == "" || cur_address_addr1 == "") {
							alert("Please provide a name and a street address.");
							return;
						}
						var new_address = [{
							key: null,
							values: [
								cur_address_name,
								cur_address_addr1,
								cur_address_addr2,
								cur_address_city,
								cur_address_state,
								cur_address_zip
							]
						}];
						addresses_table.pgrid_add(new_address);
						update_addresses();
						$(this).dialog('close');
					}
				}
			});

			// Attribute Dialog
			attribute_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 600,
				buttons: {
					"Done": function() {
						var cur_attribute_name = $("#cur_attribute_name").val();
						var cur_attribute_value = $("#cur_attribute_value").val();
						if (cur_attribute_name == "" || cur_attribute_value == "") {
							alert("Please provide both a name and a value for this attribute.");
							return;
						}
						var new_attribute = [{
							key: null,
							values: [
								cur_attribute_name,
								cur_attribute_value
							]
						}];
						attributes_table.pgrid_add(new_attribute);
						update_attributes();
						$(this).dialog('close');
					}
				}
			});

			function update_addresses() {
				$("#cur_address_name, #cur_address_addr1, #cur_address_addr2, #cur_address_city, #cur_address_state, #cur_address_zip").val("");
				addresses.val(JSON.stringify(addresses_table.pgrid_get_all_rows().pgrid_export_rows()));
			}

			function update_attributes() {
				$("#cur_attribute_name, #cur_attribute_value").val("");
				attributes.val(JSON.stringify(attributes_table.pgrid_get_all_rows().pgrid_export_rows()));
			}

			$("#customer_tabs").tabs();
			update_addresses();
			update_attributes();
		});
		// ]]>
	</script>
	<div id="customer_tabs" style="clear: both;">
		<ul>
			<li><a href="#tab_general">General</a></li>
			<li><a href="#tab_points">Points</a></li>
			<li><a href="#tab_addresses">Addresses</a></li>
			<li><a href="#tab_attributes">Attributes</a></li>
		</ul>
		<div id="tab_general">
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
			<div class="pform_twocol">
				<div class="element">
					<span class="label">Name</span>
					<span class="field"><?php echo $this->entity->name; ?></span>
				</div>
				<div class="element">
					<span class="label">Email</span>
					<span class="field"><?php echo $this->entity->email; ?></span>
				</div>
				<div class="element">
					<span class="label">Home Phone</span>
					<span class="field"><?php echo $this->entity->phone_home; ?></span>
				</div>
				<div class="element">
					<span class="label">Work Phone</span>
					<span class="field"><?php echo $this->entity->phone_work; ?></span>
				</div>
				<div class="element">
					<span class="label">Cell Phone</span>
					<span class="field"><?php echo $this->entity->phone_cell; ?></span>
				</div>
			</div>
			<div class="element">
				<label><span class="label">Login Disabled</span>
					<input class="field" type="checkbox" name="login_disabled" size="20" value="ON"<?php echo $this->entity->com_customer->login_disabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="element">
				<label><span class="label"><?php if (!is_null($this->entity->com_customer->password)) echo 'Update '; ?>Password</span>
					<?php if (is_null($this->entity->com_customer->password)) {
						echo ($config->com_user->empty_pw ? '<span class="note">May be blank.</span>' : '');
					} else {
						echo '<span class="note">Leave blank, if not changing.</span>';
					} ?>
					<input class="field" type="text" name="password" size="20" /></label>
			</div>
			<div class="element full_width">
				<span class="label">Description</span><br />
				<textarea rows="3" cols="35" class="field peditor" style="width: 100%;" name="description"><?php echo $this->entity->com_customer->description; ?></textarea>
			</div>
			<div class="element full_width">
				<span class="label">Short Description</span><br />
				<textarea rows="3" cols="35" class="field peditor_simple" style="width: 100%;" name="short_description"><?php echo $this->entity->com_customer->short_description; ?></textarea>
			</div>
			<br class="spacer" />
		</div>
		<div id="tab_points">
			<div class="element">
				<span class="label">Current Points</span>
				<span class="field"><?php echo $this->entity->com_customer->points; ?></span>
			</div>
			<div class="element">
				<span class="label">Peak Points</span>
				<span class="field"><?php echo $this->entity->com_customer->peak_points; ?></span>
			</div>
			<div class="element">
				<span class="label">Total Points in All Time</span>
				<span class="field"><?php echo $this->entity->com_customer->total_points; ?></span>
			</div>
			<?php if ($config->com_customer->adjustpoints && gatekeeper('com_customer/adjustpoints')) { ?>
			<div class="element">
				<label><span class="label">Adjust Points</span>
					<span class="note">Use a negative value to subtract points.</span>
					<input class="field" type="text" name="adjust_points" size="20" value="0" /></label>
			</div>
			<?php } ?>
			<br class="spacer" />
		</div>
		<div id="tab_addresses">
			<div class="element full_width">
				<span class="label">Addresses</span>
				<div class="group">
					<table id="addresses_table">
						<thead>
							<tr>
								<th>Name</th>
								<th>Address 1</th>
								<th>Address 2</th>
								<th>City</th>
								<th>State</th>
								<th>Zip</th>
							</tr>
						</thead>
						<tbody>
							<?php if (is_array($this->entity->com_customer->addresses)) { foreach ($this->entity->com_customer->addresses as $cur_address) { ?>
							<tr title="<?php echo $cur_address->key; ?>">
								<td><?php echo $cur_address->values[0]; ?></td>
								<td><?php echo $cur_address->values[1]; ?></td>
								<td><?php echo $cur_address->values[2]; ?></td>
								<td><?php echo $cur_address->values[3]; ?></td>
								<td><?php echo $cur_address->values[4]; ?></td>
								<td><?php echo $cur_address->values[5]; ?></td>
							</tr>
							<?php } } ?>
						</tbody>
					</table>
					<input class="field" type="hidden" id="addresses" name="addresses" size="20" />
				</div>
			</div>
			<div id="address_dialog" title="Add an Address">
				<div class="pform">
					<div class="element">
						<label>
							<span class="label">Name</span>
							<input class="field" type="text" size="20" name="cur_address_name" id="cur_address_name" />
						</label>
					</div>
					<div class="element">
						<label>
							<span class="label">Address 1</span>
							<input class="field" type="text" size="20" name="cur_address_addr1" id="cur_address_addr1" />
						</label>
					</div>
					<div class="element">
						<label>
							<span class="label">Address 2</span>
							<input class="field" type="text" size="20" name="cur_address_addr2" id="cur_address_addr2" />
						</label>
					</div>
					<div class="element">
						<label>
							<span class="label">City, State, Zip</span>
							<input class="field" type="text" size="8" name="cur_address_city" id="cur_address_city" />
							<input class="field" type="text" size="2" name="cur_address_state" id="cur_address_state" />
							<input class="field" type="text" size="5" name="cur_address_zip" id="cur_address_zip" />
						</label>
					</div>
				</div>
				<br class="spacer" />
			</div>
			<br class="spacer" />
		</div>
		<div id="tab_attributes">
			<div class="element full_width">
				<span class="label">Attributes</span>
				<div class="group">
					<table id="attributes_table">
						<thead>
							<tr>
								<th>Name</th>
								<th>Value</th>
							</tr>
						</thead>
						<tbody>
							<?php if (is_array($this->entity->com_customer->attributes)) { foreach ($this->entity->com_customer->attributes as $cur_attribute) { ?>
							<tr title="<?php echo $cur_attribute->key; ?>">
								<td><?php echo $cur_attribute->values[0]; ?></td>
								<td><?php echo $cur_attribute->values[1]; ?></td>
							</tr>
							<?php } } ?>
						</tbody>
					</table>
					<input class="field" type="hidden" id="attributes" name="attributes" size="20" />
				</div>
			</div>
			<div id="attribute_dialog" title="Add an Attribute">
				<div style="width: 100%">
					<label>
						<span>Name</span>
						<input type="text" name="cur_attribute_name" id="cur_attribute_name" />
					</label>
					<label>
						<span>Value</span>
						<input type="text" name="cur_attribute_value" id="cur_attribute_value" />
					</label>
				</div>
			</div>
			<br class="spacer" />
		</div>
	</div>
	<br />
	<div class="element buttons">
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url('com_customer', 'listcustomers'); ?>';" value="Cancel" />
	</div>
</form>