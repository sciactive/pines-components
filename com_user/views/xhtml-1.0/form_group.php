<?php
/**
 * Provides a form for the user to edit a group.
 *
 * @package Pines
 * @subpackage com_user
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Group' : 'Editing ['.htmlentities($this->entity->groupname).']';
$this->note = 'Provide group details in this form.';
$pines->com_pgrid->load();
//$pines->com_jstree->load();
$pines->uploader->load();
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
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
					var cur_attribute_name = $("#p_muid_cur_attribute_name").val();
					var cur_attribute_value = $("#p_muid_cur_attribute_value").val();
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
			$("#p_muid_cur_attribute_name").val("");
			$("#p_muid_cur_attribute_value").val("");
			attributes.val(JSON.stringify(attributes_table.pgrid_get_all_rows().pgrid_export_rows()));
		};

		update_attributes();

		<?php if ( $this->display_conditions && $pines->config->com_user->conditional_groups ) { ?>
		// Conditions
		var conditions = $("#p_muid_form [name=conditions]");
		var conditions_table = $("#p_muid_form .conditions_table");
		var condition_dialog = $("#p_muid_form .condition_dialog");
		var cur_condition = null;

		conditions_table.pgrid({
			pgrid_paginate: false,
			pgrid_toolbar: true,
			pgrid_toolbar_contents : [
				{
					type: 'button',
					text: 'Add Condition',
					extra_class: 'picon picon-document-new',
					selection_optional: true,
					click: function(){
						cur_condition = null;
						condition_dialog.dialog('open');
					}
				},
				{
					type: 'button',
					text: 'Edit Condition',
					extra_class: 'picon picon-document-edit',
					double_click: true,
					click: function(e, rows){
						cur_condition = rows;
						condition_dialog.find("input[name=cur_condition_type]").val(rows.pgrid_get_value(1));
						condition_dialog.find("input[name=cur_condition_value]").val(rows.pgrid_get_value(2));
						condition_dialog.dialog('open');
					}
				},
				{
					type: 'button',
					text: 'Remove Condition',
					extra_class: 'picon picon-edit-delete',
					click: function(e, rows){
						rows.pgrid_delete();
						update_conditions();
					}
				}
			],
			pgrid_view_height: "300px"
		});

		// Condition Dialog
		condition_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 500,
			buttons: {
				"Done": function(){
					var cur_condition_type = condition_dialog.find("input[name=cur_condition_type]").val();
					var cur_condition_value = condition_dialog.find("input[name=cur_condition_value]").val();
					if (cur_condition_type == "") {
						alert("Please provide a type for this condition.");
						return;
					}
					if (cur_condition == null) {
						// Is this a duplicate type?
						var dupe = false;
						conditions_table.pgrid_get_all_rows().each(function(){
							if (dupe) return;
							if ($(this).pgrid_get_value(1) == cur_condition_type)
								dupe = true;
						});
						if (dupe) {
							pines.notice('There is already a condition of that type.');
							return;
						}
						var new_condition = [{
							key: null,
							values: [
								cur_condition_type,
								cur_condition_value
							]
						}];
						conditions_table.pgrid_add(new_condition);
					} else {
						cur_condition.pgrid_set_value(1, cur_condition_type);
						cur_condition.pgrid_set_value(2, cur_condition_value);
					}
					$(this).dialog('close');
				}
			},
			close: function(){
				update_conditions();
			}
		});

		var update_conditions = function(){
			condition_dialog.find("input[name=cur_condition_type]").val("");
			condition_dialog.find("input[name=cur_condition_value]").val("");
			conditions.val(JSON.stringify(conditions_table.pgrid_get_all_rows().pgrid_export_rows()));
		};

		update_conditions();

		var types = <?php echo (string) json_encode((array) array_keys($pines->depend->checkers)); ?>;
		condition_dialog.find("input[name=cur_condition_type]").autocomplete({
			"source": types
		});
		<?php } ?>

		<?php /*
		// Parent Tree
		var location = $("#p_muid_form [name=parent]");
		$("#p_muid_form .location_tree")
		.bind("select_node.jstree", function(e, data){
			location.val(data.inst.get_selected().attr("id").replace("p_muid_", ""));
		})
		.bind("before.jstree", function (e, data){
			if (data.func == "parse_json" && "args" in data && 0 in data.args && "attr" in data.args[0] && "id" in data.args[0].attr)
				data.args[0].attr.id = "p_muid_"+data.args[0].attr.id;
		})
		.bind("loaded.jstree", function(e, data){
			var path = data.inst.get_path("#"+data.inst.get_settings().ui.initially_select, true);
			if (!path.length) return;
			data.inst.open_node("#"+path.join(", #"), false, true);
		})
		.jstree({
			"plugins" : [ "themes", "json_data", "ui" ],
			"json_data" : {
				"ajax" : {
					"dataType" : "json",
					"url" : "<?php echo addslashes(pines_url('com_jstree', 'groupjson')); ?>"
				}
			},
			"ui" : {
				"select_limit" : 1,
				"initially_select" : ["<?php echo (int) $this->entity->parent->guid; ?>"]
			}
		});
		*/ ?>

		$("#p_muid_tabs").tabs();
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlentities(pines_url('com_user', 'savegroup')); ?>">
	<div id="p_muid_tabs" style="clear: both;">
		<ul>
			<li><a href="#p_muid_tab_general">General</a></li>
			<li><a href="#p_muid_tab_logo">Logo</a></li>
			<li><a href="#p_muid_tab_location">Location</a></li>
			<?php if ( $this->display_abilities ) { ?>
			<li><a href="#p_muid_tab_abilities">Abilities</a></li>
			<?php } ?>
			<?php if ($pines->config->com_user->conditional_groups && $this->display_conditions) { ?>
			<li><a href="#p_muid_tab_conditions">Conditions</a></li>
			<?php } ?>
			<li><a href="#p_muid_tab_attributes">Attributes</a></li>
		</ul>
		<div id="p_muid_tab_general">
			<?php if (isset($this->entity->guid)) { ?>
			<div class="date_info" style="float: right; text-align: right;">
				<?php if (isset($this->entity->user)) { ?>
				<div>User: <span class="date"><?php echo htmlentities("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
				<div>Group: <span class="date"><?php echo htmlentities("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
				<?php } ?>
				<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
				<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
			</div>
			<?php } ?>
			<div class="pf-element">
				<label><span class="pf-label">Group Name</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="groupname" size="24" value="<?php echo htmlentities($this->entity->groupname); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Display Name</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name" size="24" value="<?php echo htmlentities($this->entity->name); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Enabled</span>
					<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Email</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="email" size="24" value="<?php echo htmlentities($this->entity->email); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Phone</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="phone" size="24" value="<?php echo format_phone($this->entity->phone); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Fax</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="fax" size="24" value="<?php echo format_phone($this->entity->fax); ?>" onkeyup="this.value=this.value.replace(/\D*0?1?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d)?\D*(\d*)\D*/, '($1$2$3) $4$5$6-$7$8$9$10 x$11').replace(/\D*$/, '');" /></label>
			</div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Timezone</span>
					<span class="pf-note">Users in this group will inherit this timezone. Primary group has priority over secondary groups.</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="timezone" size="1">
						<option value="">--System Default--</option>
						<?php
						$tz = DateTimeZone::listIdentifiers();
						sort($tz);
						foreach ($tz as $cur_tz) {
							?><option value="<?php echo htmlentities($cur_tz); ?>"<?php echo $this->entity->timezone == $cur_tz ? ' selected="selected"' : ''; ?>><?php echo htmlentities($cur_tz); ?></option><?php
						} ?>
					</select>
				</label>
			</div>
			<div class="pf-element">
				<label>
					<span class="pf-label">Parent</span>
					<select class="pf-field ui-widget-content ui-corner-all" name="parent" size="1">
						<option value="none">--No Parent--</option>
						<?php
						$pines->user_manager->group_sort($this->group_array, 'name');
						foreach ($this->group_array as $cur_group) {
							?><option value="<?php echo $cur_group->guid; ?>"<?php echo $cur_group->is($this->entity->parent) ? ' selected="selected"' : ''; ?>><?php echo htmlentities(str_repeat('->', $cur_group->get_level())." {$cur_group->name} [{$cur_group->groupname}]"); ?></option><?php
						} ?>
					</select>
				</label>
			</div>
			<?php /*
			<div class="pf-element">
				<span class="pf-label">Parent</span>
				<div class="pf-group">
					<span class="pf-field"><input type="checkbox" name="no_parent" value="ON"<?php echo !isset($this->entity->parent) ? ' checked="checked"' : ''; ?> /> No Parent</span>
					<div class="pf-field location_tree ui-widget-content ui-corner-all" style="height: 180px; width: 200px; overflow: auto;"></div>
				</div>
				<input type="hidden" name="parent" />
			</div>
			*/ ?>
			<?php if ($this->display_default) { ?>
			<div class="pf-element">
				<label><span class="pf-label">New User Primary Group</span>
					<span class="pf-note">Default primary group for newly registered users.</span>
					<input class="pf-field" type="checkbox" name="default_primary" value="ON"<?php echo $this->entity->default_primary ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">New User Secondary Group</span>
					<span class="pf-note">Default secondary group for newly registered users.</span>
					<input class="pf-field" type="checkbox" name="default_secondary" value="ON"<?php echo $this->entity->default_secondary ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<?php } ?>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_logo">
			<div class="pf-element">
				<span class="pf-label"><?php echo (isset($this->entity->logo)) ? 'Currently Set Logo' : 'Inherited Logo'; ?></span>
				<div class="pf-group">
					<span class="pf-field"><img src="<?php echo htmlentities($this->entity->get_logo()); ?>" alt="Group Logo" /></span>
					<?php if (isset($this->entity->logo)) { ?>
					<br />
					<label><span class="pf-field"><input class="pf-field" type="checkbox" name="remove_logo" value="ON" />Remove this logo.</span></label>
					<?php } ?>
				</div>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Change Logo</span>
					<input class="pf-field ui-widget-content ui-corner-all puploader" type="text" name="image" /></label>
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_location">
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
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="address_1" size="24" value="<?php echo htmlentities($this->entity->address_1); ?>" /></label>
				</div>
				<div class="pf-element">
					<label><span class="pf-label">Address 2</span>
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="address_2" size="24" value="<?php echo htmlentities($this->entity->address_2); ?>" /></label>
				</div>
				<div class="pf-element">
					<span class="pf-label">City, State</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="city" size="15" value="<?php echo htmlentities($this->entity->city); ?>" />
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
						<input class="pf-field ui-widget-content ui-corner-all" type="text" name="zip" size="24" value="<?php echo htmlentities($this->entity->zip); ?>" /></label>
				</div>
			</div>
			<div id="p_muid_address_international" style="display: none;">
				<div class="pf-element pf-full-width">
					<label><span class="pf-label">Address</span>
						<span class="pf-field pf-full-width"><textarea class="ui-widget-content ui-corner-all" style="width: 100%;" rows="3" cols="35" name="address_international"><?php echo $this->entity->address_international; ?></textarea></span></label>
				</div>
			</div>
			<br class="pf-clearing" />
		</div>
		<?php if ( $this->display_abilities ) { ?>
		<div id="p_muid_tab_abilities">
			<script type="text/javascript">
				// <![CDATA[
				pines(function(){
					var sections = $("#p_muid_form .abilities_accordian");
					sections.accordion({
						autoHeight: false,
						collapsible: true,
						active: false
					});
					$("#p_muid_form button.expand_all").button().click(function(){
						sections.each(function(){
							var section = $(this);
							if (section.accordion("option", "active") === false)
								section.accordion("activate", 0);
						});
					});
					$("#p_muid_form button.collapse_all").button().click(function(){
						sections.accordion("activate", false);
					});
				});
				// ]]>
			</script>
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
				<h3><a href="#"><?php echo ($cur_section == 'system') ? htmlentities($pines->info->name) : htmlentities($pines->info->$cur_section->name); ?> (<?php echo htmlentities($cur_section); ?>)</a></h3>
				<div>
					<div class="pf-element">
						<?php foreach ($section_abilities as $cur_ability) { ?>
						<label>
							<input type="checkbox" name="<?php echo htmlentities($cur_section); ?>[]" value="<?php echo htmlentities($cur_ability[0]); ?>" <?php echo (array_search("{$cur_section}/{$cur_ability[0]}", $this->entity->abilities) !== false) ? 'checked="checked" ' : ''; ?>/>
							<?php echo htmlentities($cur_ability[1]); ?>&nbsp;<small><?php echo htmlentities($cur_ability[2]); ?></small>
						</label>
						<br class="pf-clearing" />
						<?php } ?>
					</div>
				</div>
			</div>
			<?php } ?>
			<br class="pf-clearing" />
		</div>
		<?php } ?>
		<?php if ($pines->config->com_user->conditional_groups && $this->display_conditions) { ?>
		<div id="p_muid_tab_conditions">
			<div class="pf-element pf-heading">
				<h1>Ability Conditions</h1>
				<p>Users will only inherit abilities from this group if these conditions are met.</p>
			</div>
			<div class="pf-element pf-full-width">
				<table class="conditions_table">
					<thead>
						<tr>
							<th>Type</th>
							<th>Value</th>
						</tr>
					</thead>
					<tbody>
						<?php if (isset($this->entity->conditions)) foreach ($this->entity->conditions as $cur_key => $cur_value) { ?>
						<tr>
							<td><?php echo htmlentities($cur_key); ?></td>
							<td><?php echo htmlentities($cur_value); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="conditions" />
			</div>
			<div class="condition_dialog" style="display: none;" title="Add a Condition">
				<div class="pf-form">
					<div class="pf-element">
						<span class="pf-label">Detected Types</span>
						<span class="pf-note">These types were detected on this system.</span>
						<div class="pf-group">
							<div class="pf-field"><em><?php echo htmlentities(implode(', ', array_keys($pines->depend->checkers))); ?></em></div>
						</div>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Type</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_condition_type" size="24" /></label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Value</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_condition_value" size="24" /></label>
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
			</div>
			<br class="pf-clearing" />
		</div>
		<?php } ?>
		<div id="p_muid_tab_attributes">
			<div class="pf-element pf-full-width">
				<table class="attributes_table">
					<thead>
						<tr><th>Name</th><th>Value</th></tr>
					</thead>
					<tbody>
						<?php foreach ($this->entity->attributes as $cur_attribute) { ?>
						<tr><td><?php echo htmlentities($cur_attribute['name']); ?></td><td><?php echo htmlentities($cur_attribute['value']); ?></td></tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="attributes" />
			</div>
			<div class="attribute_dialog" style="display: none;" title="Add an Attribute">
				<div class="pf-form">
					<div class="pf-element">
						<label><span class="pf-label">Name</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" id="p_muid_cur_attribute_name" size="24" /></label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Value</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" id="p_muid_cur_attribute_value" size="24" /></label>
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
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_user', 'listgroups')); ?>');" value="Cancel" />
	</div>
</form>