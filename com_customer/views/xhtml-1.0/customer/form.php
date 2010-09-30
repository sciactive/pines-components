<?php
/**
 * Provides a form for the user to edit a customer.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Customer' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide customer profile details in this form.';
$pines->editor->load();
$pines->com_pgrid->load();
$pines->com_customer->load_company_select();
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		<?php if (in_array('address', $pines->config->com_customer->shown_fields_customer)) { ?>
		var addresses = $("#p_muid_addresses");
		var addresses_table = $("#p_muid_addresses_table");
		var address_dialog = $("#p_muid_address_dialog");
		addresses_table.pgrid({
			pgrid_paginate: false,
			pgrid_toolbar: true,
			pgrid_toolbar_contents : [
				{
					type: 'button',
					text: 'Add Address',
					extra_class: 'picon picon-list-add',
					selection_optional: true,
					click: function(){
						address_dialog.dialog('open');
					}
				},
				{
					type: 'button',
					text: 'Remove Address',
					extra_class: 'picon picon-list-remove',
					click: function(e, rows){
						rows.pgrid_delete();
						update_address();
					}
				}
			],
			pgrid_view_height: "300px"
		});

		// Address Dialog
		address_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 600,
			buttons: {
				"Done": function(){
					var cur_address_type = $("#p_muid_cur_address_type").val();
					var cur_address_addr1 = $("#p_muid_cur_address_addr1").val();
					var cur_address_addr2 = $("#p_muid_cur_address_addr2").val();
					var cur_address_city = $("#p_muid_cur_address_city").val();
					var cur_address_state = $("#p_muid_cur_address_state").val();
					var cur_address_zip = $("#p_muid_cur_address_zip").val();
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
					$(this).dialog('close');
				}
			},
			close: function(){
				update_addresses();
			}
		});

		var update_addresses = function(){
			$("#p_muid_cur_address_type, #p_muid_cur_address_addr1, #p_muid_cur_address_addr2, #p_muid_cur_address_city, #p_muid_cur_address_state, #p_muid_cur_address_zip").val("");
			addresses.val(JSON.stringify(addresses_table.pgrid_get_all_rows().pgrid_export_rows()));
		};

		update_addresses();

		<?php } if (in_array('attributes', $pines->config->com_customer->shown_fields_customer)) { ?>
		// Attributes
		var attributes = $("#p_muid_tab_attributes input[name=attributes]");
		var attributes_table = $("#p_muid_tab_attributes .attributes_table");
		var attribute_dialog = $("#p_muid_tab_attributes .attribute_dialog");

		attributes_table.pgrid({
			pgrid_paginate: false,
			pgrid_toolbar: true,
			pgrid_toolbar_contents : [
				{
					type: 'button',
					text: 'Add Attribute',
					extra_class: 'picon picon-list-add',
					selection_optional: true,
					click: function(){
						attribute_dialog.dialog('open');
					}
				},
				{
					type: 'button',
					text: 'Remove Attribute',
					extra_class: 'picon picon-list-remove',
					click: function(e, rows){
						rows.pgrid_delete();
						update_attributes();
					}
				}
			],
			pgrid_view_height: "300px"
		});

		// Attribute Dialog
		attribute_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 500,
			buttons: {
				"Done": function(){
					var cur_attribute_name = attribute_dialog.find("input[name=cur_attribute_name]").val();
					var cur_attribute_value = attribute_dialog.find("input[name=cur_attribute_value]").val();
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
					$(this).dialog('close');
				}
			},
			close: function(){
				update_attributes();
			}
		});

		var update_attributes = function(){
			attribute_dialog.find("input[name=cur_attribute_name]").val("");
			attribute_dialog.find("input[name=cur_attribute_value]").val("");
			attributes.val(JSON.stringify(attributes_table.pgrid_get_all_rows().pgrid_export_rows()));
		};

		update_attributes();
		<?php } ?>

		$("#p_muid_customer_tabs").tabs();

		$("#p_muid_company").companyselect();
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_customer', 'customer/save')); ?>">
	<div id="p_muid_customer_tabs" style="clear: both;">
		<ul>
			<li><a href="#p_muid_tab_general">General</a></li>
			<li><a href="#p_muid_tab_account">Account</a></li>
			<?php if (in_array('address', $pines->config->com_customer->shown_fields_customer)) { ?>
			<li><a href="#p_muid_tab_addresses">Addresses</a></li>
			<?php } if (in_array('attributes', $pines->config->com_customer->shown_fields_customer)) { ?>
			<li><a href="#p_muid_tab_attributes">Attributes</a></li>
			<?php } ?>
		</ul>
		<div id="p_muid_tab_general">
			<?php if (isset($this->entity->guid)) { ?>
			<div class="date_info" style="float: right; text-align: right;">
				<?php if (isset($this->entity->user)) { ?>
				<div>User: <span class="date"><?php echo htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
				<div>Group: <span class="date"><?php echo htmlspecialchars("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
				<?php } ?>
				<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
				<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
			</div>
			<?php } ?>
			<?php if (in_array('name', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="pf-element">
				<label><span class="pf-label">First Name</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name_first" size="24" value="<?php echo htmlspecialchars($this->entity->name_first); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Middle Name</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name_middle" size="24" value="<?php echo htmlspecialchars($this->entity->name_middle); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Last Name</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name_last" size="24" value="<?php echo htmlspecialchars($this->entity->name_last); ?>" /></label>
			</div>
			<?php } if (in_array('ssn', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="pf-element">
				<label><span class="pf-label">SSN</span>
					<span class="pf-note">Without dashes.</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="ssn" size="24" value="<?php echo htmlspecialchars($this->entity->ssn); ?>" /></label>
			</div>
			<?php } if (in_array('dob', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="pf-element">
				<script type="text/javascript">
					// <![CDATA[
					pines(function(){
						$("#p_muid_form [name=dob]").datepicker({
							dateFormat: "yy-mm-dd",
							changeMonth: true,
							changeYear: true,
							showOtherMonths: true,
							selectOtherMonths: true
						});
					});
					// ]]>
				</script>
				<label><span class="pf-label">Date of Birth</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="dob" size="24" value="<?php echo $this->entity->dob ? format_date($this->entity->dob, 'date_sort') : ''; ?>" /></label>
			</div>
			<?php } if (in_array('email', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Email</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="email" size="24" value="<?php echo htmlspecialchars($this->entity->email); ?>" /></label>
			</div>
			<?php } if (in_array('company', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Company</span>
					<span class="pf-note">Enter part of a company name, email, or phone # to search.</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" id="p_muid_company" name="company" size="24" value="<?php echo $this->entity->company->guid ? htmlspecialchars("{$this->entity->company->guid}: \"{$this->entity->company->name}\"") : ''; ?>" />
				</label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Job Title</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="job_title" size="24" value="<?php echo htmlspecialchars($this->entity->job_title); ?>" /></label>
			</div>
			<?php } if (in_array('phone', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Cell Phone</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="phone_cell" size="24" value="<?php echo format_phone($this->entity->phone_cell); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Work Phone</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="phone_work" size="24" value="<?php echo format_phone($this->entity->phone_work); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Home Phone</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="phone_home" size="24" value="<?php echo format_phone($this->entity->phone_home); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Fax</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="fax" size="24" value="<?php echo format_phone($this->entity->fax); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Timezone</span>
					<span class="pf-note">This overrides the primary group's timezone.</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="timezone" size="1">
						<option value="">--Inherit From Group--</option>
						<?php
						$tz = DateTimeZone::listIdentifiers();
						sort($tz);
						foreach ($tz as $cur_tz) {
							?><option value="<?php echo htmlspecialchars($cur_tz); ?>"<?php echo $this->entity->timezone == $cur_tz ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_tz); ?></option><?php
						} ?>
					</select>
				</label>
			</div>
			<?php } if (in_array('referrer', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Referrer</span>
					<span class="pf-note">Where did you hear about us?</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="referrer">
						<option value="">-- Please Select --</option>
						<?php foreach ($pines->config->com_customer->referrer_values as $cur_value) { ?>
						<option value="<?php echo htmlspecialchars($cur_value); ?>"<?php echo ($this->entity->referrer == $cur_value) ? ' selected="selected"' : ''; ?> /><?php echo htmlspecialchars($cur_value); ?></option>
						<?php } ?>
					</select></label>
			</div>
			<?php } if (in_array('description', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="pf-element pf-full-width">
				<span class="pf-label">Description</span><br />
				<textarea rows="3" cols="35" class="pf-field peditor" style="width: 100%;" name="description"><?php echo $this->entity->description; ?></textarea>
			</div>
			<?php } ?>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_account">
			<div class="pf-element">
				<label><span class="pf-label">Username</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="username" size="24" value="<?php echo htmlspecialchars($this->entity->username); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Login Enabled</span>
					<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->has_tag('enabled') ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<?php if (in_array('password', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="pf-element">
				<label><span class="pf-label"><?php if (isset($this->entity->password)) echo 'Update '; ?>Password</span>
					<?php if (!isset($this->entity->password)) {
						echo ($pines->config->com_user->pw_empty ? '<span class="pf-note">May be blank.</span>' : '');
					} else {
						echo '<span class="pf-note">Leave blank, if not changing.</span>';
					} ?>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="password" size="24" /></label>
			</div>
			<?php } if (in_array('points', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="pf-element pf-heading">
				<h1>Points</h1>
			</div>
			<div class="pf-element">
				<span class="pf-label">Current Points</span>
				<span class="pf-field"><?php echo htmlspecialchars($this->entity->points); ?></span>
			</div>
			<div class="pf-element">
				<span class="pf-label">Peak Points</span>
				<span class="pf-note">The highest amount of points the customer has ever had.</span>
				<span class="pf-field"><?php echo htmlspecialchars($this->entity->peak_points); ?></span>
			</div>
			<div class="pf-element">
				<span class="pf-label">Total Points in All Time</span>
				<span class="pf-note">The total amount of points the customer has ever had.</span>
				<span class="pf-field"><?php echo htmlspecialchars($this->entity->total_points); ?></span>
			</div>
			<?php if ($pines->config->com_customer->adjustpoints && gatekeeper('com_customer/adjustpoints')) { ?>
			<div class="pf-element">
				<label><span class="pf-label">Adjust Points</span>
					<span class="pf-note">Use a negative value to subtract points.</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="adjust_points" size="24" value="0" /></label>
			</div>
			<?php } } if (in_array('membership', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="pf-element pf-heading">
				<h1>Membership</h1>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Member</span>
					<input class="pf-field" type="checkbox" name="member" value="ON"<?php echo $this->entity->member ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<?php if ($this->entity->member) { ?>
			<div class="pf-element">
				<span class="pf-label">Member Since</span>
				<span class="pf-field"><?php echo format_date($this->entity->member_since, 'full_long'); ?></span>
			</div>
			<?php } ?>
			<div class="pf-element">
				<script type="text/javascript">
					// <![CDATA[
					pines(function(){
						$("#p_muid_form [name=member_exp]").datepicker({
							dateFormat: "yy-mm-dd",
							changeMonth: true,
							changeYear: true,
							showOtherMonths: true,
							selectOtherMonths: true
						});
					});
					// ]]>
				</script>
				<label><span class="pf-label">Membership Expiration</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="member_exp" size="24" value="<?php echo $this->entity->member_exp ? format_date($this->entity->member_exp, 'date_sort') : ''; ?>" /></label>
			</div>
			<?php } ?>
			<br class="pf-clearing" />
		</div>
		<?php if (in_array('address', $pines->config->com_customer->shown_fields_customer)) { ?>
		<div id="p_muid_tab_addresses">
			<div class="pf-element pf-heading">
				<h1>Main Address</h1>
			</div>
			<div class="pf-element">
				<script type="text/javascript">
					// <![CDATA[
					pines(function(){
						var address_us = $("#p_muid_address_us");
						var address_international = $("#p_muid_address_international");
						$("#p_muid_form [name=address_type]").change(function(){
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
				<span class="pf-label">Address Type</span>
				<label><input class="pf-field" type="radio" name="address_type" value="us"<?php echo ($this->entity->address_type == 'us') ? ' checked="checked"' : ''; ?> /> US</label>
				<label><input class="pf-field" type="radio" name="address_type" value="international"<?php echo $this->entity->address_type == 'international' ? ' checked="checked"' : ''; ?> /> International</label>
			</div>
			<div id="p_muid_address_us" style="display: none;">
				<div class="pf-element">
					<label><span class="pf-label">Address 1</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="address_1" size="24" value="<?php echo htmlspecialchars($this->entity->address_1); ?>" /></label>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Address 2</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="address_2" size="24" value="<?php echo htmlspecialchars($this->entity->address_2); ?>" /></label>
				</div>
				<div class="pf-element">
					<span class="pf-label">City, State</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="city" size="15" value="<?php echo htmlspecialchars($this->entity->city); ?>" />
					<select class="pf-field ui-widget-content ui-corner-all" name="state">
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
				<div class="pf-element">
					<label><span class="pf-label">Zip</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="zip" size="24" value="<?php echo htmlspecialchars($this->entity->zip); ?>" /></label>
				</div>
			</div>
			<div id="p_muid_address_international" style="display: none;">
				<div class="pf-element pf-full-width">
					<label><span class="pf-label">Address</span>
						<span class="pf-field pf-full-width"><textarea class="ui-widget-content ui-corner-all" style="width: 100%;" rows="3" cols="35" name="address_international"><?php echo $this->entity->address_international; ?></textarea></span></label>
				</div>
			</div>
			<div class="pf-element pf-heading">
				<h1>Additional Addresses</h1>
			</div>
			<div class="pf-element pf-full-width">
				<table id="p_muid_addresses_table">
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
						<?php foreach ($this->entity->addresses as $cur_address) { ?>
						<tr>
							<td><?php echo htmlspecialchars($cur_address['type']); ?></td>
							<td><?php echo htmlspecialchars($cur_address['address_1']); ?></td>
							<td><?php echo htmlspecialchars($cur_address['address_2']); ?></td>
							<td><?php echo htmlspecialchars($cur_address['city']); ?></td>
							<td><?php echo htmlspecialchars($cur_address['state']); ?></td>
							<td><?php echo htmlspecialchars($cur_address['zip']); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" id="p_muid_addresses" name="addresses" size="24" />
			</div>
			<div id="p_muid_address_dialog" title="Add an Address">
				<div class="pf-form">
					<div class="pf-element">
						<label>
							<span class="pf-label">Type</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" size="24" name="cur_address_type" id="p_muid_cur_address_type" />
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Address 1</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" size="24" name="cur_address_addr1" id="p_muid_cur_address_addr1" />
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Address 2</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" size="24" name="cur_address_addr2" id="p_muid_cur_address_addr2" />
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">City, State, Zip</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" size="8" name="cur_address_city" id="p_muid_cur_address_city" />
							<input class="pf-field ui-widget-content ui-corner-all" type="text" size="2" name="cur_address_state" id="p_muid_cur_address_state" />
							<input class="pf-field ui-widget-content ui-corner-all" type="text" size="5" name="cur_address_zip" id="p_muid_cur_address_zip" />
						</label>
					</div>
				</div>
				<br class="pf-clearing" />
			</div>
			<br class="pf-clearing" />
		</div>
		<?php } if (in_array('attributes', $pines->config->com_customer->shown_fields_customer)) { ?>
		<div id="p_muid_tab_attributes">
			<div class="pf-element pf-full-width">
				<table class="attributes_table">
					<thead>
						<tr>
							<th>Name</th>
							<th>Value</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->entity->attributes as $cur_attribute) { ?>
						<tr>
							<td><?php echo htmlspecialchars($cur_attribute['name']); ?></td>
							<td><?php echo htmlspecialchars($cur_attribute['value']); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="attributes" />
			</div>
			<div class="attribute_dialog" style="display: none;" title="Add an Attribute">
				<div class="pf-form">
					<div class="pf-element">
						<label>
							<span class="pf-label">Name</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_attribute_name" size="24" />
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Value</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_attribute_value" size="24" />
						</label>
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
			</div>
			<br class="pf-clearing" />
		</div>
		<?php } ?>
	</div>
	<div class="pf-element pf-buttons">
		<br />
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_customer', 'customer/list')); ?>');" value="Cancel" />
	</div>
</form>