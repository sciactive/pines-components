<?php
/**
 * Provides a form for the user to edit a module.
 *
 * @package Components\modules
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->title = (!isset($this->entity->guid)) ? 'Editing New Module' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide module details in this form.';

$pines->com_pgrid->load();
?>
<style type="text/css" >
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
</style>
<script type="text/javascript">
	pines(function(){
		// Options
		var options = $("input[name=options]", "#p_muid_tab_options"),
			options_table = $(".options_table", "#p_muid_tab_options");

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
						var type_elem = $(".component_modules [name=type]:checked", "#p_muid_form");
						if (!type_elem.length) {
							alert("Please select a module type.");
							return;
						}
						if (!type_elem.siblings(".form").length) {
							alert("The selected module type has no options.")
							options_table.pgrid_get_all_rows().pgrid_delete();
							update_options();
							return;
						}
						var type = type_elem.val(),
							cur_data = options_table.pgrid_get_all_rows(),
							module_data = [];
						if (cur_data.length) {
							cur_data.each(function(){
								var cur_row = $(this);
								module_data.push({
									name: cur_row.pgrid_get_value(1),
									value: pines.unsafe(cur_row.pgrid_get_value(2))
								});
							});
						}
						$.ajax({
							url: <?php echo json_encode(pines_url('com_modules', 'module/form')); ?>,
							type: "POST",
							dataType: "json",
							data: {"type": type, "data": JSON.stringify(module_data)},
							error: function(XMLHttpRequest, textStatus){
								pines.error("An error occured while trying to retrieve the form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
							},
							success: function(data){
								if (!data)
									return;
								pines.pause();
								if (typeof data.head !== "undefined")
									$("head").append(data.head);
								var form = $("<div title=\"Module Options\"></div>")
								.html('<form method="post" action="">'+data.content+"</form><br />");
								form.find("form").submit(function(){
									form.dialog('option', 'buttons').Done();
									return false;
								});
								form.dialog({
									bgiframe: true,
									autoOpen: true,
									modal: true,
									width: "auto",
									buttons: {
										"Done": function(){
											options_table.pgrid_get_all_rows().pgrid_delete();
											form.find(":input[name]").each(function(){
												var cur_input = $(this);
												if (cur_input.is(":radio:not(:checked)"))
													return;
												var cur_value = cur_input.val();
												if (cur_input.is(":checkbox:not(:checked)"))
													cur_value = "";
												options_table.pgrid_add([{
													values: [
														pines.safe(cur_input.attr("name")),
														pines.safe(cur_value)
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
			var type_elem = $(".component_modules [name=type]:checked", "#p_muid_form");
			if (!type_elem.length) {
				$("#p_muid_inline_format").html("No module type is selected.");
			} else {
				var inline = "";
				var type = type_elem.val();
				var cur_data = options_table.pgrid_get_all_rows();
				if (cur_data.length) {
					inline = "["+type;
					var icontent = false;
					cur_data.each(function(){
						var cur_row = $(this), name = cur_row.pgrid_get_value(1), value = pines.unsafe(cur_row.pgrid_get_value(2)), delin = '"';
						if (value.match(/"/)) {
							delin = "'";
							if (value.match(/'/))
								delin = "`";
						}
						if (name == "icontent")
							icontent = value;
						else
							inline += " "+name+"="+delin+value+delin;
					});
					if (icontent)
						inline += "]"+icontent+"[/"+type+"]";
					else
						inline += " /]";
				} else {
					inline = "["+type+" /]";
				}
				$("#p_muid_inline_format").text(inline);
			}
		};
		$(".component_modules [name=type]").change(function(){update_options();});

		update_options();


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
	});
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_modules', 'module/save')); ?>">
	<ul class="nav nav-tabs" style="clear: both;">
		<li class="active"><a href="#p_muid_tab_general" data-toggle="tab">General</a></li>
		<li><a href="#p_muid_tab_options" data-toggle="tab">Options</a></li>
		<li><a href="#p_muid_tab_conditions" data-toggle="tab">Conditions</a></li>
	</ul>
	<div id="p_muid_module_tabs" class="tab-content">
		<div class="tab-pane active" id="p_muid_tab_general">
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
				<label><span class="pf-label">Name/Title</span>
					<input class="pf-field" type="text" name="name" size="24" value="<?php echo htmlspecialchars($this->entity->name); ?>" /></label>
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
					<input class="pf-field" type="text" name="position" size="24" value="<?php echo htmlspecialchars($this->entity->position); ?>" />
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
					<input class="pf-field" type="text" name="order" size="10" value="<?php echo htmlspecialchars($this->entity->order); ?>" /></label>
			</div>
			<div class="pf-element pf-heading">
				<h3>Module Type</h3>
			</div>
			<br class="pf-clearing" />
			<?php $i=0; foreach ($this->modules as $cur_component => $cur_modules) { $i++; ?>
			<div class="pf-element pf-full-width component_modules">
				<div style="padding: .5em;" class="ui-helper-clearfix<?php echo ($i % 2) ? '' : ' alert-info'; ?>">
					<strong class="pf-label" style="font-size: 1.1em;"><?php echo htmlspecialchars($pines->info->$cur_component->name); ?></strong>
					<div class="pf-group">
						<?php foreach ($cur_modules as $cur_modname => $cur_module) { ?>
						<div class="pf-field">
							<label>
								<input type="radio" name="type" value="<?php echo htmlspecialchars("$cur_component/$cur_modname"); ?>"<?php echo ($this->entity->type == "$cur_component/$cur_modname") ? ' checked="checked"': ''; ?> />
								<?php if (isset($cur_module['form']) || isset($cur_module['form_callback'])) { ?>
								<span class="form">&nbsp;</span>
								<?php } ?>
								<strong><?php echo htmlspecialchars($cur_module['cname']); ?></strong>
								<span style="display: block; padding: 0 0 0 1.8em;">
									<?php echo htmlspecialchars($cur_module['description']); ?>
								</span>
								<span style="display: block; padding: 0 0 .6em 1.8em;">
									<span class="label"><?php echo str_replace(' ', '</span> <span class="label">', htmlspecialchars(isset($cur_module['type']) ? $cur_module['type'] : 'module imodule')); ?></span>
								</span>
							</label>
						</div>
						<?php } ?>
					</div>
				</div>
			</div>
			<?php } ?>
			<br class="pf-clearing" />
		</div>
		<div class="tab-pane" id="p_muid_tab_options">
			<div class="pf-element pf-heading">
				<h3>Module Options</h3>
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
							<input class="pf-field" type="text" name="cur_option_name" size="24" />
						</label>
					</div>
					<div class="pf-element">
						<label>
							<span class="pf-label">Value</span>
							<input class="pf-field" type="text" name="cur_option_value" size="24" />
						</label>
					</div>
				</div>
				<br style="clear: both; height: 1px;" />
			</div>
			<div class="pf-element pf-heading">
				<h3>Inline Module Format</h3>
				<p>This is the text you would use to place this module inline with content. (As long as this module type allows inline use and com_imodules is installed.)</p>
			</div>
			<div class="pf-element pf-full-width">
				<div id="p_muid_inline_format" class="ui-widget-content ui-state-highlight" style="padding: 1em; font-family: monospace; font-size: .9em; white-space: pre-wrap;"></div>
			</div>
			<br class="pf-clearing" />
		</div>
		<div class="tab-pane" id="p_muid_tab_conditions">
			<div class="pf-element pf-heading">
				<h3>Module Conditions</h3>
				<p>Users will only see this module if these conditions are met.</p>
			</div>
			<div class="pf-element pf-full-width">
				<?php
				$module = new module('system', 'conditions');
				$module->conditions = $this->entity->conditions;
				echo $module->render();
				unset($module);
				?>
			</div>
			<br class="pf-clearing" />
		</div>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_modules', 'module/list'))); ?>);" value="Cancel" />
	</div>
</form>