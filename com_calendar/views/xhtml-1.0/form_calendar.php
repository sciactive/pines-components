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
	#p_muid_interaction_dialog ul {
		font-size: 0.8em;
		list-style-type: disc;
		margin: 0;
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
	pines.com_calendar_edit_event = function(event_id){
		$.ajax({
			url: "<?php echo addslashes(pines_url('com_calendar', 'editevent')); ?>",
			type: "POST",
			dataType: "html",
			data: {id: event_id},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retreive the event form:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				if (data == "")
					return;
				var form = $("<div title=\"Editing Event ["+event_id+"]\" />");
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
				pines.error("An error occured while trying to retreive the schedule form:\n"+XMLHttpRequest.status+": "+textStatus);
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
	<?php } ?>
	// Edit an appointment.
	pines.com_calendar_edit_appointment = function(appointment_id){
		var interaction_dialog = $("#p_muid_interaction_dialog");

		// Interaction Dialog
		interaction_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 400,
			buttons: {
				"Update": function(){
					var loader;
					$.ajax({
						url: "<?php echo addslashes(pines_url('com_customer', 'interaction/process')); ?>",
						type: "POST",
						dataType: "json",
						data: {
							id: $("#p_muid_interaction_dialog [name=id]").val(),
							status: $("#p_muid_interaction_dialog [name=status]").val(),
							review_comments: $("#p_muid_interaction_dialog [name=review_comments]").val()
						},
						beforeSend: function(){
							loader = $.pnotify({
								pnotify_title: 'Updating',
								pnotify_text: 'Processing customer interaction...',
								pnotify_notice_icon: 'picon picon-throbber',
								pnotify_nonblock: true,
								pnotify_hide: false,
								pnotify_history: false
							});
						},
						complete: function(){
							loader.pnotify_remove();
						},
						error: function(XMLHttpRequest, textStatus){
							pines.error("An error occured:\n"+XMLHttpRequest.status+": "+textStatus);
						},
						success: function(data){
							if (!data) {
								alert("Could not update the interaction. Do you have permission?");
								return;
							} else if (data == 'closed') {
								alert("This interaction is already closed.");
								return;
							}
							alert("Successfully updated the interaction.");
							$("#p_muid_interaction_dialog [name=review_comments]").val('');
							interaction_dialog.dialog("close");
						}
					});
				}
			}
		});
		$.ajax({
			url: "<?php echo addslashes(pines_url('com_customer', 'interaction/info')); ?>",
			type: "POST",
			dataType: "json",
			data: {id: appointment_id},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured:\n"+XMLHttpRequest.status+": "+textStatus);
			},
			success: function(data){
				if (!data) {
					alert("No appointment was found matching the selected item.");
					return;
				}
				$("#p_muid_interaction_dialog [name=id]").val(appointment_id);
				$("#p_muid_interaction_customer").empty().append(data.customer);
				$("#p_muid_interaction_type").empty().append(data.type);
				$("#p_muid_interaction_employee").empty().append(data.employee);
				$("#p_muid_interaction_date").empty().append(data.date);
				$("#p_muid_interaction_comments").empty().append(data.comments);
				$("#p_muid_interaction_notes").empty().append((data.review_comments.length > 0) ? "<li>"+data.review_comments.join("</li><li>")+"</li>" : "");
				$("#p_muid_interaction_dialog [name=status]").val(data.status);

				interaction_dialog.dialog('open');
			}
		});
	};
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
<div id="p_muid_interaction_dialog" title="Process Customer Interaction" style="display: none;">
	<div class="pf-form">
		<div class="pf-element">
			<span class="pf-label">Customer</span>
			<span class="pf-field" id="p_muid_interaction_customer"></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Employee</span>
			<span class="pf-field" id="p_muid_interaction_employee"></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Interaction Type</span>
			<span class="pf-field" id="p_muid_interaction_type"></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Date</span>
			<span class="pf-field" id="p_muid_interaction_date"></span>
		</div>
		<div class="pf-element pf-full-width">
			<span class="pf-label">Comments</span>
			<span class="pf-field pf-full-width" id="p_muid_interaction_comments"></span>
		</div>
		<div class="pf-element pf-full-width">
			<span class="pf-label">Review Comments</span>
			<span class="pf-field pf-full-width">
				<ul id="p_muid_interaction_notes"></ul>
			</span>
		</div>
		<div class="pf-element pf-full-width">
			<textarea class="ui-widget-content ui-corner-all" rows="3" cols="40" name="review_comments"></textarea>
		</div>
		<div class="pf-element">
			<label>
				<span class="pf-label">Status</span>
				<select class="ui-widget-content ui-corner-all" name="status">
					<option value="open">Open</option>
					<option value="closed">Closed</option>
					<option value="canceled">Canceled</option>
				</select>
			</label>
		</div>
		<input type="hidden" name="id" value="" />
	</div>
	<br class="pf-clearing" />
</div>