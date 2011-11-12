<?php
/**
 * Provides a form for the user to edit a replacement.
 *
 * @package Pines
 * @subpackage com_replace
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Replacement' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide replacement details in this form.';
$pines->com_pgrid->load();
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_replace', 'replacement/save')); ?>">
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			// Strings
			var strings = $("#p_muid_form [name=strings]");
			var strings_table = $("#p_muid_form .strings_table");
			var string_dialog = $("#p_muid_form .string_dialog");
			var cur_string = null;

			strings_table.pgrid({
				pgrid_paginate: false,
				pgrid_toolbar: true,
				pgrid_toolbar_contents : [
					{
						type: 'button',
						text: 'Add String',
						extra_class: 'picon picon-edit-text-frame-update',
						selection_optional: true,
						click: function(){
							cur_string = null;
							string_dialog.dialog('open');
						}
					},
					{
						type: 'button',
						text: 'Edit String',
						extra_class: 'picon picon-edit-rename',
						double_click: true,
						click: function(e, rows){
							cur_string = rows;
							string_dialog.find("[name=cur_string_search]").val(pines.unsafe(rows.pgrid_get_value(2)));
							string_dialog.find("[name=cur_string_replace]").val(pines.unsafe(rows.pgrid_get_value(3)));
							if (rows.pgrid_get_value(4) == "Yes")
								string_dialog.find("[name=cur_string_macros]").attr("checked", true);
							else
								string_dialog.find("[name=cur_string_macros]").removeAttr("checked");
							string_dialog.dialog('open');
						}
					},
					{type: 'button', text: 'Move Up', extra_class: 'picon picon-arrow-up', click: function(e, row){
						if (!row.prev().length)
							return;
						row.prev().pgrid_set_value(1, parseInt(row.prev().pgrid_get_value(1))+1);
						row.pgrid_set_value(1, parseInt(row.pgrid_get_value(1))-1);
						update_strings();
					}},
					{type: 'button', text: 'Move Down', extra_class: 'picon picon-arrow-down', click: function(e, row){
						if (!row.next().length)
							return;
						row.next().pgrid_set_value(1, parseInt(row.next().pgrid_get_value(1))-1);
						row.pgrid_set_value(1, parseInt(row.pgrid_get_value(1))+1);
						update_strings();
					}},
					{type: 'separator'},
					{
						type: 'button',
						text: 'Remove String',
						extra_class: 'picon picon-edit-delete',
						click: function(e, rows){
							rows.pgrid_delete();
							update_strings();
						}
					}
				],
				pgrid_view_height: "300px"
			});

			// String Dialog
			string_dialog.dialog({
				bgiframe: true,
				autoOpen: false,
				modal: true,
				width: 500,
				buttons: {
					"Done": function(){
						var cur_string_search = string_dialog.find("[name=cur_string_search]").val();
						var cur_string_replace = string_dialog.find("[name=cur_string_replace]").val();
						var cur_string_macros = string_dialog.find("[name=cur_string_macros]").is(":checked");
						if (cur_string_search == "") {
							alert("Please provide a string.");
							return;
						}
						if (cur_string == null) {
							// Is this a duplicate?
							var dupe = false;
							// Get the next index.
							var index = 0;
							strings_table.pgrid_get_all_rows().each(function(){
								var cur_row = $(this);
								if (parseInt(cur_row.pgrid_get_value(1)) == index)
									index++;
								if (dupe) return;
								if (cur_row.pgrid_get_value(2) == cur_string_search && cur_row.pgrid_get_value(3) == cur_string_replace && cur_row.pgrid_get_value(4) == (cur_string_macros ? "Yes" : "No"))
									dupe = true;
							});
							if (dupe) {
								pines.notice('There is already a string just like this.');
								return;
							}
							var new_string = [{
								key: null,
								values: [
									pines.safe(index),
									pines.safe(cur_string_search),
									pines.safe(cur_string_replace),
									(cur_string_macros ? "Yes" : "No")
								]
							}];
							strings_table.pgrid_add(new_string);
						} else {
							cur_string.pgrid_set_value(2, pines.safe(cur_string_search));
							cur_string.pgrid_set_value(3, pines.safe(cur_string_replace));
							cur_string.pgrid_set_value(4, (cur_string_macros ? "Yes" : "No"));
						}
						$(this).dialog('close');
					}
				},
				close: function(){
					update_strings();
				}
			});

			var update_strings = function(){
				strings_table.pgrid_import_state({pgrid_sort_col: 1, pgrid_sort_ord: 'asc'});
				strings_table.pgrid_get_all_rows().each(function(i){
					$(this).pgrid_set_value(1, pines.safe(i));
				});
				string_dialog.find("[name=cur_string_search]").val("");
				string_dialog.find("[name=cur_string_replace]").val("");
				string_dialog.find("[name=cur_string_macros]").removeAttr("checked");
				strings.val(JSON.stringify(strings_table.pgrid_get_all_rows().pgrid_export_rows()));
			};

			update_strings();


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
							condition_dialog.find("input[name=cur_condition_type]").val(pines.unsafe(rows.pgrid_get_value(1)));
							condition_dialog.find("input[name=cur_condition_value]").val(pines.unsafe(rows.pgrid_get_value(2)));
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
									pines.safe(cur_condition_type),
									pines.safe(cur_condition_value)
								]
							}];
							conditions_table.pgrid_add(new_condition);
						} else {
							cur_condition.pgrid_set_value(1, pines.safe(cur_condition_type));
							cur_condition.pgrid_set_value(2, pines.safe(cur_condition_value));
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

			condition_dialog.find("input[name=cur_condition_type]").autocomplete({
				"source": <?php echo (string) json_encode((array) array_keys($pines->depend->checkers)); ?>
			});

			$("#p_muid_replacement_tabs").tabs();
		});
		// ]]>
	</script>
	<div id="p_muid_replacement_tabs" style="clear: both;">
		<ul>
			<li><a href="#p_muid_tab_general">General</a></li>
			<li><a href="#p_muid_tab_conditions">Conditions</a></li>
		</ul>
		<div id="p_muid_tab_general">
			<?php if (isset($this->entity->guid)) { ?>
			<div class="date_info" style="float: right; text-align: right;">
				<?php if (isset($this->entity->user)) { ?>
				<div>User: <span class="date"><?php echo htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
				<div>Group: <span class="date"><?php echo htmlspecialchars("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
				<?php } ?>
				<div>Created: <span class="date"><?php echo htmlspecialchars(format_date($this->entity->p_cdate, 'full_short')); ?></span></div>
				<div>Modified: <span class="date"><?php echo htmlspecialchars(format_date($this->entity->p_mdate, 'full_short')); ?></span></div>
			</div>
			<?php } ?>
			<div class="pf-element">
				<label><span class="pf-label">Name</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name" size="24" value="<?php echo htmlspecialchars($this->entity->name); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Enabled</span>
					<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element pf-heading">
				<h1>Search and Replace Strings</h1>
			</div>
			<div class="pf-element pf-full-width">
				<table class="strings_table">
					<thead>
						<tr>
							<th>Order</th>
							<th>Search</th>
							<th>Replace</th>
							<th>Use Macros</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->entity->strings as $key => $cur_string) { ?>
						<tr>
							<td><?php echo htmlspecialchars($key); ?></td>
							<td><?php echo htmlspecialchars($cur_string['search']); ?></td>
							<td><?php echo htmlspecialchars($cur_string['replace']); ?></td>
							<td><?php echo $cur_string['macros'] ? 'Yes' : 'No'; ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="strings" />
			</div>
			<div class="string_dialog" title="Add a String" style="display: none;">
				<div class="pf-form">
					<div class="pf-element">
						<label>
							<span class="pf-label">Search For</span>
							<textarea class="pf-field ui-widget-content ui-corner-all" name="cur_string_search" rows="3" cols="24"></textarea>
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Replace With</span>
							<textarea class="pf-field ui-widget-content ui-corner-all" name="cur_string_replace" rows="3" cols="24"></textarea>
						</label>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Use Macros</span>
							<span class="pf-note"><a href="javascript:void(0);" onclick="$(this).closest('.pf-element').nextAll('.macro_dialog').dialog({'modal': false, 'width': 600})">Explain Macros</a></span>
							<input class="pf-field" type="checkbox" name="cur_string_macros" value="ON" /></label>
					</div>
					<div title="Macros" class="macro_dialog" style="display: none;">
						Macros let you replace a string with a value that can
						change. To use them, insert the desired macro string
						into the "Replace With" value where you would like the
						macro to appear.
						<table style="width: 100%; margin: 1em;">
							<thead>
								<tr>
									<th>Macro String</th>
									<th>Is Replaced With</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td>#username#</td>
									<td>The current user's username.</td>
								</tr>
								<tr>
									<td>#name#</td>
									<td>The current user's full name.</td>
								</tr>
								<tr>
									<td>#first_name#</td>
									<td>The current user's first name.</td>
								</tr>
								<tr>
									<td>#last_name#</td>
									<td>The current user's last name.</td>
								</tr>
								<tr>
									<td>#email#</td>
									<td>The current user's email</td>
								</tr>
								<tr>
									<td>#date_short#</td>
									<td>The date. (Short)</td>
								</tr>
								<tr>
									<td>#date_med#</td>
									<td>The date. (Medium)</td>
								</tr>
								<tr>
									<td>#date_long#</td>
									<td>The date. (Long)</td>
								</tr>
								<tr>
									<td>#time_short#</td>
									<td>The time of day. (Short)</td>
								</tr>
								<tr>
									<td>#time_med#</td>
									<td>The time of day. (Medium)</td>
								</tr>
								<tr>
									<td>#time_long#</td>
									<td>The time of day. (Long)</td>
								</tr>
								<tr>
									<td>#system_name#</td>
									<td>The system name.</td>
								</tr>
								<tr>
									<td>#page_title#</td>
									<td>The page title.</td>
								</tr>
								<tr>
									<td>#full_page_title#</td>
									<td>The full title of the HTML page.</td>
								</tr>
							</tbody>
						</table>
						Care should be taken when using values from the current
						user, because they will be empty when no user is logged
						in.
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_conditions">
			<div class="pf-element pf-heading">
				<h1>Replacement Conditions</h1>
				<p>Strings will only be replaced if these conditions are met.</p>
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
							<td><?php echo htmlspecialchars($cur_key); ?></td>
							<td><?php echo htmlspecialchars($cur_value); ?></td>
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
							<div class="pf-field"><em><?php
							$checker_links = array();
							foreach (array_keys($pines->depend->checkers) as $cur_checker) {
								$checker_html = htmlspecialchars($cur_checker);
								$checker_js = htmlspecialchars(json_encode($cur_checker));
								$checker_links[] = "<a href=\"javascript:void(0);\" onclick=\"\$('#p_muid_cur_condition_type').val($checker_js);\">$checker_html</a>";
							}
							echo implode(', ', $checker_links);
							?></em></div>
						</div>
					</div>
					<div class="pf-element">
						<label><span class="pf-label">Type</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_condition_type" id="p_muid_cur_condition_type" size="24" /></label>
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
	</div>
	<br />
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_replace', 'replacement/list')); ?>');" value="Cancel" />
	</div>
</form>