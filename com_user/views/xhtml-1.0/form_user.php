<?php
/**
 * Provides a form for the user to edit a user.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New User' : 'Editing ['.htmlentities($this->entity->username).']';
$this->note = 'Provide user details in this form.';
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var password = $("#user_details [name=password]");
		var password2 = $("#user_details [name=password2]");
		$("#user_details").submit(function(){
			if (password.val() != password2.val()) {
				alert("Your passwords do not match.");
				return false;
			}
			return true;
		});

		// Attributes
		var attributes = $("#tab_attributes .attributes");
		var attributes_table = $("#tab_attributes .attributes_table");
		var attribute_dialog = $("#tab_attributes .attribute_dialog");

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
			width: 500,
			buttons: {
				"Done": function() {
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
					update_attributes();
					$(this).dialog('close');
				}
			}
		});

		function update_attributes() {
			attribute_dialog.find("input[name=cur_attribute_name]").val("");
			attribute_dialog.find("input[name=cur_attribute_value]").val("");
			attributes.val(JSON.stringify(attributes_table.pgrid_get_all_rows().pgrid_export_rows()));
		}

		update_attributes();

		$("#user_tabs").tabs();
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="user_details" action="<?php echo htmlentities(pines_url('com_user', 'saveuser')); ?>">
	<div id="user_tabs" style="clear: both;">
		<ul>
			<li><a href="#tab_general">General</a></li>
			<li><a href="#tab_groups">Groups</a></li>
			<li><a href="#tab_location">Location</a></li>
			<li><a href="#tab_abilities">Abilities</a></li>
			<li><a href="#tab_attributes">Attributes</a></li>
		</ul>
		<div id="tab_general">
			<?php if (isset($this->entity->guid)) { ?>
			<div class="date_info" style="float: right; text-align: right;">
				<?php if (isset($this->entity->user)) { ?>
				<div>User: <span class="date"><?php echo "{$this->entity->user->name} [{$this->entity->user->username}]"; ?></span></div>
				<div>Group: <span class="date"><?php echo "{$this->entity->group->name} [{$this->entity->group->groupname}]"; ?></span></div>
				<?php } ?>
				<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
				<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
			</div>
			<?php } ?>
			<div class="pf-element">
				<label><span class="pf-label">Username</span>
					<input class="pf-field ui-widget-content" type="text" name="username" size="24" value="<?php echo $this->entity->username; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Name</span>
					<input class="pf-field ui-widget-content" type="text" name="name" size="24" value="<?php echo $this->entity->name; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Enabled</span>
					<input class="pf-field ui-widget-content" type="checkbox" name="enabled" size="24" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Email</span>
					<input class="pf-field ui-widget-content" type="text" name="email" size="24" value="<?php echo $this->entity->email; ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Phone</span>
					<input class="pf-field ui-widget-content" type="text" name="phone" size="24" value="<?php echo format_phone($this->entity->phone); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Fax</span>
					<input class="pf-field ui-widget-content" type="text" name="fax" size="24" value="<?php echo format_phone($this->entity->fax); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Timezone</span>
					<span class="pf-note">This overrides the primary group's timezone.</span>
					<select class="pf-field ui-widget-content" name="timezone" size="1">
						<option value="">--Inherit From Group--</option>
						<?php $tz = DateTimeZone::listIdentifiers();
						sort($tz);
						foreach ($tz as $cur_tz) { ?>
						<option value="<?php echo $cur_tz; ?>"<?php echo $this->entity->timezone == $cur_tz ? ' selected="selected"' : ''; ?>><?php echo $cur_tz; ?></option>
						<?php } ?>
					</select></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label"><?php if (isset($this->entity->guid)) echo 'Update '; ?>Password</span>
					<?php if (!isset($this->entity->guid)) {
						echo ($pines->config->com_user->empty_pw ? '<span class="pf-note">May be blank.</span>' : '');
					} else {
						echo '<span class="pf-note">Leave blank, if not changing.</span>';
					} ?>
					<input class="pf-field ui-widget-content" type="password" name="password" size="24" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Repeat Password</span>
					<input class="pf-field ui-widget-content" type="password" name="password2" size="24" /></label>
			</div>
			<?php if ( $this->display_pin ) { ?>
			<div class="pf-element">
				<label><span class="pf-label">PIN code</span>
					<input class="pf-field ui-widget-content" type="password" name="pin" size="5" value="<?php echo $this->entity->pin; ?>" <?php echo $pines->config->com_user->max_pin_length > 0 ? "maxlength=\"{$pines->config->com_user->max_pin_length}\"" : ''; ?>/></label>
			</div>
			<?php } ?>
			<?php if ( $this->display_default_components ) { ?>
				<div class="pf-element">
					<label><span class="pf-label">Default Component</span>
						<span class="pf-note">This component will be responsible for the user's home page.</span>
						<select class="pf-field ui-widget-content" name="default_component">
								<?php foreach ($this->default_components as $cur_component) { ?>
							<option value="<?php echo $cur_component; ?>"<?php echo (($this->entity->default_component == $cur_component) ? ' selected="selected"' : ''); ?>><?php echo $cur_component; ?></option>
								<?php } ?>
						</select></label>
				</div>
			<?php } ?>
			<br class="pf-clearing" />
		</div>
		<div id="tab_groups">
			<?php if ( $this->display_groups ) { ?>
				<?php if (empty($this->group_array)) { ?>
				<div class="pf-element">
					<span class="pf-label">There are no groups to display.</span>
				</div>
				<?php } else { ?>
				<div class="pf-element">
					<label><span class="pf-label">Primary Group</span>
						<select class="pf-field ui-widget-content" name="group" size="1">
							<option value="null">-- No Primary Group --</option>
									<?php echo $pines->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->group_array, $this->entity->group); ?>
						</select></label>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Groups</span>
						<span class="pf-note">Hold Ctrl (Command on Mac) to select multiple groups.</span>
						<select class="pf-field ui-widget-content" name="groups[]" multiple="multiple" size="6">
									<?php echo $pines->user_manager->get_group_tree('<option value="#guid#"#selected#>#mark##name# [#groupname#]</option>', $this->group_array, $this->entity->groups); ?>
						</select></label>
				</div>
				<?php }
			} else { ?>
			<div class="pf-element">
				<p>You do not have sufficient privileges to edit group membership.</p>
			</div>
			<?php } ?>
			<br class="pf-clearing" />
		</div>
		<div id="tab_location">
			<div class="pf-element">
				<script type="text/javascript">
					// <![CDATA[
					pines(function(){
						var address_us = $("#address_us");
						var address_international = $("#address_international");
						$("#user_details [name=address_type]").change(function(){
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
				<label><input class="pf-field ui-widget-content" type="radio" name="address_type" value="us"<?php echo ($this->entity->address_type == 'us') ? ' checked="checked"' : ''; ?> /> US</label>
				<label><input class="pf-field ui-widget-content" type="radio" name="address_type" value="international"<?php echo $this->entity->address_type == 'international' ? ' checked="checked"' : ''; ?> /> International</label>
			</div>
			<div id="address_us" style="display: none;">
				<div class="pf-element">
					<label><span class="pf-label">Address 1</span>
						<input class="pf-field ui-widget-content" type="text" name="address_1" size="24" value="<?php echo $this->entity->address_1; ?>" /></label>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Address 2</span>
						<input class="pf-field ui-widget-content" type="text" name="address_2" size="24" value="<?php echo $this->entity->address_2; ?>" /></label>
				</div>
				<div class="pf-element">
					<span class="pf-label">City, State</span>
					<input class="pf-field ui-widget-content" type="text" name="city" size="15" value="<?php echo $this->entity->city; ?>" />
					<select class="pf-field ui-widget-content" name="state">
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
						<input class="pf-field ui-widget-content" type="text" name="zip" size="24" value="<?php echo $this->entity->zip; ?>" /></label>
				</div>
			</div>
			<div id="address_international" style="display: none;">
				<div class="pf-element pf-full-width">
					<label><span class="pf-label">Address</span>
						<span class="pf-field pf-full-width"><textarea class="ui-widget-content" style="width: 100%;" rows="3" cols="35" name="address_international"><?php echo $this->entity->address_international; ?></textarea></span></label>
				</div>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="tab_abilities">
			<?php if ( $this->display_abilities ) { ?>
			<input type="hidden" name="abilities" value="true" />
			<script type="text/javascript">
				// <![CDATA[
				pines(function(){
					var sections = $("#user_details .abilities_accordian");
					sections.accordion({
						autoHeight: false,
						collapsible: true,
						active: false
					});
					$("#user_details button.expand_all").button().click(function(){
						sections.each(function(){
							var section = $(this);
							if (section.accordion("option", "active") === false)
								section.accordion("activate", 0);
						});
					});
					$("#user_details button.collapse_all").button().click(function(){
						sections.accordion("activate", false);
					});
				});
				// ]]>
			</script>
			<div class="pf-element">
				<span class="pf-label">Inherit</span>
				<label>
					<input class="pf-field ui-widget-content" type="checkbox" name="inherit_abilities" value="ON" <?php echo ($this->entity->inherit_abilities ? 'checked="checked" ' : ''); ?>/>
					&nbsp;Inherit additional abilities from groups.
				</label>
			</div>
			<div class="pf-element pf-full-width ui-helper-clearfix">
				<div style="float: right; clear: both;">
					<button type="button" class="expand_all">Expand All</button>
					<button type="button" class="collapse_all">Collapse All</button>
				</div>
			</div>
			<br class="pf-clearing" />
			<?php foreach ($this->sections as $cur_section) {
				if ($cur_section == 'system') {
					$section_abilities = (array) $pines->info->abilities;
				} else {
					$section_abilities = (array) $pines->info->$cur_section->abilities;
				}
				if (!$section_abilities) continue; ?>
			<div class="abilities_accordian">
				<h3><a href="#"><?php echo ($cur_section == 'system') ? $pines->info->name : $pines->info->$cur_section->name; ?> (<?php echo $cur_section; ?>)</a></h3>
				<div>
					<div class="pf-element">
						<?php foreach ($section_abilities as $cur_ability) { ?>
						<label>
							<input class="ui-widget-content" type="checkbox" name="<?php echo $cur_section; ?>[]" value="<?php echo $cur_ability[0]; ?>" <?php echo (array_search("{$cur_section}/{$cur_ability[0]}", $this->entity->abilities) !== false) ? 'checked="checked" ' : ''; ?>/>
							<?php echo $cur_ability[1]; ?>&nbsp;<small><?php echo $cur_ability[2]; ?></small>
						</label>
						<br class="pf-clearing" />
						<?php } ?>
					</div>
				</div>
			</div>
			<?php } ?>
			<?php } else { ?>
			<div class="pf-element">
				<p>You do not have sufficient privileges to edit abilities.</p>
			</div>
			<?php } ?>
			<br class="pf-clearing" />
		</div>
		<div id="tab_attributes">
			<div class="pf-element pf-full-width">
				<span class="pf-label">Attributes</span>
				<div class="pf-group">
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
								<td><?php echo $cur_attribute['name']; ?></td>
								<td><?php echo $cur_attribute['value']; ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					<input type="hidden" name="attributes" />
				</div>
			</div>
			<div class="attribute_dialog" style="display: none;" title="Add an Attribute">
				<div class="pf-form">
					<div class="pf-element">
						<label>
							<span class="pf-label">Name</span>
							<input class="pf-field ui-widget-content" type="text" name="cur_attribute_name" size="24" />
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Value</span>
							<input class="pf-field ui-widget-content" type="text" name="cur_attribute_value" size="24" />
						</label>
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
			</div>
			<br class="pf-clearing" />
		</div>
	</div>

	<div class="pf-element pf-buttons">
		<br />
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_user', 'listusers')); ?>');" value="Cancel" />
	</div>
</form>