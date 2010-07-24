<?php
/**
 * Display a form to view schedules for different company divisions/locations.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Actions';
$pines->com_pgrid->load();
$pines->com_jstree->load();
$pines->com_ptags->load();
?>
<style type="text/css" >
	/* <![CDATA[ */
	.p_muid_btn {
		display: inline-block;
		width: 16px;
		height: 16px;
	}
	#p_muid_actions button {
		padding: 0.2em;
	}
	#p_muid_actions button .ui-button-text {
		padding: 0;
	}
	/* ]]> */
</style>
<script type='text/javascript'>
	// <![CDATA[
	<?php if (gatekeeper('com_hrm/editcalendar')) { ?>
	pines(function(){
		var change_counter = 0;
		$("#p_muid_employee").change(function(){
			if (change_counter > 0)
				pines.post("<?php echo addslashes(pines_url('com_hrm', 'editcalendar')); ?>", { "location": '<?php echo addslashes($this->location); ?>', "employee": $(this).val() });
			change_counter++;
		}).change();
	});

	// Change the location / division within the company.
	pines.com_hrm_select_location = function(){
		$.ajax({
			url: "<?php echo addslashes(pines_url('com_hrm', 'locationselect')); ?>",
			type: "POST",
			dataType: "html",
			data: {"location": "<?php echo addslashes($this->location); ?>"},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retreive the company schedule form:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				if (data == "")
					return;
				var form = $("<div title=\"Choose Location\" />");
				form.dialog({
					bgiframe: true,
					autoOpen: true,
					modal: true,
					open: function(){
						form.html(data+"<br />").dialog("option", "position", "center");
					},
					close: function(){
						form.remove();
					},
					buttons: {
						"View Schedule": function(){
							form.dialog('close');
							var schedule_location = form.find(":input[name=location]").val();
							pines.post("<?php echo addslashes(pines_url('com_hrm', 'editcalendar')); ?>", { "location": schedule_location });
						}
					}
				});
			}
		});
	};
	// Create a new event.
	pines.com_hrm_new_event = function(){
		$.ajax({
			url: "<?php echo addslashes(pines_url('com_hrm', 'editevent')); ?>",
			type: "POST",
			dataType: "html",
			data: {"location": "<?php echo addslashes($this->location); ?>"},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retreive the new event form:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				if (data == "")
					return;
				var form = $("<div title=\"Add a New Event\" />");
				form.dialog({
					bgiframe: true,
					autoOpen: true,
					modal: true,
					open: function(){
						form.html(data+"<br />").dialog("option", "position", "center");
					},
					close: function(){
						form.remove();
					},
					buttons: {
						"Add Event": function(){
							form.dialog('close');
							pines.post("<?php echo addslashes(pines_url('com_hrm', 'saveevent')); ?>", {
								employee: form.find(":input[name=employee]").val(),
								event_label: form.find(":input[name=event_label]").val(),
								all_day: !!form.find(":input[name=all_day]").attr('checked'),
								start: form.find(":input[name=start]").val(),
								end: form.find(":input[name=end]").val(),
								time_start: form.find(":input[name=time_start]").val(),
								time_end: form.find(":input[name=time_end]").val(),
								location: form.find(":input[name=location]").val()
							});
						}
					}
				});
			}
		});
	};
	// Edit an existing event.
	pines.com_hrm_edit_event = function(){
		$.ajax({
			url: "<?php echo addslashes(pines_url('com_hrm', 'editevent')); ?>",
			type: "POST",
			dataType: "html",
			data: {id: "<?php echo $this->entity->guid; ?>"},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retreive the new event form:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				if (data == "")
					return;
				var form = $("<div title=\"Editing "+"<?php echo htmlentities($this->entity->label); ?>"+"\" />");
				form.dialog({
					bgiframe: true,
					autoOpen: true,
					modal: true,
					open: function(){
						form.html(data+"<br />").dialog("option", "position", "center");
					},
					close: function(){
						form.remove();
					},
					buttons: {
						"Save Event": function(){
							form.dialog('close');
							pines.post("<?php echo addslashes(pines_url('com_hrm', 'saveevent')); ?>",
							{
								id: form.find(":input[name=id]").val(),
								employee: form.find(":input[name=employee]").val(),
								event_label: form.find(":input[name=event_label]").val(),
								all_day: !!form.find(":input[name=all_day]").attr('checked'),
								start: form.find(":input[name=start]").val(),
								end: form.find(":input[name=end]").val(),
								time_start: form.find(":input[name=time_start]").val(),
								time_end: form.find(":input[name=time_end]").val(),
								location: form.find(":input[name=location]").val(),
								employee_view: <?php echo isset($this->employee) ? 'true' : 'false'; ?>
							});
						}
					}
				});
			}
		});
	};
	// Create an employee work schedule.
	pines.com_hrm_new_schedule = function(){
		<?php if (isset($this->employee)) { ?>
		$.ajax({
			url: "<?php echo addslashes(pines_url('com_hrm', 'editschedule')); ?>",
			type: "POST",
			dataType: "html",
			data: {"employee": "<?php echo addslashes($this->employee->guid); ?>"},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retreive the new event form:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				if (data == "")
					return;
				var form = $("<div title=\"Schedule work for <?php echo $this->employee->name; ?>\" />");
				form.dialog({
					bgiframe: true,
					autoOpen: true,
					modal: true,
					open: function(){
						form.html(data+"<br />").dialog("option", "position", "center");
					},
					close: function(){
						form.remove();
					},
					buttons: {
						"Add to Schedule": function(){
							form.dialog('close');
							pines.post("<?php echo addslashes(pines_url('com_hrm', 'saveschedule')); ?>", {
								employee: form.find(":input[name=employee]").val(),
								all_day: !!form.find(":input[name=all_day]").attr('checked'),
								time_start: form.find(":input[name=time_start]").val(),
								time_end: form.find(":input[name=time_end]").val(),
								dates: form.find(":input[name=dates]").val()
							});
						}
					}
				});
			}
		});
		<?php } else { ?>
		alert('An employee must be selected in order to create a work schedule.');
		<?php } ?>
	};
	// Show and approve the requested time off.
	pines.com_hrm_time_off = function(){
		$.ajax({
			url: "<?php echo addslashes(pines_url('com_hrm', 'timeoff/review')); ?>",
			type: "POST",
			dataType: "html",
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retreive the new event form:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				if (data == "")
					return;
				var form = $("<div title=\"Pending Requests for Time Off\" />");
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
	};
	<?php if (isset($this->entity)) { ?>
	// Edit the event if there is one to be edited.
	pines.com_hrm_edit_event();
	<?php } } if (gatekeeper('com_hrm/clock')) { ?>
	// Request time off.
	pines.com_hrm_time_off_form = function(rto_id){
		$.ajax({
			url: "<?php echo addslashes(pines_url('com_hrm', 'timeoff/request')); ?>",
			type: "POST",
			dataType: "html",
			data: {id: rto_id},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retreive the new event form:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				if (data == "")
					return;
				var form = $("<div title=\"Time Off Request for "+"<?php echo htmlentities($_SESSION['user']->name); ?>"+"\" />");
				form.dialog({
					bgiframe: true,
					autoOpen: true,
					modal: true,
					open: function(){
						form.html(data+"<br />").dialog("option", "position", "center");
					},
					close: function(){
						form.remove();
					},
					buttons: {
						"Submit Request": function(){
							form.dialog('close');
							pines.post("<?php echo addslashes(pines_url('com_hrm', 'timeoff/save')); ?>",
							{
								id: form.find(":input[name=id]").val(),
								employee: form.find(":input[name=employee]").val(),
								reason: form.find(":input[name=reason]").val(),
								all_day: !!form.find(":input[name=all_day]").attr('checked'),
								start: form.find(":input[name=start]").val(),
								end: form.find(":input[name=end]").val(),
								time_start: form.find(":input[name=time_start]").val(),
								time_end: form.find(":input[name=time_end]").val()
							});
						}
					}
				});
			}
		});
	};
	<?php if (isset($this->rto)) { ?>
	// Edit the event if there is one to be edited.
	pines.com_hrm_time_off_form(<?php echo $this->rto->guid; ?>);
	<?php } } ?>
	// ]]>
</script>
<?php if (gatekeeper('com_hrm/editcalendar')) { ?>
<div style="margin-bottom: 1em; text-align: center;" id="p_muid_actions">
	<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_hrm_select_location();" title="Select Location"><span class="p_muid_btn picon picon-applications-internet"></span></button>
	<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_hrm_new_event();" title="New Event"><span class="p_muid_btn picon picon-resource-calendar-insert"></span></button>
	<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_hrm_new_schedule();" title="New Schedule" <?php echo !isset($this->employee) ? 'disabled="disabled"' : '';?>><span class="p_muid_btn picon picon-list-resource-add"></span></button>
	<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_hrm_time_off();" title="Requested Time Off"><span class="p_muid_btn picon picon-view-calendar-upcoming-events"></span></button>
</div>
<div style="margin-bottom: 1em;">
	<select class="ui-widget-content ui-corner-all" id="p_muid_employee" name="employee" style="width: 100%;">
		<option value="all">Entire Staff</option>
		<?php
		// Load employees for this location.
		foreach ($this->employees as $cur_employee) {
			if (!isset($cur_employee->group))
				continue;
			$cur_select = (isset($this->employee->group) && $this->employee->is($cur_employee)) ? 'selected="selected"' : '';
			if ($this->location == $cur_employee->group->guid)
				echo '<option value="'.$cur_employee->guid.'" '.$cur_select.'>'.htmlentities($cur_employee->name).'</option>';
		} ?>
	</select>
</div>
<?php } if (gatekeeper('com_hrm/clock')) { ?>
<div style="text-align: center; font-size: .9em;">
	<button class="ui-state-default ui-corner-all" type="button" style="width: 100%;" onclick="pines.com_hrm_time_off_form();">Request Time Off</button>
</div>
<?php } ?>