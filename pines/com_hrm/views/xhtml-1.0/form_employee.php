<?php
/**
 * Provides a form for the user to edit a employee.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (is_null($this->entity->guid)) ? 'Editing New Employee' : 'Editing ['.htmlentities($this->entity->name).']';
$this->note = 'Provide employee account details in this form.';
?>
<form class="pform" method="post" id="employee_details" action="<?php echo htmlentities(pines_url('com_hrm', 'saveemployee')); ?>">
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

			$("#employee_tabs").tabs();
			update_addresses();
			update_attributes();
		});
		// ]]>
	</script>
	<div id="employee_tabs" style="clear: both;">
		<ul>
			<li><a href="#tab_general">General</a></li>
			<li><a href="#tab_user_account">User Account</a></li>
			<li><a href="#tab_addresses">Addresses</a></li>
			<li><a href="#tab_attributes">Attributes</a></li>
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
			<?php } ?>
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
			<?php if ($pines->config->com_hrm->ssn_field && gatekeeper('com_hrm/showssn')) { ?>
			<div class="element">
				<label><span class="label">SSN</span>
					<span class="note">Without dashes.</span>
					<input class="field ui-widget-content" type="text" name="ssn" size="24" value="<?php echo $this->entity->ssn; ?>" /></label>
			</div>
			<?php } ?>
			<div class="element">
				<label><span class="label">Email</span>
					<input class="field ui-widget-content" type="text" name="email" size="24" value="<?php echo $this->entity->email; ?>" /></label>
			</div>
			<div class="element">
				<label><span class="label">Job Title</span>
					<input class="field ui-widget-content" type="text" name="job_title" size="24" value="<?php echo $this->entity->job_title; ?>" /></label>
			</div>
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
			<div class="element">
				<label><span class="label">Schedule Color</span>
					<select class="field ui-widget-content" name="color">
						<option value="blue" <?php echo ($this->entity->color == 'blue') ? 'selected="selected"' : ''; ?>>Blue</option>
						<option value="blueviolet" <?php echo ($this->entity->color == 'blueviolet') ? 'selected="selected"' : ''; ?>>Blue Violet</option>
						<option value="brown" <?php echo ($this->entity->color == 'brown') ? 'selected="selected"' : ''; ?>>Brown</option>
						<option value="cornflowerblue" <?php echo ($this->entity->color == 'cornflowerblue') ? 'selected="selected"' : ''; ?>>Cornflower Blue</option>
						<option value="darkorange" <?php echo ($this->entity->color == 'darkorange') ? 'selected="selected"' : ''; ?>>Dark Orange</option>
						<option value="gainsboro" <?php echo ($this->entity->color == 'gainsboro') ? 'selected="selected"' : ''; ?>>Gainsboro</option>
						<option value="gold" <?php echo ($this->entity->color == 'gold') ? 'selected="selected"' : ''; ?>>Gold</option>
						<option value="greenyellow" <?php echo ($this->entity->color == 'greenyellow') ? 'selected="selected"' : ''; ?>>Green Yellow</option>
						<option value="lightpink" <?php echo ($this->entity->color == 'lightpink') ? 'selected="selected"' : ''; ?>>Light Pink</option>
						<option value="olive" <?php echo ($this->entity->color == 'olive') ? 'selected="selected"' : ''; ?>>Olive</option>
						<option value="red" <?php echo ($this->entity->color == 'red') ? 'selected="selected"' : ''; ?>>Red</option>
						<option value="vanilla" <?php echo ($this->entity->color == 'vanilla') ? 'selected="selected"' : ''; ?>>Vanilla</option>
					</select></label>
			</div>
			<div class="element full_width">
				<span class="label">Description</span><br />
				<textarea rows="3" cols="35" class="field peditor" style="width: 100%;" name="description"><?php echo $this->entity->description; ?></textarea>
			</div>
			<br class="clearing" />
		</div>
		<div id="tab_user_account">
			<div class="element heading">
				<p>Attach a user to this employee.</p>
			</div>
			<div class="element">
				<label><span class="label">Username</span>
					<span class="note">Provide either the username or GUID of the user to attach.</span>
					<span class="note">Blank to remove any currently attached user.</span>
					<input class="field ui-widget-content" type="text" name="username" size="24" value="<?php echo $this->entity->user_account->username; ?>" /></label>
			</div>
			<div class="element">
				<span class="label">Sync User</span>
				<label>
					<input class="field ui-widget-content" type="checkbox" name="sync_user" value="ON" <?php echo ($this->entity->sync_user ? 'checked="checked" ' : ''); ?>/> Keep the user's data in sync with this employee's. (Employee data will overwrite user data.)
				</label>
			</div>
			<fieldset class="group">
				<legend>User Templates</legend>
				<div class="element heading">
					<p>You can use a template to create a user for this employee.</p>
				</div>
				<script type="text/javascript">
					// <![CDATA[
					$(function(){
						var template = $("#employee_details [name=user_template]");
						var pgroupselects = $("#employee_details .user_template_group");
						template.change(function(){
							pgroupselects.hide();
							if (this.value != "null")
								$("#employee_details [name=user_template_group_"+this.value+"]").show();
							return true;
						});
						pgroupselects.hide();
					});
					// ]]>
				</script>
				<div class="element">
					<label><span class="label">User Template</span>
						<select class="field ui-widget-content" name="user_template" size="1">
							<option value="null">-- Select a Template --</option>
							<?php foreach ($this->user_templates as $cur_template) { ?>
							<option value="<?php echo $cur_template->guid; ?>"><?php echo $cur_template->name; ?></option>
							<?php } ?>
						</select></label>
				</div>
				<div class="element">
					<label><span class="label">Primary Group</span>
						<?php foreach ($this->user_templates as $cur_template) { ?>
						<select class="field ui-widget-content user_template_group" name="user_template_group_<?php echo $cur_template->guid; ?>" size="1">
							<?php if (!isset($cur_template->group)) { ?>
							<option value="null">-- No Primary Group --</option>
							<?php } else { ?>
							<option value="<?php echo $cur_template->group->guid; ?>"><?php echo $cur_template->group->name; ?> [<?php echo $cur_template->group->groupname; ?>]</option>
							<?php echo $pines->user_manager->get_group_tree('<option value="#guid#">#mark##name# [#groupname#]</option>', $pines->user_manager->get_group_array($cur_template->group->guid), null, '', '-> '); ?>
							<?php } ?>
						</select>
						<?php } ?>
					</label>
				</div>
				<div class="element">
					<label><span class="label">Username</span>
						<input class="field ui-widget-content" type="text" name="user_template_username" size="24" /></label>
				</div>
				<script type="text/javascript">
					// <![CDATA[
					$(function(){
						var password = $("#employee_details [name=user_template_password]");
						var password2 = $("#employee_details [name=user_template_password2]");
						$("#employee_details").submit(function(){
							if (password.val() != password2.val()) {
								alert("Your passwords do not match.");
								return false;
							}
							return true;
						});
					});
					// ]]>
				</script>
				<div class="element">
					<label><span class="label">Password</span>
						<input class="field ui-widget-content" type="password" name="user_template_password" size="24" /></label>
				</div>
				<div class="element">
					<label><span class="label">Repeat Password</span>
						<input class="field ui-widget-content" type="password" name="user_template_password2" size="24" /></label>
				</div>
			</fieldset>
			<br class="clearing" />
		</div>
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
						$("#employee_details [name=address_type]").change(function(){
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
				<br class="clearing" />
			</div>
			<br class="clearing" />
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
			<br class="clearing" />
		</div>
	</div>
	<div class="element buttons">
		<br />
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_hrm', 'listemployees')); ?>');" value="Cancel" />
	</div>
</form>