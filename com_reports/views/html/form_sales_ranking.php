<?php
/**
 * Display a form to edit the details of a sales ranking.
 *
 * @package Components\reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$this->title = isset($this->entity->guid) ? 'Editing Sales Ranking ['.htmlspecialchars($this->entity->guid).']' : 'New Sales Ranking';
$pines->com_jstree->load();
$pines->com_pgrid->load();
?>
<script type='text/javascript'>
	pines(function(){
		var top_location = <?php echo json_encode("{$this->entity->top_location->guid}"); ?>;
		$("#p_muid_form [name=start], #p_muid_form [name=end]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});
		// Location Tree
		var top_location_input = $("#p_muid_form [name=top_location]");
		$("#p_muid_form .location_tree")
		.bind("select_node.jstree", function(e, data){
			top_location = data.inst.get_selected().attr("id").replace("p_muid_", "");
			top_location_input.val(top_location);
			change_location(top_location);
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
					"url" : <?php echo json_encode(pines_url('com_jstree', 'groupjson')); ?>
				}
			},
			"ui" : {
				"select_limit" : 1,
				"initially_select" : [top_location]
			}
		});

		var goal_box;
		// Sales Goals Grid
		var goal_grid = $("#p_muid_sales_goals").pgrid({
			pgrid_view_height: "300px",
			pgrid_paginate: false,
			pgrid_child_prefix: "ch_",
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{
					type: 'text',
					title: 'Enter a Sales Goal',
					load: function(textbox){
						goal_box = textbox;
						textbox.keydown(function(e){
							if (e.keyCode == 13)
								update_goal(textbox.val());
						});
					}
				},
				{
					type: 'button',
					text: 'Update Goal',
					extra_class: 'picon picon-dialog-ok-apply',
					multi_select: true,
					click: function(){
						update_goal(goal_box.val());
					}
				},
				{type: 'separator'},
				{type: 'button', text: 'Expand', title: 'Expand All', extra_class: 'picon picon-arrow-down', selection_optional: true, return_all_rows: true, click: function(e, rows){
					rows.pgrid_expand_rows();
				}},
				{type: 'button', text: 'Collapse', title: 'Collapse All', extra_class: 'picon picon-arrow-right', selection_optional: true, return_all_rows: true, click: function(e, rows){
					rows.pgrid_collapse_rows();
				}},
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post(<?php echo json_encode(pines_url('system', 'csv')); ?>, {
						filename: 'sales ranking',
						content: rows
					});
				}}
			]
		});

		var change_location = function(new_location){
			var loader;
			$.ajax({
				url: <?php echo json_encode(pines_url('com_reports', 'salesrankings_locationcontents')); ?>,
				type: "GET",
				dataType: "json",
				data: {"id": new_location},
				beforeSend: function(){
					loader = $.pnotify({
						title: 'Loading',
						text: 'Loading location...',
						icon: 'picon picon-throbber',
						nonblock: true,
						hide: false,
						history: false
					});
					goal_grid.pgrid_get_all_rows().pgrid_delete();
				},
				complete: function(){
					loader.pnotify_remove();
				},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
				},
				success: function(data){
					if (!data) {
						alert("Nothing was found in this location.");
						return;
					}
					var struct = [];
					$.each(data, function(){
						struct.push({
							"key": this.guid,
							"classes": (this.parent ? "parent" : "")+(this.child ? " child ch_"+pines.safe(this.parent_id) : ""),
							"values": [
								pines.safe(this.name),
								this.type == "location" ? "Location" : (this.type == "employee" ? "Employee" : "Unknown"),
								"0.00"
							]
						});
					});
					goal_grid.pgrid_add(struct);
					load_grid();
					save_grid();
				}
			});
		};

		var update_goal = function(new_goal){
			var new_value = parseFloat(new_goal);
			new_value = isNaN(new_value) ? "0.00" : String(new_value.toFixed(2));
			goal_grid.pgrid_get_selected_rows().pgrid_set_value(3, pines.safe(new_value));
			save_grid();
		};

		var save_grid = function(){
			var sales_goals = {};
			goal_grid.pgrid_get_all_rows().each(function(){
				var cur_row = $(this);
				sales_goals[cur_row.attr("title")] = cur_row.pgrid_get_value(3);
			});
			$("#p_muid_form input[name=sales_goals]").val(JSON.stringify(sales_goals));
		};

		var load_grid = function(){
			var sales_json = $("#p_muid_form input[name=sales_goals]").val();
			if (!sales_json || sales_json == "")
				return;
			var sales_goals = JSON.parse(sales_json);
			if (!sales_goals)
				return;
			goal_grid.pgrid_get_all_rows().each(function(){
				var cur_row = $(this);
				if (typeof sales_goals[cur_row.attr("title")] != "undefined")
					cur_row.pgrid_set_value(3, String(parseFloat(sales_goals[cur_row.attr("title")]).toFixed(2)));
			});
		}
	});
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_reports', 'savesalesranking')); ?>">
	<div class="pf-element">
		<label><span class="pf-label">Ranking Name</span>
			<input class="pf-field" type="text" name="ranking_name" value="<?php echo htmlspecialchars($this->entity->name); ?>" /></label>
	</div>

	<div class="pf-element pf-heading">
		<h3>Timespan and Highest Company Division</h3>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Start Date</span>
			<input class="pf-field" type="text" name="start" value="<?php echo ($this->entity->start_date) ? htmlspecialchars(format_date($this->entity->start_date, 'date_sort')) : htmlspecialchars(format_date(time(), 'date_sort')); ?>" style="text-align: center;" /></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">End Date</span>
			<input class="pf-field" type="text" name="end" value="<?php echo ($this->entity->end_date) ? htmlspecialchars(format_date($this->entity->end_date - 1, 'date_sort')) : htmlspecialchars(format_date(time(), 'date_sort')); ?>" style="text-align: center;" /></label>
	</div>
	<?php if ($pines->depend->check('component', 'com_mifi')) { ?>
	<div class="pf-element">
		<span class="pf-label">Exclude Pending Contracts</span>
		<label><input type="checkbox" class="pf-field" name="exclude_pending_contracts" value="ON"<?php echo $this->entity->exclude_pending_contracts ? ' checked="checked"' : ''; ?> /> Exclude sales with pending MiFi contracts from rankings.</label>
	</div>
	<?php } ?>
	<div class="pf-element">
		<span class="pf-label">New Hire Goals</span>
		<label><input type="checkbox" class="pf-field" name="calc_nh_goals" value="ON"<?php echo $this->entity->calc_nh_goals ? ' checked="checked"' : ''; ?> /> Calculate new hire goals using training completion date.</label>
	</div>
	<div class="pf-element">
		<span class="pf-label">Division</span>
		<div class="pf-group">
			<div class="pf-field">
				<label><input type="checkbox" name="only_below" value="ON"<?php echo $this->entity->only_below ? ' checked="checked"' : ''; ?> /> Only show locations <em>below</em> the selected location.</label>
			</div>
			<div class="pf-field">
				<div class="location_tree"></div>
			</div>
		</div>
	</div>
	<div class="pf-element pf-heading">
		<h3>Sales Goals</h3>
	</div>
	<div class="pf-element pf-full-width">
		<table id="p_muid_sales_goals">
			<thead>
				<tr>
					<th>Name</th>
					<th>Type</th>
					<th>Dollar Goal</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
	<div class="pf-element pf-buttons">
		<?php if (isset($this->entity->guid)) { ?>
		<input type="hidden" name="id" value="<?php echo htmlspecialchars($this->entity->guid); ?>" />
		<?php } ?>
		<input type="hidden" name="top_location" />
		<input type="hidden" name="sales_goals" value="<?php echo htmlspecialchars(json_encode((array) @array_combine(array_map('strval', array_keys($this->entity->sales_goals)), array_map('strval', $this->entity->sales_goals)))); ?>" />
		<input class="pf-button btn btn-primary" type="submit" value="Save" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_reports', 'salesrankings'))); ?>);" value="Cancel" />
	</div>
</form>