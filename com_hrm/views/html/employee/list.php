<?php
/**
 * Lists employees and provides functions to manipulate them.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!$this->employed ? 'Prior ' : '').'Employees';
$pines->com_pgrid->load();
if (isset($_SESSION['user']) && is_array($_SESSION['user']->pgrid_saved_states))
	$this->pgrid_state = $_SESSION['user']->pgrid_saved_states['com_hrm/employee/list'];
?>
<script type="text/javascript">
	// <![CDATA[

	pines(function(){
		var employees;
		<?php if (gatekeeper('com_hrm/editbonus')) { ?>
		var bonus_dialog = $("#p_muid_bonus_dialog");

		$("#p_muid_bonus_dialog [name=effective_date]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});

		bonus_dialog.find("form").submit(function(){
			bonus_dialog.dialog('option', 'buttons').Done();
			return false;
		});
		bonus_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				"Grant Bonus": function(){
					pines.post("<?php echo addslashes(pines_url('com_hrm', 'bonus/add')); ?>", {
						employees: employees,
						name: bonus_dialog.find("[name=name]").val(),
						date: bonus_dialog.find("[name=effective_date]").val(),
						amount: bonus_dialog.find("[name=amount]").val(),
						comments: bonus_dialog.find("[name=comments]").val()
					});
					bonus_dialog.dialog("close");
				}
			}
		});
		<?php } if (gatekeeper('com_hrm/editadjustment')) { ?>
		var adjustment_dialog = $("#p_muid_adjustment_dialog");

		$("#p_muid_adjustment_dialog [name=effective_date]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});

		adjustment_dialog.find("form").submit(function(){
			adjustment_dialog.dialog('option', 'buttons').Done();
			return false;
		});
		adjustment_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				"Grant Adjustment": function(){
					pines.post("<?php echo addslashes(pines_url('com_hrm', 'adjustment/add')); ?>", {
						employees: employees,
						name: adjustment_dialog.find("[name=name]").val(),
						date: adjustment_dialog.find("[name=effective_date]").val(),
						amount: adjustment_dialog.find("[name=amount]").val(),
						comments: adjustment_dialog.find("[name=comments]").val()
					});
					adjustment_dialog.dialog("close");
				}
			}
		});
		<?php } if (gatekeeper('com_hrm/fileissue')) { ?>
		var issue_dialog = $("#p_muid_issue_dialog");

		$("#p_muid_issue_dialog [name=effective_date]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});

		issue_dialog.find("form").submit(function(){
			issue_dialog.dialog('option', 'buttons').Done();
			return false;
		});
		issue_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				"File Issue": function(){
					pines.post("<?php echo addslashes(pines_url('com_hrm', 'issue/file')); ?>", {
						items: employees,
						issue_type: issue_dialog.find(":input[name=issue_type]").val(),
						date: $("#p_muid_issue_dialog [name=effective_date]").val(),
						quantity: $("#p_muid_issue_dialog [name=quantity]").val(),
						comments: $("#p_muid_issue_dialog [name=comments]").val()
					});
					issue_dialog.dialog("close");
				}
			}
		});
		<?php } if ($this->employed) { ?>
		var terminate_dialog = $("#p_muid_terminate_dialog");

		terminate_dialog.find("form").submit(function(){
			terminate_dialog.dialog('option', 'buttons').Done();
			return false;
		});
		terminate_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				"Terminate": function(){
					var dispose_as = terminate_dialog.find(":input[name=reason]").val();
					pines.post("<?php echo addslashes(pines_url('com_hrm', 'employee/terminate')); ?>", {
						items: employees,
						date: $("#p_muid_terminate_dialog [name=effective_date]").val(),
						reason: dispose_as,
						employed: '<?php echo $this->employed ? 'true' : 'false'; ?>'
					});
					terminate_dialog.dialog("close");
				}
			}
		});

		<?php } else { ?>
		var rehire_dialog = $("#p_muid_rehire_dialog");

		rehire_dialog.find("form").submit(function(){
			rehire_dialog.dialog('option', 'buttons').Done();
			return false;
		});
		rehire_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				"Rehire": function(){
					pines.post("<?php echo addslashes(pines_url('com_hrm', 'employee/terminate')); ?>", {
						items: employees,
						date: $("#p_muid_rehire_dialog [name=effective_date]").val(),
						reason: 'rehired',
						employed: '<?php echo $this->employed ? 'true' : 'false'; ?>'
					});
					rehire_dialog.dialog("close");
				}
			}
		});
		<?php } ?>

		var state_xhr;
		var cur_state = JSON.parse("<?php echo (isset($this->pgrid_state) ? addslashes($this->pgrid_state) : '{}');?>");
		var cur_defaults = {
			pgrid_toolbar: true,
			pgrid_toolbar_contents: [
				<?php if (gatekeeper('com_hrm/addemployee')) { if (!$this->employed) { ?>
					{type: 'button', text: 'Rehire', extra_class: 'picon picon-edit-undo', multi_select: true, click: function(e, rows){
					employees = "";
					$.each(rows.pgrid_export_rows(), function(){
						if (employees != "")
							employees += ",";
						employees += this.key;
					});
					if (rows.length == 1)
						rehire_dialog.find("div.dialog_title").html('<h1>'+rows.pgrid_get_value(3)+'</h1>');
					else
						rehire_dialog.find("div.dialog_title").html('<h1>'+rows.length+' Employees</h1>');
					rehire_dialog.dialog("open");
					}},
				<?php } else { ?>
				{type: 'button', text: 'Add User(s)', extra_class: 'picon picon-list-add-user', selection_optional: true, click: function(){
					$.ajax({
						url: "<?php echo addslashes(pines_url('com_hrm', 'forms/userselect')); ?>",
						type: "POST",
						dataType: "html",
						error: function(XMLHttpRequest, textStatus){
							pines.error("An error occured while trying to retrieve the user select form:\n"+XMLHttpRequest.status+": "+textStatus);
						},
						success: function(data){
							if (data == "")
								return;
							var form = $("<div title=\"Select User(s)\"></div>");
							form.html(data+"<br />");
							form.dialog({
								bgiframe: true,
								autoOpen: true,
								modal: true,
								close: function(){
									form.remove();
								},
								buttons: {
									"Make Employee": function(){
										form.dialog('close');
										var users = form.find(":input[name=users]").val();
										pines.post("<?php echo addslashes(pines_url('com_hrm', 'employee/add')); ?>", { "id": users });
									}
								}
							});
						}
					});
				}},
				<?php } } if (gatekeeper('com_hrm/editemployee')) { ?>
				{type: 'button', text: 'Edit', extra_class: 'picon picon-user-properties', double_click: true, url: '<?php echo addslashes(pines_url('com_hrm', 'employee/edit', array('id' => '__title__'))); ?>'},
				<?php } ?>
				//{type: 'button', text: 'E-Mail', extra_class: 'picon picon-mail-message-new', multi_select: true, url: 'mailto:__col_2__', delimiter: ','},
				{type: 'separator'},
				{type: 'button', text: 'History', extra_class: 'picon picon-folder-html', url: '<?php echo addslashes(pines_url('com_hrm', 'employee/history', array('id' => '__title__'))); ?>'},
				<?php if (gatekeeper('com_hrm/editbonus')) { ?>
				{type: 'button', title: 'Bonus', extra_class: 'picon picon-get-hot-new-stuff', multi_select: true, click: function(e, rows){
					employees = "";
					$.each(rows.pgrid_export_rows(), function(){
						if (employees != "")
							employees += ",";
						employees += this.key;
					});
					if (rows.length == 1)
						bonus_dialog.find("div.dialog_title").html('<h1>'+rows.pgrid_get_value(3)+'</h1>');
					else
						bonus_dialog.find("div.dialog_title").html('<h1>'+rows.length+' Employees</h1>');
					bonus_dialog.dialog("open");
				}},
				<?php } if (gatekeeper('com_hrm/editadjustment')) { ?>
				{type: 'button', title: 'Adjustment', extra_class: 'picon picon-accessories-calculator', multi_select: true, click: function(e, rows){
					employees = "";
					$.each(rows.pgrid_export_rows(), function(){
						if (employees != "")
							employees += ",";
						employees += this.key;
					});
					if (rows.length == 1)
						adjustment_dialog.find("div.dialog_title").html('<h1>'+rows.pgrid_get_value(3)+'</h1>');
					else
						adjustment_dialog.find("div.dialog_title").html('<h1>'+rows.length+' Employees</h1>');
					adjustment_dialog.dialog("open");
				}},
				<?php } if (gatekeeper('com_hrm/fileissue')) { ?>
				{type: 'button', title: 'File Issue', extra_class: 'picon picon-task-attention', multi_select: true, click: function(e, rows){
					employees = "";
					$.each(rows.pgrid_export_rows(), function(){
						if (employees != "")
							employees += ",";
						employees += this.key;
					});
					if (rows.length == 1)
						issue_dialog.find("div.dialog_title").html('<h1>'+rows.pgrid_get_value(3)+'</h1>');
					else
						issue_dialog.find("div.dialog_title").html('<h1>'+rows.length+' Employees</h1>');
					issue_dialog.dialog("open");
				}},
				<?php } if (gatekeeper('com_hrm/removeemployee') && $this->employed) { ?>
				{type: 'button', text: 'Terminate', extra_class: 'picon picon-list-remove-user', multi_select: true, click: function(e, rows){
					employees = "";
					$.each(rows.pgrid_export_rows(), function(){
						if (employees != "")
							employees += ",";
						employees += this.key;
					});
					if (rows.length == 1)
						terminate_dialog.find("div.dialog_title").html('<h1>'+rows.pgrid_get_value(3)+'</h1>');
					else
						terminate_dialog.find("div.dialog_title").html('<h1>'+rows.length+' Employees</h1>');
					terminate_dialog.dialog("open");
				}},
				<?php } if (gatekeeper('com_hrm/editbonus')) { ?>
				{type: 'button', text: 'Bonuses', extra_class: 'picon picon-story-editor', selection_optional: true, url: '<?php echo addslashes(pines_url('com_hrm', 'bonus/list')); ?>'},
				<?php } if (gatekeeper('com_hrm/editadjustment')) { ?>
				{type: 'button', text: 'Adjustments', extra_class: 'picon picon-story-editor', selection_optional: true, url: '<?php echo addslashes(pines_url('com_hrm', 'adjustment/list')); ?>'},
				<?php } if (gatekeeper('com_hrm/editissuetypes')) { ?>
				{type: 'button', text: 'Issue Types', extra_class: 'picon picon-story-editor', selection_optional: true, url: '<?php echo addslashes(pines_url('com_hrm', 'issue/list')); ?>'},
				<?php } if (gatekeeper('com_hrm/managerto')) { ?>
				{type: 'button', title: 'Time Off Requests', extra_class: 'picon picon-view-calendar-upcoming-events', selection_optional: true, click: function(e){
					// Show and approve the requested time off.
					$.ajax({
						url: "<?php echo addslashes(pines_url('com_hrm', 'timeoff/review')); ?>",
						type: "POST",
						dataType: "html",
						error: function(XMLHttpRequest, textStatus){
							pines.error("An error occured while trying to retrieve the time off form:\n"+XMLHttpRequest.status+": "+textStatus);
						},
						success: function(data){
							if (data == "")
								return;
							var form = $("<div title=\"Pending Requests for Time Off\"></div>");
							form.dialog({
								bgiframe: true,
								autoOpen: true,
								modal: true,
								width: 700,
								open: function(){
									form.html(data+"<br />").dialog("option", "position", "center");
								},
								close: function(){
									form.remove();
								}
							});
						}
					});
				}},
				<?php } ?>
				{type: 'separator'},
				<?php if ($this->employed) { ?>
				{type: 'button', text: 'Prior', extra_class: 'picon picon-vcs-removed', selection_optional: true, url: '<?php echo addslashes(pines_url('com_hrm', 'employee/list', array('employed' => 'false'))); ?>'},
				<?php } else { ?>
				{type: 'button', text: 'Current', extra_class: 'picon picon-vcs-normal', selection_optional: true, url: '<?php echo addslashes(pines_url('com_hrm', 'employee/list')); ?>'},
				<?php } ?>
				{type: 'separator'},
				{type: 'button', title: 'Select All', extra_class: 'picon picon-document-multiple', select_all: true},
				{type: 'button', title: 'Select None', extra_class: 'picon picon-document-close', select_none: true},
				{type: 'separator'},
				{type: 'button', title: 'Make a Spreadsheet', extra_class: 'picon picon-x-office-spreadsheet', multi_select: true, pass_csv_with_headers: true, click: function(e, rows){
					pines.post("<?php echo addslashes(pines_url('system', 'csv')); ?>", {
						filename: 'employees',
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
				state_xhr = $.post("<?php echo addslashes(pines_url('com_pgrid', 'save_state')); ?>", {view: "com_hrm/employee/list", state: cur_state});
			}
		};
		var cur_options = $.extend(cur_defaults, cur_state);
		$("#p_muid_grid").pgrid(cur_options);
	});

	// ]]>
</script>
<table id="p_muid_grid">
	<thead>
		<tr>
			<th>ID</th>
			<th>Username</th>
			<th>Real Name</th>
			<th>Email</th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($this->employees as $employee) { ?>
		<tr title="<?php echo $employee->guid; ?>">
			<td><?php echo $employee->guid; ?></td>
			<td><?php echo htmlspecialchars($employee->username); ?></td>
			<td><?php echo htmlspecialchars($employee->name); ?></td>
			<td><?php echo htmlspecialchars($employee->email); ?></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
<?php if (gatekeeper('com_hrm/editbonus')) { ?>
<div id="p_muid_bonus_dialog" title="Grant Employee Bonus" style="display: none;">
	<form class="pf-form" method="post" action="">
		<div class="pf-element pf-heading dialog_title"></div>
		<div class="pf-element">
			<label><span class="pf-label">Description</span>
				<input class="ui-widget-content ui-corner-all" type="text" size="24" name="name" value="" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Date</span>
				<input class="ui-widget-content ui-corner-all" type="text" size="24" name="effective_date" value="<?php echo format_date(time(), 'date_sort'); ?>" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Amount</span>
				$ <input class="ui-widget-content ui-corner-all" type="text" size="5" name="amount" value="" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Comments</span>
				<input class="ui-widget-content ui-corner-all" type="text" size="24" name="comments" value="" /></label>
		</div>
	</form>
	<br />
</div>
<?php } if (gatekeeper('com_hrm/editadjustment')) { ?>
<div id="p_muid_adjustment_dialog" title="Grant Employee Adjustment" style="display: none;">
	<form class="pf-form" method="post" action="">
		<div class="pf-element pf-heading dialog_title"></div>
		<div class="pf-element">
			<label><span class="pf-label">Description</span>
				<input class="ui-widget-content ui-corner-all" type="text" size="24" name="name" value="" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Date</span>
				<input class="ui-widget-content ui-corner-all" type="text" size="24" name="effective_date" value="<?php echo format_date(time(), 'date_sort'); ?>" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Amount</span>
				$ <input class="ui-widget-content ui-corner-all" type="text" size="5" name="amount" value="" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Comments</span>
				<input class="ui-widget-content ui-corner-all" type="text" size="24" name="comments" value="" /></label>
		</div>
	</form>
	<br />
</div>
<?php } if (gatekeeper('com_hrm/fileissue')) { ?>
<div id="p_muid_issue_dialog" title="File Employee Issue" style="display: none;">
	<form class="pf-form" method="post" action="">
		<div class="pf-element pf-heading dialog_title"></div>
		<div class="pf-element">
			<label><span class="pf-label">Issue</span>
				<select class="ui-widget-content ui-corner-all" name="issue_type">
					<?php
						foreach ($pines->com_hrm->get_issue_types() as $cur_issue) {
							echo '<option value="'.htmlspecialchars($cur_issue->guid).'">'.htmlspecialchars($cur_issue->name).'</option>';
						}
					?>
				</select></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Occurred on</span>
				<input class="ui-widget-content ui-corner-all" type="text" size="24" name="effective_date" value="<?php echo format_date(time(), 'date_sort'); ?>" /></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label"># of Occurrences</span>
				<select class="ui-widget-content ui-corner-all" name="quantity">
					<option value="1" selected="selected">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5">5</option>
				</select></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Comments</span>
				<input class="ui-widget-content ui-corner-all" type="text" size="24" name="comments" value="" /></label>
		</div>
	</form>
	<br />
</div>
<?php } if ($this->employed) { ?>
<div id="p_muid_terminate_dialog" title="Terminate Employee" style="display: none;">
	<form class="pf-form" method="post" action="">
		<div class="pf-element pf-heading dialog_title"></div>
		<div class="pf-element">
			<label><span class="pf-label">Reason for Termination</span>
			<select class="ui-widget-content ui-corner-all" name="reason">
				<?php 
					foreach ($pines->config->com_hrm->termination_reasons as $cur_dispo) {
						$dispo_array = explode(':', $cur_dispo);
						echo '<option value="'.htmlspecialchars($dispo_array[0]).'">'.htmlspecialchars($dispo_array[1]).'</option>';
					}
				?>
			</select></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Effective Date</span>
			<input class="ui-widget-content ui-corner-all" type="text" size="16" name="effective_date" value="<?php echo format_date(time(), 'date_sort'); ?>" /></label>
		</div>
	</form>
	<br />
</div>
<?php } else { ?>
<div id="p_muid_rehire_dialog" title="Rehire Employee" style="display: none;">
	<form class="pf-form" method="post" action="">
		<div class="pf-element pf-heading dialog_title"></div>
		<div class="pf-element">
			<label><span class="pf-label">Effective Date</span>
			<input class="ui-widget-content ui-corner-all" type="text" size="16" name="effective_date" value="<?php echo format_date(time(), 'date_sort'); ?>" /></label>
		</div>
	</form>
	<br />
</div>
<?php } ?>