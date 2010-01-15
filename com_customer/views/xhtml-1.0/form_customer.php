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
$this->title = (is_null($this->entity->guid)) ? 'Editing New Customer' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide customer account details in this form.';
?>
<form class="pform" method="post" id="customer_details" action="<?php echo pines_url('com_customer', 'savecustomer'); ?>">
	<script type="text/javascript">
		// <![CDATA[
		$(function(){
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
						var cur_address_type = $("#cur_address_type").val();
						var cur_address_addr1 = $("#cur_address_addr1").val();
						var cur_address_addr2 = $("#cur_address_addr2").val();
						var cur_address_city = $("#cur_address_city").val();
						var cur_address_state = $("#cur_address_state").val();
						var cur_address_zip = $("#cur_address_zip").val();
						if (cur_address_type == "" || cur_address_addr1 == "") {
							alert("Please provide a name and a street address.");
							return;
						}
						var new_address = [{
							key: null,
							values: [
								cur_address_type,
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
				$("#cur_address_type, #cur_address_addr1, #cur_address_addr2, #cur_address_city, #cur_address_state, #cur_address_zip").val("");
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
			<div class="element">
				<label><span class="label">First Name</span>
					<input class="field" type="text" name="name_first" size="24" value="<?php echo $this->entity->name_first; ?>" /></label>
			</div>
			<div class="element">
				<label><span class="label">Last Name</span>
					<input class="field" type="text" name="name_last" size="24" value="<?php echo $this->entity->name_last; ?>" /></label>
			</div>
			<?php if ($config->com_customer->ssn_field) { ?>
			<div class="element">
				<label><span class="label">SSN</span>
					<input class="field" type="text" name="ssn" size="24" value="<?php echo $this->entity->ssn; ?>" /></label>
			</div>
			<?php } ?>
			<div class="element">
				<label><span class="label">Email</span>
					<input class="field" type="text" name="email" size="24" value="<?php echo $this->entity->email; ?>" /></label>
			</div>
			<div class="element">
				<label><span class="label">Company</span>
					<input class="field" type="text" name="company" size="24" value="<?php echo $this->entity->company; ?>" /></label>
			</div>
			<div class="element">
				<label><span class="label">Job Title</span>
					<input class="field" type="text" name="job_title" size="24" value="<?php echo $this->entity->job_title; ?>" /></label>
			</div>
			<div class="element">
				<script type="text/javascript">
					// <![CDATA[
					$(function(){
						var address_us = $("#address_us");
						var address_international = $("#address_international");
						$("#customer_details [name=address_type]").change(function(){
							var address_type = $(this);
							if (address_type.is(":checked") && address_type.val() == "us") {
								address_us.show();
								address_international.hide();
							} else if (address_type.is(":checked") && address_type.val() == "international") {
								address_international.show();
								address_us.hide();
							}
						}).change();
					});
					// ]]>
				</script>
				<span class="label">Address Type</span>
				<label><input class="field" type="radio" name="address_type" value="us"<?php echo ($this->entity->address_type == 'us' || empty($this->entity->address_type)) ? ' checked="checked"' : ''; ?> /> US</label>
				<label><input class="field" type="radio" name="address_type" value="international"<?php echo $this->entity->address_type == 'international' ? ' checked="checked"' : ''; ?> /> International</label>
			</div>
			<div id="address_us" style="display: none;">
				<div class="element">
					<label><span class="label">Address 1</span>
						<input class="field" type="text" name="address_1" size="24" value="<?php echo $this->entity->address_1; ?>" /></label>
				</div>
				<div class="element">
					<label><span class="label">Address 2</span>
						<input class="field" type="text" name="address_2" size="24" value="<?php echo $this->entity->address_2; ?>" /></label>
				</div>
				<div class="element">
					<span class="label">City, State</span>
					<input class="field" type="text" name="city" size="15" value="<?php echo $this->entity->city; ?>" />
					<select name="state">
						<option value="">None</option>
						<?php foreach (array(
								'AL' => 'Alabama',
								'AK' => 'Alaska',
								'AZ' => 'Arizona',
								'AR' => 'Arkansas',
								'CA' => 'California',
								'CO' => 'Colorado',
								'CT' => 'Connecticut',
								'DE' => 'Delaware',
								'DC' => 'DC',
								'FL' => 'Florida',
								'GA' => 'Georgia',
								'HI' => 'Hawaii',
								'ID' => 'Idaho',
								'IL' => 'Illinois',
								'IN' => 'Indiana',
								'IA' => 'Iowa',
								'KS' => 'Kansas',
								'KY' => 'Kentucky',
								'LA' => 'Louisiana',
								'ME' => 'Maine',
								'MD' => 'Maryland',
								'MA' => 'Massachusetts',
								'MI' => 'Michigan',
								'MN' => 'Minnesota',
								'MS' => 'Mississippi',
								'MO' => 'Missouri',
								'MT' => 'Montana',
								'NE' => 'Nebraska',
								'NV' => 'Nevada',
								'NH' => 'New Hampshire',
								'NJ' => 'New Jersey',
								'NM' => 'New Mexico',
								'NY' => 'New York',
								'NC' => 'North Carolina',
								'ND' => 'North Dakota',
								'OH' => 'Ohio',
								'OK' => 'Oklahoma',
								'OR' => 'Oregon',
								'PA' => 'Pennsylvania',
								'RI' => 'Rhode Island',
								'SC' => 'South Carolina',
								'SD' => 'South Dakota',
								'TN' => 'Tennessee',
								'TX' => 'Texas',
								'UT' => 'Utah',
								'VT' => 'Vermont',
								'VA' => 'Virginia',
								'WA' => 'Washington',
								'WV' => 'West Virginia',
								'WI' => 'Wisconsin',
								'WY' => 'Wyoming'
							) as $key => $cur_state) { ?>
						<option value="<?php echo $key; ?>"<?php echo $this->entity->state == $key ? ' selected="selected"' : ''; ?>><?php echo $cur_state; ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="element">
					<label><span class="label">Zip</span>
						<input class="field" type="text" name="zip" size="24" value="<?php echo $this->entity->zip; ?>" /></label>
				</div>
			</div>
			<div id="address_international" style="display: none;">
				<div class="element full_width">
				<label><span class="label">Address</span>
					<span class="field full_width"><textarea style="width: 100%;" rows="3" cols="35" name="address_international"><?php echo $this->entity->address_international; ?></textarea></span></label>
				</div>
			</div>
			<div class="element">
				<label><span class="label">Cell Phone</span>
					<input class="field" type="text" name="phone_cell" size="24" value="<?php echo $this->entity->phone_cell; ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="element">
				<label><span class="label">Work Phone</span>
					<input class="field" type="text" name="phone_work" size="24" value="<?php echo $this->entity->phone_work; ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="element">
				<label><span class="label">Home Phone</span>
					<input class="field" type="text" name="phone_home" size="24" value="<?php echo $this->entity->phone_home; ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="element">
				<label><span class="label">Fax</span>
					<input class="field" type="text" name="fax" size="24" value="<?php echo $this->entity->fax; ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="element">
				<label><span class="label">Login Disabled</span>
					<input class="field" type="checkbox" name="login_disabled" size="24" value="ON"<?php echo $this->entity->com_customer->login_disabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="element">
				<label><span class="label"><?php if (!is_null($this->entity->com_customer->password)) echo 'Update '; ?>Password</span>
					<?php if (is_null($this->entity->com_customer->password)) {
						echo ($config->com_user->empty_pw ? '<span class="note">May be blank.</span>' : '');
					} else {
						echo '<span class="note">Leave blank, if not changing.</span>';
					} ?>
					<input class="field" type="text" name="password" size="24" /></label>
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
					<input class="field" type="text" name="adjust_points" size="24" value="0" /></label>
			</div>
			<?php } ?>
			<br class="spacer" />
		</div>
		<div id="tab_addresses">
			<div class="element">
				<span class="label">Main Address</span>
				<div class="group">
					<div class="field">
						<?php if ($this->entity->address_type == 'us') { ?>
							<?php echo $this->entity->address_1; ?><br />
							<?php if (isset($this->entity->address_2)) { ?>
								<?php echo $this->entity->address_2; ?><br />
							<?php } ?>
							<?php echo $this->entity->city; ?>, <?php echo $this->entity->state; ?> <?php echo $this->entity->zip; ?>
						<?php } else { ?>
							<?php echo str_replace("\n", "<br />\n", htmlentities($this->entity->address_international)); ?>
						<?php } ?>
					</div>
				</div>
			</div>
			<div class="element full_width">
				<span class="label">Additional Addresses</span>
				<div class="group">
					<table id="addresses_table">
						<thead>
							<tr>
								<th>Type</th>
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
					<input class="field" type="hidden" id="addresses" name="addresses" size="24" />
				</div>
			</div>
			<div id="address_dialog" title="Add an Address">
				<div class="pform">
					<div class="element">
						<label>
							<span class="label">Type</span>
							<input class="field" type="text" size="24" name="cur_address_type" id="cur_address_type" />
						</label>
					</div>
					<div class="element">
						<label>
							<span class="label">Address 1</span>
							<input class="field" type="text" size="24" name="cur_address_addr1" id="cur_address_addr1" />
						</label>
					</div>
					<div class="element">
						<label>
							<span class="label">Address 2</span>
							<input class="field" type="text" size="24" name="cur_address_addr2" id="cur_address_addr2" />
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
					<input class="field" type="hidden" id="attributes" name="attributes" />
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