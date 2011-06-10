<?php
/**
 * Lists all of the countsheets.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Countsheets';
$pines->com_pgrid->load();
$pines->com_jstree->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_sales/countsheet/list'];
?>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var submit_url = "<?php echo addslashes(pines_url('com_sales', 'countsheet/list')); ?>";
		var submit_search = function(){
			// Submit the form with all of the fields.
			pines.get(submit_url, {
				"location": location,
				"descendents": descendents,
				"all_time": all_time,
				"start_date": start_date,
				"end_date": end_date
			});
		};

		// Timespan Defaults
		var all_time = <?php echo $this->all_time ? 'true' : 'false'; ?>;
		var start_date = "<?php echo $this->start_date ? addslashes(format_date($this->start_date, 'date_sort')) : ''; ?>";
		var end_date = "<?php echo $this->end_date ? addslashes(format_date($this->end_date - 1, 'date_sort')) : ''; ?>";
		// Location Defaults
		var location = "<?php echo $this->location->guid; ?>";
		var descendents = <?php echo $this->descendents ? 'true' : 'false'; ?>;

		// Group Tree
		var assign_location = $("#p_muid_assign_dialog [name=location]");
		$("#p_muid_location_tree")
		.bind("select_node.jstree", function(e, data){
			assign_location.val(data.inst.get_selected().attr("id").replace("p_muid_", ""));
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
				"select_limit" : 1
			}
		});

		var assign_dialog = $("#p_muid_assign_dialog");

		assign_dialog.find("form").submit(function(){
			assign_dialog.dialog('option', 'buttons').Done();
			return false;
		});
		assign_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: "250px",
			buttons: {
				"Assign": function(){
					var assign_to = assign_dialog.find(":input[name=location]").val();
					if (assign_to == "") {
						alert("Please select a group");
					} else {
						pines.post("<?php echo addslashes(pines_url('com_sales', 'countsheet/assign')); ?>", {
							location: assign_to
						});
						assign_dialog.dialog("close");
					}
				}
			}
		});

		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				{type: 'button', title: 'Location', extra_class: 'picon picon-applications-internet', selection_optional: true, click: function(){countsheet_grid.location_form();}},
				{type: 'button', title: 'Timespan', extra_class: 'picon picon-view-time-schedule', selection_optional: true, click: function(){countsheet_grid.date_form();}},
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/newcountsheet')) { ?>
				{type: 'button', text: 'New', extra_class: 'picon picon-document-new', selection_optional: true, url: '<?php echo addslashes(pines_url('com_sales', 'countsheet/edit')); ?>'},
				<?php } if (gatekeeper('com_sales/editcountsheet')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-document-edit', double_click: true, url: '<?php echo addslashes(pines_url('com_sales', 'countsheet/edit', array('id' => '__title__'))); ?>'},
				<?php } if (gatekeeper('com_sales/approvecountsheet')) { ?>
				{type: 'button', text: 'Review', extra_class: 'picon picon-checkbox', url: '<?php echo addslashes(pines_url('com_sales', 'countsheet/approve', array('id' => '__title__'))); ?>'},
				<?php } if (gatekeeper('com_sales/assigncountsheet')) { ?>
				{type: 'button', text: 'Assign', extra_class: 'picon picon-view-task-add', selection_optional: true, click: function(e, rows){
					assign_dialog.dialog("open");
				}},
				<?php } ?>
				{type: 'separator'},
				<?php if (gatekeeper('com_sales/deletecountsheet')) { ?>
				{type: 'button', text: 'Delete', extra_class: 'picon picon-edit-delete', confirm: true, multi_select: true, url: '<?php echo addslashes(pines_url('com_sales', 'countsheet/delete', array('id' => '__title__'))); ?>', delimiter: ','},
				{type: 'separator'},
				<?php } ?>
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'countsheets',
						content: rows
					});
				}}
			],
			pgrid_sort_col: 1,
			pgrid_sort_ord: 'asc',
			pgrid_state_change: function(state) {
				if (typeof state_xhr == "object")
					state_xhr.abort();
				cur_state = JSON.stringify(state);
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_sales/countsheet/list", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		var countsheet_grid = $("#p_muid_grid").pgrid(cur_options);

		countsheet_grid.date_form = function(){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_sales', 'forms/dateselect')); ?>",
				type: "POST",
				dataType: "html",
				data: {"all_time": all_time, "start_date": start_date, "end_date": end_date},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retreive the date form:\n"+XMLHttpRequest.status+": "+textStatus);
				},
				success: function(data){
					if (data == "")
						return;
					var form = $("<div title=\"Date Selector\"></div>");
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						height: 315,
						modal: true,
						open: function(){
							form.html(data);
						},
						close: function(){
							form.remove();
						},
						buttons: {
							"Update": function(){
								if (form.find(":input[name=timespan_saver]").val() == "alltime") {
									all_time = true;
								} else {
									all_time = false;
									start_date = form.find(":input[name=start_date]").val();
									end_date = form.find(":input[name=end_date]").val();
								}
								form.dialog('close');
								submit_search();
							}
						}
					});
				}
			});
		};
		countsheet_grid.location_form = function(){
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_sales', 'forms/locationselect')); ?>",
				type: "POST",
				dataType: "html",
				data: {"location": location, "descendents": descendents},
				error: function(XMLHttpRequest, textStatus){
					pines.error("An error occured while trying to retreive the location form:\n"+XMLHttpRequest.status+": "+textStatus);
				},
				success: function(data){
					if (data == "")
						return;
					var form = $("<div title=\"Location Selector\"></div>");
					form.dialog({
						bgiframe: true,
						autoOpen: true,
						height: 250,
						modal: true,
						open: function(){
							form.html(data);
						},
						close: function(){
							form.remove();
						},
						buttons: {
							"Update": function(){
								location = form.find(":input[name=location]").val();
								if (form.find(":input[name=descendents]").attr('checked'))
									descendents = true;
								else
									descendents = false;
								form.dialog('close');
								submit_search();
							}
						}
					});
				}
			});
		};
	});
	// ]]>
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>ID</th>
			<th>Location</th>
			<th>Created By</th>
			<th>Created</th>
			<th>Modified</th>
			<th>Committed</th>
			<th>Status</th>
			<th>Reviewed By</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->countsheets as $countsheet) { ?>
		<tr title="<?php echo $countsheet->guid; ?>">
			<td><?php echo $countsheet->guid; ?></td>
			<td><?php echo htmlspecialchars($countsheet->group->name); ?></td>
			<td><?php echo htmlspecialchars($countsheet->user->name); ?></td>
			<td><?php echo format_date($countsheet->p_cdate); ?></td>
			<td><?php echo format_date($countsheet->p_mdate); ?></td>
			<td><?php echo $countsheet->final ? 'Yes' : 'No'; ?></td>
			<td><?php switch ($countsheet->status) {
				case 'approved':
					echo 'Approved';
					break;
				case 'declined':
					echo 'Declined';
					break;
				case 'info_requested':
					echo 'Info Requested';
					break;
				case 'pending':
					echo 'Pending';
					break;
				default:
					echo 'Unrecognized';
					break;
			} ?></td>
			<td><?php echo $countsheet->review_user->guid ? htmlspecialchars("{$countsheet->review_user->name} [{$countsheet->review_user->username}]") : '' ; ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<div id="p_muid_assign_dialog" title="Assign a Countsheet to" style="display: none;">
	<div id="p_muid_location_tree"></div>
	<input type="hidden" name="location" />
</div>