<?php
/**
 * Provides a form for the user to edit a customer.
 *
 * @package Pines
 * @subpackage com_customer
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'Editing New Customer' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide customer profile details in this form.';
?>
<form class="pform" method="post" id="customer_details" action="<?php echo htmlentities(pines_url('com_customer', 'savecustomer')); ?>">
	<script type="text/javascript">
		// <![CDATA[
		$(function(){
			<?php if (in_array('address', $pines->config->com_customer->shown_fields_customer)) { ?>
			var addresses = $("#addresses");
			var addresses_table = $("#addresses_table");
			var address_dialog = $("#address_dialog");
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
			
			function update_addresses() {
				$("#cur_address_type, #cur_address_addr1, #cur_address_addr2, #cur_address_city, #cur_address_state, #cur_address_zip").val("");
				addresses.val(JSON.stringify(addresses_table.pgrid_get_all_rows().pgrid_export_rows()));
			}

			update_addresses();

			<?php } if (in_array('attributes', $pines->config->com_customer->shown_fields_customer)) { ?>
			var attributes = $("#attributes");
			var attributes_table = $("#attributes_table");
			var attribute_dialog = $("#attribute_dialog");

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

			function update_attributes() {
				$("#cur_attribute_name, #cur_attribute_value").val("");
				attributes.val(JSON.stringify(attributes_table.pgrid_get_all_rows().pgrid_export_rows()));
			}
			
			update_attributes();
			<?php } ?>
				
			$("#customer_tabs").tabs();
		});
		// ]]>
	</script>
	<div id="customer_tabs" style="clear: both;">
		<ul>
			<li><a href="#tab_general">General</a></li>
			<li><a href="#tab_account">Account</a></li>
			<?php if (in_array('address', $pines->config->com_customer->shown_fields_customer)) { ?>
			<li><a href="#tab_addresses">Addresses</a></li>
			<?php } if (in_array('attributes', $pines->config->com_customer->shown_fields_customer)) { ?>
			<li><a href="#tab_attributes">Attributes</a></li>
			<?php } ?>
		</ul>
		<div id="tab_general">
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
			<?php } if (in_array('name', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="element">
				<label><span class="label">First Name</span>
					<input class="field ui-widget-content" type="text" name="name_first" size="24" value="<?php echo $this->entity->name_first; ?>" /></label>
			</div>
			<div class="element">
				<label><span class="label">Middle Name</span>
					<input class="field ui-widget-content" type="text" name="name_middle" size="24" value="<?php echo $this->entity->name_middle; ?>" /></label>
			</div>
			<div class="element">
				<label><span class="label">Last Name</span>
					<input class="field ui-widget-content" type="text" name="name_last" size="24" value="<?php echo $this->entity->name_last; ?>" /></label>
			</div>
			<?php } if (in_array('ssn', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="element">
				<label><span class="label">SSN</span>
					<span class="note">Without dashes.</span>
					<input class="field ui-widget-content" type="text" name="ssn" size="24" value="<?php echo $this->entity->ssn; ?>" /></label>
			</div>
			<?php } if (in_array('dob', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="element">
				<script type="text/javascript">
					// <![CDATA[
					$(function(){
						$("#customer_details [name=dob]").datepicker({
							dateFormat: "yy-mm-dd",
							changeMonth: true,
							changeYear: true
						});
					});
					// ]]>
				</script>
				<label><span class="label">Date of Birth</span>
					<input class="field ui-widget-content" type="text" name="dob" size="24" value="<?php echo $this->entity->dob ? date('Y-m-d', $this->entity->dob) : ''; ?>" /></label>
			</div>
			<?php } if (in_array('email', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="element">
				<label><span class="label">Email</span>
					<input class="field ui-widget-content" type="text" name="email" size="24" value="<?php echo $this->entity->email; ?>" /></label>
			</div>
			<?php } if (in_array('company', $pines->config->com_customer->shown_fields_customer)) { ?>
			<script type="text/javascript">
				// <![CDATA[
				var company_box;
				var company_search_box;
				var company_search_button;
				var company_table;
				var company_dialog;

				$(function(){
					company_box = $("#company");
					company_search_box = $("#company_search");
					company_search_button = $("#company_search_button");
					company_table = $("#company_table");
					company_dialog = $("#company_dialog");

					company_search_box.keydown(function(eventObject){
						if (eventObject.keyCode == 13) {
							company_search(this.value);
							return false;
						}
					});
					company_search_button.click(function(){
						company_search(company_search_box.val());
					});

					company_table.pgrid({
						pgrid_paginate: true,
						pgrid_multi_select: false,
						pgrid_double_click: function(){
							company_dialog.dialog('option', 'buttons').Done();
						}
					});

					company_dialog.dialog({
						bgiframe: true,
						autoOpen: false,
						modal: true,
						width: 600,
						buttons: {
							"Done": function(){
								var rows = company_table.pgrid_get_selected_rows().pgrid_export_rows();
								if (!rows[0]) {
									alert("Please select a company.");
									return;
								} else {
									var company = rows[0];
								}
								company_box.val(company.key+": \""+company.values[0]+"\"");
								company_search_box.val("");
								company_dialog.dialog('close');
							}
						}
					});
				});

				function company_search(search_string) {
					var loader;
					$.ajax({
						url: "<?php echo pines_url("com_customer", "companysearch"); ?>",
						type: "POST",
						dataType: "json",
						data: {"q": search_string},
						beforeSend: function(){
							loader = pines.alert('Searching for companies...', 'Company Search', 'icon picon_16x16_animations_throbber', {pnotify_hide: false, pnotify_history: false});
							company_table.pgrid_get_all_rows().pgrid_delete();
						},
						complete: function(){
							loader.pnotify_remove();
						},
						error: function(XMLHttpRequest, textStatus){
							pines.error("An error occured while trying to find customers:\n"+XMLHttpRequest.status+": "+textStatus);
						},
						success: function(data){
							if (!data) {
								alert("No companies were found that matched the query.");
								return;
							}
							company_dialog.dialog('open');
							company_table.pgrid_add(data);
						}
					});
				}
				// ]]>
			</script>
			<div class="element">
				<label for="company_search"><span class="label">Company</span>
					<span class="note">Enter part of a company name, email, or phone # to search.</span>
				</label>
				<div class="group">
					<input class="field ui-widget-content" type="text" id="company" name="company" size="24" onfocus="this.blur();" value="<?php echo htmlentities($this->entity->company->guid ? "{$this->entity->company->guid}: \"{$this->entity->company->name}\"" : 'No Company Selected'); ?>" />
					<br />
					<input class="field ui-widget-content" type="text" id="company_search" name="company_search" size="24" />
					<button type="button" id="company_search_button"><span class="picon_16x16_actions_system-search" style="padding-left: 16px; background-repeat: no-repeat;">Search</span></button>
				</div>
			</div>
			<div id="company_dialog" title="Pick a Company">
				<table id="company_table">
					<thead>
						<tr>
							<th>Name</th>
							<th>Address</th>
							<th>City</th>
							<th>State</th>
							<th>Zip</th>
							<th>Email</th>
							<th>Phone</th>
							<th>Fax</th>
							<th>Website</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>----------------------</td>
							<td>----------------------</td>
							<td>----------------------</td>
							<td>----------------------</td>
							<td>----------------------</td>
							<td>----------------------</td>
							<td>----------------------</td>
							<td>----------------------</td>
							<td>----------------------</td>
						</tr>
					</tbody>
				</table>
				<br class="spacer" />
			</div>
			<div class="element">
				<label><span class="label">Job Title</span>
					<input class="field ui-widget-content" type="text" name="job_title" size="24" value="<?php echo $this->entity->job_title; ?>" /></label>
			</div>
			<?php } if (in_array('phone', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="element">
				<label><span class="label">Cell Phone</span>
					<input class="field ui-widget-content" type="text" name="phone_cell" size="24" value="<?php echo pines_phone_format($this->entity->phone_cell); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="element">
				<label><span class="label">Work Phone</span>
					<input class="field ui-widget-content" type="text" name="phone_work" size="24" value="<?php echo pines_phone_format($this->entity->phone_work); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="element">
				<label><span class="label">Home Phone</span>
					<input class="field ui-widget-content" type="text" name="phone_home" size="24" value="<?php echo pines_phone_format($this->entity->phone_home); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="element">
				<label><span class="label">Fax</span>
					<input class="field ui-widget-content" type="text" name="fax" size="24" value="<?php echo pines_phone_format($this->entity->fax); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<?php } ?>
			<div class="element">
				<label><span class="label">Login Disabled</span>
					<input class="field ui-widget-content" type="checkbox" name="login_disabled" size="24" value="ON"<?php echo $this->entity->login_disabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<?php if (in_array('password', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="element">
				<label><span class="label"><?php if (!is_null($this->entity->password)) echo 'Update '; ?>Password</span>
					<?php if (is_null($this->entity->password)) {
						echo ($pines->config->com_user->empty_pw ? '<span class="note">May be blank.</span>' : '');
					} else {
						echo '<span class="note">Leave blank, if not changing.</span>';
					} ?>
					<input class="field ui-widget-content" type="text" name="password" size="24" value="<?php echo $this->entity->tmp_password; ?>" /></label>
			</div>
			<?php } if (in_array('description', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="element full_width">
				<span class="label">Description</span><br />
				<textarea rows="3" cols="35" class="field peditor" style="width: 100%;" name="description"><?php echo $this->entity->description; ?></textarea>
			</div>
			<?php } ?>
			<br class="spacer" />
		</div>
		<div id="tab_account">
			<?php if (in_array('points', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="element heading">
				<h1>Points</h1>
			</div>
			<div class="element">
				<span class="label">Current Points</span>
				<span class="field"><?php echo $this->entity->points; ?></span>
			</div>
			<div class="element">
				<span class="label">Peak Points</span>
				<span class="note">The highest amount of points the customer has ever had.</span>
				<span class="field"><?php echo $this->entity->peak_points; ?></span>
			</div>
			<div class="element">
				<span class="label">Total Points in All Time</span>
				<span class="note">The total amount of points the customer has ever had.</span>
				<span class="field"><?php echo $this->entity->total_points; ?></span>
			</div>
			<?php if ($pines->config->com_customer->adjustpoints && gatekeeper('com_customer/adjustpoints')) { ?>
			<div class="element">
				<label><span class="label">Adjust Points</span>
					<span class="note">Use a negative value to subtract points.</span>
					<input class="field ui-widget-content" type="text" name="adjust_points" size="24" value="0" /></label>
			</div>
			<?php } } if (in_array('membership', $pines->config->com_customer->shown_fields_customer)) { ?>
			<div class="element heading">
				<h1>Membership</h1>
			</div>
			<div class="element">
				<label><span class="label">Member</span>
					<input class="field ui-widget-content" type="checkbox" name="member" size="24" value="ON"<?php echo $this->entity->member ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<?php if ($this->entity->member) { ?>
			<div class="element">
				<span class="label">Member Since</span>
				<span class="field"><?php echo pines_date_format($this->entity->member_since); ?></span>
			</div>
			<?php } ?>
			<div class="element">
				<script type="text/javascript">
					// <![CDATA[
					$(function(){
						$("#customer_details [name=member_exp]").datepicker({
							dateFormat: "yy-mm-dd",
							changeMonth: true,
							changeYear: true
						});
					});
					// ]]>
				</script>
				<label><span class="label">Membership Expiration</span>
					<input class="field ui-widget-content" type="text" name="member_exp" size="24" value="<?php echo $this->entity->member_exp ? date('Y-m-d', $this->entity->member_exp) : ''; ?>" /></label>
			</div>
			<?php } ?>
			<br class="spacer" />
		</div>
		<?php if (in_array('address', $pines->config->com_customer->shown_fields_customer)) { ?>
		<div id="tab_addresses">
			<div class="element heading">
				<h1>Main Address</h1>
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
				<label><input class="field ui-widget-content" type="radio" name="address_type" value="us"<?php echo ($this->entity->address_type == 'us') ? ' checked="checked"' : ''; ?> /> US</label>
				<label><input class="field ui-widget-content" type="radio" name="address_type" value="international"<?php echo $this->entity->address_type == 'international' ? ' checked="checked"' : ''; ?> /> International</label>
			</div>
			<div id="address_us" style="display: none;">
				<div class="element">
					<label><span class="label">Address 1</span>
						<input class="field ui-widget-content" type="text" name="address_1" size="24" value="<?php echo $this->entity->address_1; ?>" /></label>
				</div>
				<div class="element">
					<label><span class="label">Address 2</span>
						<input class="field ui-widget-content" type="text" name="address_2" size="24" value="<?php echo $this->entity->address_2; ?>" /></label>
				</div>
				<div class="element">
					<span class="label">City, State</span>
					<input class="field ui-widget-content" type="text" name="city" size="15" value="<?php echo $this->entity->city; ?>" />
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
						<input class="field ui-widget-content" type="text" name="zip" size="24" value="<?php echo $this->entity->zip; ?>" /></label>
				</div>
			</div>
			<div id="address_international" style="display: none;">
				<div class="element full_width">
				<label><span class="label">Address</span>
					<span class="field full_width"><textarea style="width: 100%;" rows="3" cols="35" name="address_international"><?php echo $this->entity->address_international; ?></textarea></span></label>
				</div>
			</div>
			<div class="element heading">
				<h1>Additional Addresses</h1>
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
							<?php foreach ($this->entity->addresses as $cur_address) { ?>
							<tr>
								<td><?php echo $cur_address['type']; ?></td>
								<td><?php echo $cur_address['address_1']; ?></td>
								<td><?php echo $cur_address['address_2']; ?></td>
								<td><?php echo $cur_address['city']; ?></td>
								<td><?php echo $cur_address['state']; ?></td>
								<td><?php echo $cur_address['zip']; ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					<input type="hidden" id="addresses" name="addresses" size="24" />
				</div>
			</div>
			<div id="address_dialog" title="Add an Address">
				<div class="pform">
					<div class="element">
						<label>
							<span class="label">Type</span>
							<input class="field ui-widget-content" type="text" size="24" name="cur_address_type" id="cur_address_type" />
						</label>
					</div>
					<div class="element">
						<label>
							<span class="label">Address 1</span>
							<input class="field ui-widget-content" type="text" size="24" name="cur_address_addr1" id="cur_address_addr1" />
						</label>
					</div>
					<div class="element">
						<label>
							<span class="label">Address 2</span>
							<input class="field ui-widget-content" type="text" size="24" name="cur_address_addr2" id="cur_address_addr2" />
						</label>
					</div>
					<div class="element">
						<label>
							<span class="label">City, State, Zip</span>
							<input class="field ui-widget-content" type="text" size="8" name="cur_address_city" id="cur_address_city" />
							<input class="field ui-widget-content" type="text" size="2" name="cur_address_state" id="cur_address_state" />
							<input class="field ui-widget-content" type="text" size="5" name="cur_address_zip" id="cur_address_zip" />
						</label>
					</div>
				</div>
				<br class="spacer" />
			</div>
			<br class="spacer" />
		</div>
		<?php } if (in_array('attributes', $pines->config->com_customer->shown_fields_customer)) { ?>
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
							<?php foreach ($this->entity->attributes as $cur_attribute) { ?>
							<tr>
								<td><?php echo $cur_attribute['name']; ?></td>
								<td><?php echo $cur_attribute['value']; ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					<input type="hidden" id="attributes" name="attributes" />
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
		<?php } ?>
	</div>
	<div class="element buttons">
		<br />
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_customer', 'listcustomers')); ?>');" value="Cancel" />
	</div>
</form>