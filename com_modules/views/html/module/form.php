<?php
/**
 * Provides a form for the user to edit a module.
 *
 * @package Pines
 * @subpackage com_modules
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Module' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide module details in this form.';
$pines->editor->load();
$pines->com_pgrid->load();
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_form .combobox {
		position: relative;
	}
	#p_muid_form .combobox input {
		padding-right: 32px;
	}
	#p_muid_form .combobox a {
		display: block;
		position: absolute;
		right: 8px;
		top: 50%;
		margin-top: -8px;
	}

	#p_muid_form .component_modules .form {
		display: none;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		// Options
		var options = $("input[name=options]", "#p_muid_tab_options");
		var options_table = $(".options_table", "#p_muid_tab_options");

		options_table.pgrid({
			pgrid_paginate: false,
			pgrid_toolbar: true,
			pgrid_toolbar_contents : [
				{
					type: 'button',
					text: 'Edit Options',
					extra_class: 'picon picon-list-add',
					selection_optional: true,
					click: function(){
						var type = $(".component_modules [name=type]:checked");
						if (!type.length) {
							alert("Please select a module type.");
							return;
						}
						var form = type.siblings(".form").text();
						if (form == "") {
							alert("The selected module type has no options.")
							options_table.pgrid_get_all_rows().pgrid_delete();
							update_options();
							return;
						}
						type = type.val();
						$.ajax({
							url: "<?php echo addslashes(pines_url('com_modules', 'module/form')); ?>",
							type: "POST",
							dataType: "html",
							data: {"type": type},
							error: function(XMLHttpRequest, textStatus){
								pines.error("An error occured while trying to retreive the form:\n"+XMLHttpRequest.status+": "+textStatus);
							},
							success: function(data){
								if (data == "")
									return;
								pines.pause();
								var form = $("<div title=\"Module Options\"></div>")
								.html('<form method="post" action="">'+data+"</form><br />");
								form.find("form").submit(function(){
									form.dialog('option', 'buttons').Done();
									return false;
								});
								var cur_data = options_table.pgrid_get_all_rows();
								if (cur_data.length) {
									cur_data.each(function(){
										var cur_row = $(this);
										var name = cur_row.pgrid_get_value(1);
										var value = cur_row.pgrid_get_value(2);
										form.find(":input:not(:radio, :checkbox)[name="+name+"]").val(value);
										form.find(":input:radio[name="+name+"][value="+value+"]").attr("checked", "checked");
										if (value == "")
											form.find(":input:checkbox[name="+name+"]").removeAttr("checked");
										else
											form.find(":input:checkbox[name="+name+"][value="+value+"]").attr("checked", "checked");
									});
								}
								form.dialog({
									bgiframe: true,
									autoOpen: true,
									modal: true,
									width: "auto",
									close: function(){
										form.remove();
									},
									buttons: {
										"Done": function(){
											options_table.pgrid_get_all_rows().pgrid_delete();
											form.find(":input").each(function(){
												var cur_input = $(this);
												if (cur_input.is(":radio:not(:checked)"))
													return;
												var cur_value = cur_input.val();
												if (cur_input.is(":checkbox:not(:checked)"))
													cur_value = "";
												options_table.pgrid_add([{
													values: [
														cur_input.attr("name"),
														cur_value
													]
												}]);
											});
											update_options();
											form.dialog('close');
										}
									}
								});
								pines.play();
							}
						});
					}
				}
			],
			pgrid_view_height: "300px"
		});

		var update_options = function(){
			options.val(JSON.stringify(options_table.pgrid_get_all_rows().pgrid_export_rows()));
		};

		update_options();


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

		condition_dialog.find("input[name=cur_condition_type]").autocomplete({
			"source": <?php echo (string) json_encode((array) array_keys($pines->depend->checkers)); ?>
		});

		$(".combobox", "#p_muid_form").each(function(){
			var box = $(this);
			var autobox = box.children("input").autocomplete({
				minLength: 0,
				source: $.map(box.children("select").children(), function(elem){
					return $(elem).attr("value");
				})
			});
			box.children("a").hover(function(){
				$(this).addClass("ui-icon-circle-triangle-s").removeClass("ui-icon-triangle-1-s");
			}, function(){
				$(this).addClass("ui-icon-triangle-1-s").removeClass("ui-icon-circle-triangle-s");
			}).click(function(){
				autobox.focus().autocomplete("search", "");
			});
		});

		$("#p_muid_module_tabs").tabs();
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_modules', 'module/save')); ?>">
	<div id="p_muid_module_tabs" style="clear: both;">
		<ul>
			<li><a href="#p_muid_tab_general">General</a></li>
			<li><a href="#p_muid_tab_options">Options</a></li>
			<li><a href="#p_muid_tab_conditions">Conditions</a></li>
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
			<div class="pf-element">
				<label><span class="pf-label">Name/Title</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="name" size="24" value="<?php echo htmlspecialchars($this->entity->name); ?>" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Enabled</span>
					<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Show Title</span>
					<input class="pf-field" type="checkbox" name="show_title" value="ON"<?php echo $this->entity->show_title ? ' checked="checked"' : ''; ?> /></label>
			</div>
			<div class="pf-element">
				<span class="pf-label">Position</span>
				<span class="combobox">
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="position" size="24" value="<?php echo htmlspecialchars($this->entity->position); ?>" />
					<a href="javascript:void(0);" class="ui-icon ui-icon-triangle-1-s"></a>
					<select style="display: none;">
						<?php foreach ($pines->info->template->positions as $cur_position) {
							?><option value="<?php echo htmlspecialchars($cur_position); ?>"><?php echo htmlspecialchars($cur_position); ?></option><?php
						} ?>
					</select>
				</span>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Order</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" name="order" size="10" value="<?php echo htmlspecialchars($this->entity->order); ?>" /></label>
			</div>
			<div class="pf-element pf-heading">
				<h1>Module Type</h1>
			</div>
			<br class="pf-clearing" />
			<?php $i=0; foreach ($this->modules as $cur_component => $cur_modules) { $i++; ?>
			<div class="pf-element pf-full-width component_modules">
				<div style="padding: .5em;" class="ui-helper-clearfix<?php echo ($i % 2) ? '' : ' ui-widget-content'; ?>">
					<strong class="pf-label"><?php echo htmlspecialchars($pines->info->$cur_component->name); ?></strong>
					<div class="pf-group">
						<?php foreach ($cur_modules as $cur_modname => $cur_module) { ?>
						<div class="pf-field">
							<label><input type="radio" name="type" value="<?php echo htmlspecialchars("$cur_component/$cur_modname"); ?>"<?php echo ($this->entity->type == "$cur_component/$cur_modname") ? ' checked="checked"': ''; ?> /> <span class="form"><?php echo htmlspecialchars($cur_module['form']); ?></span><?php echo htmlspecialchars($cur_module['cname']); ?></label>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<?php } ?>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_options">
			<div class="pf-element pf-heading">
				<h1>Module Options</h1>
				<p>If the module type has options, you can edit them below.</p>
			</div>
			<div class="pf-element pf-full-width">
				<table class="options_table">
					<thead>
						<tr>
							<th>Name</th>
							<th>Value</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($this->entity->options as $cur_option) { ?>
						<tr>
							<td><?php echo htmlspecialchars($cur_option['name']); ?></td>
							<td><?php echo htmlspecialchars($cur_option['value']); ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<input type="hidden" name="options" />
			</div>
			<div class="option_dialog" title="Add an Option" style="display: none;">
				<div class="pf-form">
					<div class="pf-element">
						<label>
							<span class="pf-label">Name</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_option_name" size="24" />
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Value</span>
							<input class="pf-field ui-widget-content ui-corner-all" type="text" name="cur_option_value" size="24" />
						</label>
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
			</div>
			<br class="pf-clearing" />
		</div>
		<div id="p_muid_tab_conditions">
			<div class="pf-element pf-heading">
				<h1>Module Conditions</h1>
				<p>Users will only see this module if these conditions are met.</p>
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
								$checker_js = addslashes($cur_checker);
								$checker_links[] = "<a href=\"javascript:void(0);\" onclick=\"\$('#p_muid_cur_condition_type').val('$checker_js');\">$checker_html</a>";
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
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_modules', 'module/list')); ?>');" value="Cancel" />
	</div>
</form>