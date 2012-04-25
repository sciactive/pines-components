<?php
/**
 * Provides a form for the user to edit an entry.
 *
 * @package Components
 * @subpackage menueditor
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Entry' : 'Editing ['.htmlspecialchars($this->entity->name).']';
$this->note = 'Provide entry details in this form.';
$pines->com_pgrid->load();
$pines->com_jstree->load();

// Make a JSON structure of the current entire menu.
$menus = array();
foreach ($this->captured_menu_arrays as $cur_entry) {
	// Transform URL arrays into actual URLs.
	if (isset($cur_entry['href']) && (array) $cur_entry['href'] === $cur_entry['href'])
		$cur_entry['href'] = call_user_func_array(array($pines->template, 'url'), $cur_entry['href']);
	$tmp_path = explode('/', $cur_entry['path']);
	$cur_menus =& $menus;
	foreach ($tmp_path as $cur_path) {
		if (!isset($cur_menus[$cur_path]))
			$cur_menus[$cur_path] = array();
		$cur_menus =& $cur_menus[$cur_path];
	}
	$cur_menus[0] = (object) array(
		'data' => $cur_path.' ['.$cur_entry['text'].']',
		'attr' => (object) array(
			'id' => 'p_muid_'.md5($cur_entry['path'])
		),
		'metadata' => (object) $cur_entry
	);
}

/**
 * Pack menu objects' children into the right structure.
 * @param mixed &$menu_object A menu object (array).
 */
function com_menueditor__pack_children(&$menu_object) {
	if ($menu_object[0]->metadata->sort)
		ksort($menu_object);
	$new_item = $menu_object[0];
	unset($menu_object[0]);
	$children = array_values($menu_object);
	$menu_object = $new_item;
	if (!$children)
		return;
	$menu_object->children = $children;
	foreach ($menu_object->children as &$cur_child) {
		com_menueditor__pack_children($cur_child);
	}
	unset($cur_child);
}

// Format it for use in the jsTree.
foreach ($menus as &$cur_child) {
	if ($cur_child[0]->metadata->sort)
		ksort($cur_child);
	com_menueditor__pack_children($cur_child);
	$cur_child = array($cur_child);
}
unset($cur_child);
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
</style>
<script type='text/javascript'>
	pines(function(){
		// Position box.
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


		// Menu Structure
		var menu = <?php echo json_encode($menus); ?>;

		// Location Tree
		var location = $("#p_muid_location");
		var location_container = $("#p_muid_location_container");
		var sel_path = $("#p_muid_sel_path"), sel_position = $("#p_muid_sel_position"), sel_sort = $("#p_muid_sel_sort"), sel_text = $("#p_muid_sel_text"), sel_href = $("#p_muid_sel_href"), sel_onclick = $("#p_muid_sel_onclick"), sel_depend = $("#p_muid_sel_depend");
		$("#p_muid_top_menu").change(function(){
			var cur_menu, cur_value = $(this).val();
			if (cur_value == '--new--') {
				$("#p_muid_location_form").hide();
				$("#p_muid_position").show();
				cur_menu = [];
			} else {
				$("#p_muid_location_form").show();
				$("#p_muid_position").hide();
				cur_menu = menu[cur_value];
			}
			location_container.empty();
			$("<div></div>").appendTo(location_container).bind("select_node.jstree", function(e, data){
				var cur_item = data.inst.get_selected();
				var data = cur_item.data();
				location.val(data.path);
				if (typeof data.path != "undefined")
					sel_path.show().find(".text").html(pines.safe(data.path));
				else
					sel_path.hide();
				if (typeof data.position != "undefined")
					sel_position.show().find(".text").html(pines.safe(data.position));
				else
					sel_position.hide();
				if (typeof data.sort != "undefined")
					sel_sort.find(".text").html("Yes");
				else
					sel_sort.find(".text").html("No");
				if (typeof data.text != "undefined")
					sel_text.show().find(".text").html(pines.safe(data.text));
				else
					sel_text.hide();
				if (typeof data.href != "undefined")
					sel_href.show().find(".text").html(pines.safe(data.href));
				else
					sel_href.hide();
				if (typeof data.onclick != "undefined")
					sel_onclick.show().find(".text").html(pines.safe(data.onclick));
				else
					sel_onclick.hide();
				if (typeof data.depend != "undefined" && !$.isEmptyObject(data.depend)) {
					// Format the dependencies.
					var depend_ul = $("<ul></ul>");
					$.each(data.depend, function(key, val){
						depend_ul.append("<li><strong>"+pines.safe(key)+"</strong>: "+pines.safe(val)+"</li>");
					});
					sel_depend.show().children("ul").remove().end().append(depend_ul);
				} else
					sel_depend.hide();
			}).bind("loaded.jstree", function(e, data){
				var path = data.inst.get_path("#"+data.inst.get_settings().ui.initially_select, true);
				if (!path.length)
					data.inst.select_node($("#p_muid_location_container li:first"), true);
				else
					data.inst.open_node("#"+path.join(", #"), false, true);
			}).jstree({
				"plugins" : [ "themes", "json_data", "ui" ],
				"json_data" : {
					"data" : cur_menu
				},
				"ui" : {
					"select_limit" : 1,
					"initially_select" : [<?php echo json_encode('p_muid_'.md5($this->entity->location)); ?>]
				}
			});
		}).change();


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
	});
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_menueditor', 'entry/save')); ?>">
	<div class="pf-element pf-heading">
		<h3>Menu Entry Location</h3>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Menu</span>
			<span class="pf-note">What menu does this entry belong to?</span>
			<select class="pf-field" name="top_menu" id="p_muid_top_menu">
				<option value="--new--"<?php echo isset($this->entity->top_menu) ? '' : ' selected="selected"'; ?>>-- New Menu --</option>
				<?php foreach ($menus as $key => $cur_menu) { ?>
				<option value="<?php echo htmlspecialchars($key); ?>"<?php echo $this->entity->top_menu == $key ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_menu[0]->data); ?></option>
				<?php } ?>
			</select></label>
	</div>
	<div class="pf-element pf-full-width" id="p_muid_location_form">
		<span class="pf-label">Location</span>
		<span class="pf-note">Under what other entry is this entry placed?</span>
		<br class="pf-clearing" />
		<div class="ui-widget-content ui-corner-all ui-helper-clearfix" style="clear: left; margin-top: .5em; font-size: .8em;">
			<div style="padding: .5em">
				<div style="float: left; width: 49%;">
					<div class="ui-widget-header ui-corner-all">Choose an Entry</div>
					<div id="p_muid_location_container">
						<div></div>
					</div>
				</div>
				<div style="float: right; width: 49%;">
					<div class="ui-widget-header ui-corner-all">Entry Details</div>
					<ul style="padding-right: 1em; word-wrap: break-word;">
						<li id="p_muid_sel_path"><strong>Path:</strong> <span class="text">&nbsp;</span></li>
						<li id="p_muid_sel_text"><strong>Text:</strong> <span class="text">&nbsp;</span></li>
						<li id="p_muid_sel_position"><strong>Position:</strong> <span class="text">&nbsp;</span></li>
						<li id="p_muid_sel_sort"><strong>Sort Children:</strong> <span class="text">&nbsp;</span></li>
						<li id="p_muid_sel_href"><strong>Link:</strong> <span class="text">&nbsp;</span></li>
						<li id="p_muid_sel_onclick"><strong>Click Action:</strong> <span class="text">&nbsp;</span></li>
						<li id="p_muid_sel_depend"><strong>Conditions:</strong></li>
					</ul>
				</div>
			</div>
		</div>
		<input type="hidden" name="location" id="p_muid_location" value="<?php echo htmlspecialchars($this->location); ?>" />
	</div>
	<div class="pf-element pf-heading">
		<h3>Menu Entry Information</h3>
	</div>
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
	<div class="pf-element" id="p_muid_position">
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
	<?php if (!in_array('name', (array) $this->disabled_fields)) { ?>
	<div class="pf-element">
		<label><span class="pf-label">Name</span>
			<span class="pf-note">This is what will make its path. It also determines its position alphanumerically if the location is sorted.</span>
			<input class="pf-field" type="text" name="name" size="24" value="<?php echo htmlspecialchars($this->entity->name); ?>" /></label>
	</div>
	<?php } if (!in_array('text', (array) $this->disabled_fields)) { ?>
	<div class="pf-element">
		<label><span class="pf-label">Text</span>
			<span class="pf-note">This is the text that will appear on the menu entry.</span>
			<input class="pf-field" type="text" name="text" size="24" value="<?php echo htmlspecialchars($this->entity->text); ?>" /></label>
	</div>
	<?php } if (!in_array('sort_order', (array) $this->disabled_fields)) { ?>
	<div class="pf-element">
		<label><span class="pf-label">Sort Order</span>
			<span class="pf-note">Menu entries created by this system will be sorted using this value. However, if they are placed in a sorted parent, they will use the name.</span>
			<input class="pf-field" type="text" name="sort_order" size="24" value="<?php echo htmlspecialchars($this->entity->sort_order); ?>" /></label>
	</div>
	<?php } if (!in_array('enabled', (array) $this->disabled_fields)) { ?>
	<div class="pf-element">
		<label><span class="pf-label">Enabled</span>
			<input class="pf-field" type="checkbox" name="enabled" value="ON"<?php echo $this->entity->enabled ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<?php } if (!in_array('sort', (array) $this->disabled_fields)) { ?>
	<div class="pf-element">
		<label><span class="pf-label">Sort Children</span>
			<input class="pf-field" type="checkbox" name="sort" value="ON"<?php echo $this->entity->sort ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<?php } if (!in_array('link', (array) $this->disabled_fields)) { ?>
	<div class="pf-element">
		<label><span class="pf-label">Link</span>
			<input class="pf-field" type="text" name="link" size="24" value="<?php echo htmlspecialchars($this->entity->link); ?>" /></label>
	</div>
	<?php } if (!in_array('text', (array) $this->disabled_fields) && gatekeeper('com_menueditor/jsentry')) { ?>
	<div class="pf-element">
		<label><span class="pf-label">Onclick JavaScript</span>
			<input class="pf-field" type="text" name="onclick" size="24" value="<?php echo htmlspecialchars($this->entity->onclick); ?>" /></label>
	</div>
	<?php } ?>
	<div class="pf-element pf-heading">
		<h3>Menu Entry Conditions</h3>
		<p>Users will only see this entry if these conditions are met.</p>
	</div>
	<?php if (!in_array('children', (array) $this->disabled_fields)) { ?>
	<div class="pf-element">
		<label><span class="pf-label">Require Children</span>
			<span class="pf-note">Only show the menu entry if it has children.</span>
			<input class="pf-field" type="checkbox" name="children" value="ON"<?php echo $this->entity->children ? ' checked="checked"' : ''; ?> /></label>
	</div>
	<?php } if (!in_array('conditions', (array) $this->disabled_fields)) { ?>
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
					<input class="pf-field" type="text" name="cur_condition_type" id="p_muid_cur_condition_type" size="24" /></label>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Value</span>
					<input class="pf-field" type="text" name="cur_condition_value" size="24" /></label>
			</div>
		</div>
		<br style="clear: both; height: 1px;" />
	</div>
	<?php } if (!$this->dialog) { ?>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button btn btn-primary" type="submit" value="Submit" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_menueditor', 'entry/list'))); ?>);" value="Cancel" />
	</div>
	<?php } ?>
</form>