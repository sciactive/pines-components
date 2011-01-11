<?php
/**
 * Display a form to view schedules for different company divisions/locations.
 *
 * @package Pines
 * @subpackage com_calendar
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
	pines(function(){
		var change_counter = 0;
		$("#p_muid_employee").change(function(){
			if (change_counter > 0)
				pines.post("<?php echo addslashes(pines_url('com_calendar', 'editcalendar')); ?>", { "location": '<?php echo addslashes($this->location->guid); ?>', "employee": $(this).val() });
			change_counter++;
		}).change();
	});

	// Change the location / division within the company.
	pines.com_calendar_select_location = function(){
		var descendents = <?php echo $this->descendents ? 'true' : 'false'; ?>;
		$.ajax({
			url: "<?php echo addslashes(pines_url('com_calendar', 'locationselect')); ?>",
			type: "POST",
			dataType: "html",
			data: {"location": "<?php echo addslashes($this->location->guid); ?>", "descendents": descendents},
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
							var descendents = form.find(":input[name=descendents]").attr('checked');
							pines.post("<?php echo addslashes(pines_url('com_calendar', 'editcalendar')); ?>", {
								"location": schedule_location,
								"descendents": descendents
							});
						}
					}
				});
			}
		});
	};

	<?php if (gatekeeper('com_calendar/editcalendar')) { ?>
	// Create a new event.
	pines.com_calendar_new_event = function(){
		$.ajax({
			url: "<?php echo addslashes(pines_url('com_calendar', 'editevent')); ?>",
			type: "POST",
			dataType: "html",
			data: {"location": "<?php echo addslashes($this->location->guid); ?>"},
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
							pines.post("<?php echo addslashes(pines_url('com_calendar', 'saveevent')); ?>", {
								employee: form.find(":input[name=employee]").val(),
								event_label: form.find(":input[name=event_label]").val(),
								private_event: !!form.find(":input[name=private]").attr('checked'),
								all_day: !!form.find(":input[name=all_day]").attr('checked'),
								start: form.find(":input[name=start]").val(),
								end: form.find(":input[name=end]").val(),
								time_start_hour: form.find(":input[name=time_start_hour]").val(),
								time_start_minute: form.find(":input[name=time_start_minute]").val(),
								time_start_ampm: form.find(":input[name=time_start_ampm]").val(),
								time_end_hour: form.find(":input[name=time_end_hour]").val(),
								time_end_minute: form.find(":input[name=time_end_minute]").val(),
								time_end_ampm: form.find(":input[name=time_end_ampm]").val(),
								location: form.find(":input[name=location]").val()
							});
						}
					}
				});
			}
		});
	};
	// Edit an existing event.
	pines.com_calendar_edit_event = function(){
		$.ajax({
			url: "<?php echo addslashes(pines_url('com_calendar', 'editevent')); ?>",
			type: "POST",
			dataType: "html",
			data: {id: "<?php echo $this->entity->guid; ?>"},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retreive the new event form:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				if (data == "")
					return;
				var form = $("<div title=\"Editing "+"<?php echo htmlspecialchars($this->entity->label); ?>"+"\" />");
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
							pines.post("<?php echo addslashes(pines_url('com_calendar', 'saveevent')); ?>",
							{
								id: form.find(":input[name=id]").val(),
								employee: form.find(":input[name=employee]").val(),
								event_label: form.find(":input[name=event_label]").val(),
								private_event: !!form.find(":input[name=private]").attr('checked'),
								all_day: !!form.find(":input[name=all_day]").attr('checked'),
								start: form.find(":input[name=start]").val(),
								end: form.find(":input[name=end]").val(),
								time_start_hour: form.find(":input[name=time_start_hour]").val(),
								time_start_minute: form.find(":input[name=time_start_minute]").val(),
								time_start_ampm: form.find(":input[name=time_start_ampm]").val(),
								time_end_hour: form.find(":input[name=time_end_hour]").val(),
								time_end_minute: form.find(":input[name=time_end_minute]").val(),
								time_end_ampm: form.find(":input[name=time_end_ampm]").val(),
								location: form.find(":input[name=location]").val(),
								employee_view: <?php echo isset($this->employee) ? 'true' : 'false'; ?>
							});
						}
					}
				});
			}
		});
	};
	// Create a quick work schedule for an entire location.
	pines.com_calendar_quick_schedule = function(){
		$.ajax({
			url: "<?php echo addslashes(pines_url('com_calendar', 'editlineup')); ?>",
			type: "POST",
			dataType: "html",
			data: {"location": "<?php echo addslashes($this->location->guid); ?>"},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retrieve the quick schedule form:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				if (data == "")
					return;
				var form = $("<div title=\"Quick schedule for <?php echo $this->location->name; ?>\" />");
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
							pines.post("<?php echo addslashes(pines_url('com_calendar', 'savelineup')); ?>", {
								location: form.find(":input[name=location]").val(),
								shifts: form.find(":input[name=shifts]").val()
							});
						}
					}
				});
			}
		});
	};
	// Create an employee work schedule.
	pines.com_calendar_new_schedule = function(){
		<?php if (isset($this->employee)) { ?>
		$.ajax({
			url: "<?php echo addslashes(pines_url('com_calendar', 'editschedule')); ?>",
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
							pines.post("<?php echo addslashes(pines_url('com_calendar', 'saveschedule')); ?>", {
								employee: form.find(":input[name=employee]").val(),
								all_day: !!form.find(":input[name=all_day]").attr('checked'),
								time_start_hour: form.find(":input[name=time_start_hour]").val(),
								time_start_minute: form.find(":input[name=time_start_minute]").val(),
								time_start_ampm: form.find(":input[name=time_start_ampm]").val(),
								time_end_hour: form.find(":input[name=time_end_hour]").val(),
								time_end_minute: form.find(":input[name=time_end_minute]").val(),
								time_end_ampm: form.find(":input[name=time_end_ampm]").val(),
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
	<?php if (isset($this->entity)) { ?>
	// Edit the event if there is one to be edited.
	pines.com_calendar_edit_event();
	<?php }
	} ?>
	// ]]>
</script>
<?php if (gatekeeper('com_calendar/editcalendar')) { ?>
<div style="margin-bottom: 1em; text-align: center;" id="p_muid_actions">
	<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_calendar_select_location();" title="Select Location"><span class="p_muid_btn picon picon-applications-internet"></span></button>
	<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_calendar_new_event();" title="New Event"><span class="p_muid_btn picon picon-resource-calendar-insert"></span></button>
	<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_calendar_quick_schedule();" title="Quick Schedule"><span class="p_muid_btn picon picon-view-calendar-workweek"></span></button>
	<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_calendar_new_schedule();" title="Personal Schedule" <?php echo !isset($this->employee) ? 'disabled="disabled"' : '';?>><span class="p_muid_btn picon picon-list-resource-add"></span></button>
</div>
<?php } if (gatekeeper('com_calendar/editcalendar') || gatekeeper('com_calendar/managecalendar')) { ?>
<div style="margin-bottom: 1em;">
	<select class="ui-widget-content ui-corner-all" id="p_muid_employee" name="employee" style="width: 100%;">
		<option value="all"><?php echo $this->location->name; ?></option>
		<?php
		// Load employees for this location.
		foreach ($this->employees as $cur_employee) {
			if (!isset($cur_employee->group))
				continue;
			$cur_select = (isset($this->employee->group) && $this->employee->is($cur_employee)) ? 'selected="selected"' : '';
			if ($this->location->guid == $cur_employee->group->guid)
				echo '<option value="'.$cur_employee->guid.'" '.$cur_select.'>'.htmlspecialchars($cur_employee->name).'</option>';
		} ?>
	</select>
</div>
<?php } ?>