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
if ($pines->config->com_calendar->com_customer)
	$pines->com_customer->load_customer_select();
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
		padding-left: 10px;
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
		<?php if ($pines->config->com_calendar->com_customer) { ?>
		$("#p_muid_new_interaction [name=interaction_date]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});

		$("#p_muid_customer").customerselect();
		<?php } ?>
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

	// Create a new event.
	pines.com_calendar_new_event = function(start, end){
		$.ajax({
			url: "<?php echo addslashes(pines_url('com_calendar', 'editevent')); ?>",
			type: "POST",
			dataType: "html",
			data: {
				"location": "<?php echo addslashes($this->location->guid); ?>",
				"start": start,
				"end": end
			},
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
					width: 550,
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
								information: form.find(":input[name=information]").val(),
								private_event: !!form.find(":input[name=private]").attr('checked'),
								all_day: !!form.find(":input[name=all_day]").attr('checked'),
								start: form.find(":input[name=start]").val(),
								end: form.find(":input[name=end]").val(),
								time_start: form.find(":input[name=time_start]").val(),
								time_end: form.find(":input[name=time_end]").val(),
								location: form.find(":input[name=location]").val()
							});
						},
						"✪": function(){
							form.dialog('close');
							pines.com_calendar_new_appointment();
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
					width: 550,
					open: function(){
						form.html(data+"<br />").dialog("option", "position", "center");
					},
					close: function(){
						form.remove();
						pines.selected_event.removeClass('ui-state-disabled');
					},
					buttons: {
						"Save Event": function(){
							pines.post("<?php echo addslashes(pines_url('com_calendar', 'saveevent')); ?>",
							{
								id: form.find(":input[name=id]").val(),
								employee: form.find(":input[name=employee]").val(),
								event_label: form.find(":input[name=event_label]").val(),
								information: form.find(":input[name=information]").val(),
								private_event: !!form.find(":input[name=private]").attr('checked'),
								all_day: !!form.find(":input[name=all_day]").attr('checked'),
								start: form.find(":input[name=start]").val(),
								end: form.find(":input[name=end]").val(),
								time_start: form.find(":input[name=time_start]").val(),
								time_end: form.find(":input[name=time_end]").val(),
								location: form.find(":input[name=location]").val(),
								employee_view: <?php echo isset($this->employee) ? 'true' : 'false'; ?>
							});
							form.dialog('close');
							pines.selected_event.removeClass('ui-state-disabled');
						},
						"Delete Event": function(){
							if (!confirm("Are you sure you want to delete this event?"))
								return;
							$.ajax({
								url: "<?php echo addslashes(pines_url('com_calendar', 'deleteevents')); ?>",
								type: "POST",
								dataType: "json",
								data: {"events": Array(form.find(":input[name=id]").val())},
								error: function(){
									pines.error("An error occured while trying to delete the event.");
								},
								success: function(data) {
									alert('Deleted event ['+form.find(":input[name=event_label]").val()+'].');
									$("#calendar").fullCalendar('removeEvents', form.find(":input[name=id]").val());
									form.dialog('close');
									pines.selected_event.removeClass('ui-state-disabled');
								}
							});
						}
					}
				});
			}
		});
	};
	<?php if (gatekeeper('com_calendar/editcalendar')) { ?>
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
	<?php } ?>
	<?php if ($pines->config->com_calendar->com_customer) { ?>
	// Create an appointment.
	pines.com_calendar_new_appointment = function(){
		var interaction_dialog = $("#p_muid_new_interaction");

		// Interaction Dialog
		interaction_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 402,
			buttons: {
				"Create": function(){
					var loader;
					$.ajax({
						url: "<?php echo addslashes(pines_url('com_customer', 'interaction/add')); ?>",
						type: "POST",
						dataType: "json",
						data: {
							customer: $("#p_muid_new_interaction [name=customer]").val(),
							employee: $("#p_muid_new_interaction [name=employee]").val(),
							date: $("#p_muid_new_interaction [name=interaction_date]").val(),
							time_ampm: $("#p_muid_new_interaction [name=interaction_ampm]").val(),
							time_hour: $("#p_muid_new_interaction [name=interaction_hour]").val(),
							time_minute: $("#p_muid_new_interaction [name=interaction_minute]").val(),
							type: $("#p_muid_new_interaction [name=interaction_type]").val(),
							status: $("#p_muid_new_interaction [name=interaction_status]").val(),
							comments: $("#p_muid_new_interaction [name=interaction_comments]").val()
						},
						beforeSend: function(){
							loader = $.pnotify({
								pnotify_title: 'Saving',
								pnotify_text: 'Creating customer interaction...',
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
								alert("Could not create the appointment.");
								return;
							}
							alert("Successfully created the appointment.");
							$("#p_muid_new_interaction [name=customer]").val('');
							$("#p_muid_new_interaction [name=interaction_comments]").val('');
							interaction_dialog.dialog("close");
						}
					});
				}
			}
		});

		interaction_dialog.dialog('open');
	};

	// Edit an appointment.
	pines.com_calendar_edit_appointment = function(appointment_id){
		var interaction_dialog = $("#p_muid_interaction_dialog");

		// Interaction Dialog
		interaction_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 400,
			close: function(){
				pines.selected_event.removeClass('ui-state-disabled');
			},
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
							} else if (data == 'comments') {
								alert("You must provide information in the comments section.");
								return;
							}
							alert("Successfully updated the interaction.");
							$("#p_muid_interaction_dialog [name=review_comments]").val('');
							interaction_dialog.dialog("close");
							pines.selected_event.removeClass('ui-state-disabled');
							window.location.reload();
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
				$("#p_muid_interaction_customer").empty().append('<a href="'+data.customer_url+'" onclick="window.open(this.href); return false;">'+data.customer+'</a>');
				$("#p_muid_interaction_type").empty().append(data.type+' - '+data.contact_info);
				$("#p_muid_interaction_employee").empty().append(data.employee);
				$("#p_muid_interaction_date").empty().append(data.date);
				$("#p_muid_interaction_comments").empty().append(data.comments);
				$("#p_muid_interaction_notes").empty().append((data.review_comments.length > 0) ? "<li>"+data.review_comments.join("</li><li>")+"</li>" : "");
				$("#p_muid_interaction_dialog [name=status]").val(data.status);

				interaction_dialog.dialog('open');
				$("#p_muid_interaction_dialog [name=review_comments]").focus();
			}
		});
	};
	<?php } ?>
	// ]]>
</script>
<?php if (gatekeeper('com_calendar/editcalendar')) { ?>
<div style="margin-bottom: 1em; text-align: center;" id="p_muid_actions">
	<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_calendar_select_location();" title="Select Location"><span class="p_muid_btn picon picon-applications-internet"></span></button>
	<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_calendar_new_appointment();" title="New Appointment"><span class="p_muid_btn picon picon-appointment-new"></span></button>
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
<?php } if ($pines->config->com_calendar->com_customer) { ?>
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
			<span class="pf-full-width" id="p_muid_interaction_comments"></span>
			<span class="pf-full-width">
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
<div id="p_muid_new_interaction" title="Create a Customer Appointment" style="display: none;">
	<div class="pf-form">
		<div class="pf-element">
			<label><span class="pf-label">Customer</span>
				<input class="ui-widget-content ui-corner-all" type="text" id="p_muid_customer" name="customer" size="22" value="" /></label>
		</div>
		<?php if (gatekeeper('com_customer/manageinteractions')) { ?>
		<div class="pf-element">
			<label><span class="pf-label">Employee</span>
				<select class="ui-widget-content ui-corner-all" name="employee">
				<?php foreach ($pines->com_hrm->get_employees() as $cur_employee) {
					$selected = $_SESSION['user']->is($cur_employee) ? ' selected="selected"' : '';
					echo '<option value="'.$cur_employee->guid.'"'.$selected.'>'.htmlspecialchars($cur_employee->name).'</option>"';
				} ?>
			</select></label>
		</div>
		<?php } else { ?>
		<div class="pf-element">
			<label><span class="pf-label">Employee</span>
				<?php echo htmlspecialchars($_SESSION['user']->name); ?></label>
		</div>
		<input type="hidden" name="employee" value="<?php echo $_SESSION['user']->guid; ?>" />
		<?php } ?>
		<div class="pf-element">
			<label><span class="pf-label">Interaction Type</span>
				<select class="ui-widget-content ui-corner-all" name="interaction_type">
					<?php foreach ($pines->config->com_customer->interaction_types as $cur_type) {
						$cur_type = explode(':', $cur_type);
						echo '<option value="'.htmlspecialchars($cur_type[1]).'">'.htmlspecialchars($cur_type[1]).'</option>';
					} ?>
				</select></label>
		</div>
		<div class="pf-element">
			<label><span class="pf-label">Date</span>
				<input class="ui-widget-content ui-corner-all" type="text" size="22" name="interaction_date" value="<?php echo format_date(time(), 'date_sort'); ?>" /></label>
		</div>
		<div class="pf-element pf-full-width">
			<?php
			$time_hour = format_date(time(), 'custom', 'H');
			$time_minute = format_date(time(), 'custom', 'i');
			?>
			<span class="pf-label">Time</span>
			<select class="ui-widget-content ui-corner-all" name="interaction_hour">
				<option value="1" <?php echo ($time_hour == '1' || $time_hour == '13') ? 'selected="selected"' : ''; ?>>1</option>
				<option value="2" <?php echo ($time_hour == '2' || $time_hour == '14') ? 'selected="selected"' : ''; ?>>2</option>
				<option value="3" <?php echo ($time_hour == '3' || $time_hour == '15') ? 'selected="selected"' : ''; ?>>3</option>
				<option value="4" <?php echo ($time_hour == '4' || $time_hour == '16') ? 'selected="selected"' : ''; ?>>4</option>
				<option value="5" <?php echo ($time_hour == '5' || $time_hour == '17') ? 'selected="selected"' : ''; ?>>5</option>
				<option value="6" <?php echo ($time_hour == '6' || $time_hour == '18') ? 'selected="selected"' : ''; ?>>6</option>
				<option value="7" <?php echo ($time_hour == '7' || $time_hour == '19') ? 'selected="selected"' : ''; ?>>7</option>
				<option value="8" <?php echo ($time_hour == '8' || $time_hour == '20') ? 'selected="selected"' : ''; ?>>8</option>
				<option value="9" <?php echo ($time_hour == '9' || $time_hour == '21') ? 'selected="selected"' : ''; ?>>9</option>
				<option value="10" <?php echo ($time_hour == '10' || $time_hour == '22') ? 'selected="selected"' : ''; ?>>10</option>
				<option value="11" <?php echo ($time_hour == '11' || $time_hour == '23') ? 'selected="selected"' : ''; ?>>11</option>
				<option value="0" <?php echo ($time_hour == '0' || $time_hour == '12') ? 'selected="selected"' : ''; ?>>12</option>
			</select> :
			<select class="ui-widget-content ui-corner-all" name="interaction_minute">
				<option value="0" <?php echo ($time_minute >= '0' && $time_minute < '15') ? 'selected="selected"' : ''; ?>>00</option>
				<option value="15" <?php echo ($time_minute >= '15' && $time_minute < '30') ? 'selected="selected"' : ''; ?>>15</option>
				<option value="30" <?php echo ($time_minute >= '30' && $time_minute < '45') ? 'selected="selected"' : ''; ?>>30</option>
				<option value="45" <?php echo ($time_minute >= '45' && $time_minute < '60') ? 'selected="selected"' : ''; ?>>45</option>
			</select>
			<select class="ui-widget-content ui-corner-all" name="interaction_ampm">
				<option value="am" selected="selected">AM</option>
				<option value="pm" <?php echo ($time_hour >= 12) ? 'selected="selected"' : ''; ?>>PM</option>
			</select>
		</div>
		<div class="pf-element">
			<label>
				<span class="pf-label">Status</span>
				<select class="ui-widget-content ui-corner-all" name="interaction_status">
					<option value="open">Open</option>
					<option value="closed">Closed</option>
				</select>
			</label>
		</div>
		<div class="pf-element pf-full-width">
			<textarea class="ui-widget-content ui-corner-all" rows="3" cols="40" name="interaction_comments"></textarea>
		</div>
	</div>
	<br class="pf-clearing" />
</div>
<?php } ?>