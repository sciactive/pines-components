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
/* @var $pines pines *//* @var $this module */
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
	#p_muid_filter .ui-button-text-only .ui-button-text {
		padding: 0.2em;
	}
	.calendar_form .combobox {
		position: relative;
	}
	.calendar_form .combobox input {
		padding-right: 32px;
	}
	.calendar_form .combobox a {
		display: block;
		position: absolute;
		right: 8px;
		top: 50%;
		margin-top: -8px;
	}
	.ui-autocomplete {
		max-height: 200px;
		overflow-y: auto;
		/* prevent horizontal scrollbar */
		overflow-x: hidden;
		/* add padding to account for vertical scrollbar */
		padding-right: 20px;
	}
	/* IE 6 doesn't support max-height
	 * we use height instead, but this forces the menu to always be this tall
	 */
	* html .ui-autocomplete {
		height: 200px;
	}
	/* ]]> */
</style>
<script type='text/javascript'>
	// <![CDATA[
	pines(function(){
		var change_counter = 0;
		$("#p_muid_employee").change(function(){
			if (change_counter > 0)
				pines.post(<?php echo json_encode(pines_url('com_calendar', 'editcalendar')); ?>, {
					"view_type": <?php echo json_encode($this->view_type); ?>,
					"start": '<?php echo format_date($this->date[0], 'date_short'); ?>',
					"end": '<?php echo format_date($this->date[1], 'date_short'); ?>',
					"location": <?php echo json_encode((string) $this->location->guid); ?>,
					"employee": $(this).val(),
					"descendents": <?php echo $this->descendents ? 'true' : 'false'; ?>,
					"filter": <?php echo json_encode($this->filter); ?>
				});
			change_counter++;
		}).change();

		$(".combobox", ".calendar_form").each(function(){
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

		$("#p_muid_filter").buttonset().delegate("input", "click", function() {
			pines.get(<?php echo json_encode(pines_url('com_calendar', 'editcalendar')); ?>, {
				view_type: <?php echo json_encode($this->view_type); ?>,
				start: '<?php echo format_date($this->date[0], 'date_short'); ?>',
				end: '<?php echo format_date($this->date[1], 'date_short'); ?>',
				location: <?php echo (int) $this->location->guid; ?>,
				employee: <?php echo isset($this->employee) ? (int) $this->employee->guid : 'null'; ?>,
				descendents: <?php echo $this->descendents ? 'true' : 'false'; ?>,
				filter: $(this).val()
			});
		});
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
			url: <?php echo json_encode(pines_url('com_calendar', 'locationselect')); ?>,
			type: "POST",
			dataType: "html",
			data: {
				"location": <?php echo json_encode((string) $this->location->guid); ?>,
				"descendents": descendents
			},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retrieve the company schedule form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
			},
			success: function(data){
				if (data == "")
					return;
				pines.pause();
				var form = $("<div title=\"Choose Location\"></div>").html(data+"<br />").dialog({
					bgiframe: true,
					autoOpen: true,
					modal: true,
					close: function(){
						form.remove();
					},
					buttons: {
						"View Schedule": function(){
							form.dialog('close');
							var schedule_location = form.find(":input[name=location]").val();
							descendents = form.find(":input[name=descendents]").attr('checked');
							pines.post(<?php echo json_encode(pines_url('com_calendar', 'editcalendar')); ?>, {
								"view_type": <?php echo json_encode($this->view_type); ?>,
								"start": '<?php echo format_date($this->date[0], 'date_short'); ?>',
								"end": '<?php echo format_date($this->date[1], 'date_short'); ?>',
								"location": schedule_location,
								"descendents": descendents,
								"filter": <?php echo json_encode($this->filter); ?>
							});
						}
					}
				});
				pines.play();
			}
		});
	};

	// Create a new event.
	pines.com_calendar_new_event = function(start, end){
		$.ajax({
			url: <?php echo json_encode(pines_url('com_calendar', 'editevent')); ?>,
			type: "POST",
			dataType: "html",
			data: {
				"location": <?php echo json_encode((string) $this->location->guid); ?>,
				"start": start,
				"end": end
			},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retrieve the new event form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
			},
			success: function(data){
				if (data == "")
					return;
				pines.pause();
				var form = $("<div title=\"Add a New Event\"></div>").html(data+"<br />").dialog({
					bgiframe: true,
					autoOpen: true,
					modal: true,
					width: 550,
					close: function(){
						form.remove();
					},
					buttons: {
						"Add Event": function(){
							form.dialog('close');
							pines.post(<?php echo json_encode(pines_url('com_calendar', 'saveevent')); ?>, {
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
								descendents: <?php echo $this->descendents ? 'true' : 'false'; ?>,
								employee_view: <?php echo isset($this->employee) ? 'true' : 'false'; ?>,
								view_type: <?php echo json_encode($this->view_type); ?>,
								calendar_start: '<?php echo format_date($this->date[0], 'date_short'); ?>',
								calendar_end: '<?php echo format_date($this->date[1], 'date_short'); ?>'
							});
						},
						"Cust. Appt.": function(){
							$("#p_muid_new_interaction [name=interaction_date]").val(form.find(":input[name=start]").val());
							$("#p_muid_new_interaction [name=interaction_time]").val(form.find(":input[name=time_start]").val());
							form.dialog('close');
							pines.com_calendar_new_appointment();
						}
					}
				});
				pines.play();
			}
		});
	};
	// Edit an existing event.
	pines.com_calendar_edit_event = function(event_id){
		$.ajax({
			url: <?php echo json_encode(pines_url('com_calendar', 'editevent')); ?>,
			type: "POST",
			dataType: "html",
			data: {"id": event_id},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retrieve the event form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
			},
			success: function(data){
				if (data == "")
					return;
				pines.pause();
				var form = $("<div title=\"Editing Event ["+pines.safe(event_id)+"]\"></div>").html(data+"<br />").dialog({
					bgiframe: true,
					autoOpen: true,
					modal: true,
					width: 550,
					close: function(){
						form.remove();
						pines.selected_event.removeClass('ui-state-disabled');
					},
					buttons: {
						"Save Event": function(){
							pines.post(<?php echo json_encode(pines_url('com_calendar', 'saveevent')); ?>, {
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
								descendents: <?php echo $this->descendents ? 'true' : 'false'; ?>,
								employee_view: <?php echo isset($this->employee) ? 'true' : 'false'; ?>,
								view_type: <?php echo json_encode($this->view_type); ?>,
								calendar_start: '<?php echo format_date($this->date[0], 'date_short'); ?>',
								calendar_end: '<?php echo format_date($this->date[1], 'date_short'); ?>'
							});
							form.dialog('close');
							pines.selected_event.removeClass('ui-state-disabled');
						},
						"Delete Event": function(){
							if (!confirm("Are you sure you want to delete this event?"))
								return;
							$.ajax({
								url: <?php echo json_encode(pines_url('com_calendar', 'deleteevents')); ?>,
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
				pines.play();
			}
		});
	};
	<?php if (gatekeeper('com_calendar/managecalendar')) { ?>
	// Create a quick work schedule for an entire location.
	pines.com_calendar_quick_schedule = function(){
		$.ajax({
			url: <?php echo json_encode(pines_url('com_calendar', 'editlineup')); ?>,
			type: "POST",
			dataType: "html",
			data: {"location": <?php echo json_encode((string) $this->location->guid); ?>},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retrieve the quick schedule form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
			},
			success: function(data){
				if (data == "")
					return;
				pines.pause();
				var form = $("<div title=\"Quick Schedule for <?php echo htmlspecialchars($this->location->name); ?>\"></div>").html(data+"<br />").dialog({
					bgiframe: true,
					autoOpen: true,
					modal: true,
					close: function(){
						form.remove();
					},
					buttons: {
						"Add to Schedule": function(){
							form.dialog('close');
							pines.post(<?php echo json_encode(pines_url('com_calendar', 'savelineup')); ?>, {
								location: form.find(":input[name=location]").val(),
								shifts: form.find(":input[name=shifts]").val()
							});
						}
					}
				});
				pines.play();
			}
		});
	};
	// Create an employee work schedule.
	pines.com_calendar_new_schedule = function(){
		<?php if (isset($this->employee)) { ?>
		$.ajax({
			url: <?php echo json_encode(pines_url('com_calendar', 'editschedule')); ?>,
			type: "POST",
			dataType: "html",
			data: {"employee": <?php echo json_encode((string) $this->employee->guid); ?>},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured while trying to retrieve the schedule form:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
			},
			success: function(data){
				if (data == "")
					return;
				pines.pause();
				var form = $("<div title=\"Schedule Work for <?php echo htmlspecialchars($this->employee->name); ?>\"></div>").html(data+"<br />").dialog({
					bgiframe: true,
					autoOpen: true,
					modal: true,
					close: function(){
						form.remove();
					},
					buttons: {
						"Add to Schedule": function(){
							form.dialog('close');
							pines.post(<?php echo json_encode(pines_url('com_calendar', 'saveschedule')); ?>, {
								employee: form.find(":input[name=employee]").val(),
								all_day: !!form.find(":input[name=all_day]").attr('checked'),
								time_start: form.find(":input[name=time_start]").val(),
								time_end: form.find(":input[name=time_end]").val(),
								dates: form.find(":input[name=dates]").val()
							});
						}
					}
				});
				pines.play();
				// Have to position again because of datepicker.
				form.dialog("option", "position", "center");
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
			width: 415,
			buttons: {
				"Create": function(){
					var loader;
					$.ajax({
						url: <?php echo json_encode(pines_url('com_customer', 'interaction/add')); ?>,
						type: "POST",
						dataType: "json",
						data: {
							customer: $("#p_muid_new_interaction [name=customer]").val(),
							employee: $("#p_muid_new_interaction [name=employee]").val(),
							date: $("#p_muid_new_interaction [name=interaction_date]").val(),
							time: $("#p_muid_new_interaction [name=interaction_time]").val(),
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
							pines.error("An error occured:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
						},
						success: function(data){
							if (!data) {
								alert("Could not create the appointment.");
								return;
							} else if (data == 'conflict') {
								alert("This customer already has an appointment during this timeslot.");
								return;
							}
							alert("Successfully created the appointment.");
							$("#p_muid_new_interaction [name=customer]").val('');
							$("#p_muid_new_interaction [name=interaction_comments]").val('');
							interaction_dialog.dialog("close");
							window.location.reload();
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
						url: <?php echo json_encode(pines_url('com_customer', 'interaction/process')); ?>,
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
							pines.error("An error occured:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
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
			url: <?php echo json_encode(pines_url('com_customer', 'interaction/info')); ?>,
			type: "POST",
			dataType: "json",
			data: {id: appointment_id},
			error: function(XMLHttpRequest, textStatus){
				pines.error("An error occured:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
			},
			success: function(data){
				if (!data) {
					alert("No appointment was found matching the selected item.");
					return;
				}
				$("#p_muid_interaction_dialog [name=id]").val(appointment_id);
				$("#p_muid_interaction_customer").empty().append('<a href="'+pines.safe(data.customer_url)+'" onclick="window.open(this.href); return false;">'+pines.safe(data.customer)+'</a>');
				if (data.sale_url != '') {
					$("#p_muid_interaction_sale").empty().append('<a href="'+pines.safe(data.sale_url)+'" onclick="window.open(this.href); return false;">'+pines.safe(data.sale)+'</a>');
					$("#p_muid_sale_info").show();
				} else {
					$("#p_muid_sale_info").hide();
				}
				$("#p_muid_interaction_type").empty().append(pines.safe(data.type)+' - '+pines.safe(data.contact_info));
				$("#p_muid_interaction_employee").empty().append(pines.safe(data.employee));
				$("#p_muid_interaction_created_date").empty().append(pines.safe(data.created_date));
				$("#p_muid_interaction_date").empty().append(pines.safe(data.date));
				$("#p_muid_interaction_comments").empty().append(pines.safe(data.comments));
				$("#p_muid_interaction_notes").empty().append((data.review_comments.length > 0) ? "<li>"+$.map(data.review_comments, pines.safe).join("</li><li>")+"</li>" : "");
				$("#p_muid_interaction_dialog [name=status]").val(data.status);

				interaction_dialog.dialog('open');
				$("#p_muid_interaction_dialog [name=review_comments]").focus();
			}
		});
	};
	<?php } ?>
	// ]]>
</script>
<div id="p_muid_filter" style="padding: 0; margin: 0;">
	<input type="radio" name="filter" id="p_muid_filter_rad1" value="all" <?php echo ($this->filter == 'all') ? 'checked="checked"' : ''; ?>/><label for="p_muid_filter_rad1" title="Show all calendar events.">all</label>
	<input type="radio" name="filter" id="p_muid_filter_rad2" value="shifts" <?php echo ($this->filter == 'shifts') ? 'checked="checked"' : ''; ?>/><label for="p_muid_filter_rad2" title="Show scheduled employee shifts.">shifts</label>
	<input type="radio" name="filter" id="p_muid_filter_rad3" value="appointments" <?php echo ($this->filter == 'appointments') ? 'checked="checked"' : ''; ?>/><label for="p_muid_filter_rad3" title="Show customer appointments.">appts</label>
	<input type="radio" name="filter" id="p_muid_filter_rad4" value="events" <?php echo ($this->filter == 'events') ? 'checked="checked"' : ''; ?>/><label for="p_muid_filter_rad4" title="Show calendar events.">events</label>
</div>
<?php if (gatekeeper('com_calendar/managecalendar')) { ?>
<div style="margin: 0.75em 0; text-align: center;" id="p_muid_actions">
	<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_calendar_select_location();" title="Select Location"><span class="p_muid_btn picon picon-applications-internet"></span></button>
	<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_calendar_new_appointment();" title="New Appointment"><span class="p_muid_btn picon picon-appointment-new"></span></button>
	<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_calendar_new_event();" title="New Event"><span class="p_muid_btn picon picon-resource-calendar-insert"></span></button>
	<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_calendar_quick_schedule();" title="Quick Schedule"><span class="p_muid_btn picon picon-view-calendar-workweek"></span></button>
	<button class="ui-state-default ui-corner-all" type="button" onclick="pines.com_calendar_new_schedule();" title="Personal Schedule" <?php echo !isset($this->employee) ? 'disabled="disabled"' : '';?>><span class="p_muid_btn picon picon-list-resource-add"></span></button>
</div>
<div>
	<select class="ui-widget-content ui-corner-all" id="p_muid_employee" name="employee" style="width: 100%;">
		<option value="all"><?php echo htmlspecialchars($this->location->name); ?></option>
		<?php
		// Load employees for this location.
		foreach ($this->employees as $cur_employee) {
			if (!isset($cur_employee->group))
				continue;
			$cur_select = (isset($this->employee->group) && $this->employee->is($cur_employee)) ? 'selected="selected"' : '';
			if ( $this->location->guid == $cur_employee->group->guid || ($this->descendents && $cur_employee->is_descendent($this->location)) )
				echo '<option value="'.((int) $cur_employee->guid).'" '.$cur_select.'>'.htmlspecialchars($cur_employee->name).'</option>';
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
		<div class="pf-element" id="p_muid_sale_info" style="display: none">
			<span class="pf-label">Sale</span>
			<span class="pf-field" id="p_muid_interaction_sale"></span>
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
			<span class="pf-label">Created</span>
			<span class="pf-field" id="p_muid_interaction_created_date"></span>
		</div>
		<div class="pf-element">
			<span class="pf-label">Date</span>
			<span class="pf-field" id="p_muid_interaction_date"></span>
		</div>
		<div class="pf-element pf-full-width">
			<span class="pf-full-width" id="p_muid_interaction_comments"></span>
			<div class="pf-full-width">
				<ul id="p_muid_interaction_notes"></ul>
			</div>
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
	<div class="pf-form calendar_form">
		<div class="pf-element">
			<label><span class="pf-label">Customer</span>
				<input class="ui-widget-content ui-corner-all" type="text" id="p_muid_customer" name="customer" size="22" value="" /></label>
		</div>
		<?php if (gatekeeper('com_customer/manageinteractions')) { ?>
		<div class="pf-element">
			<label><span class="pf-label">Employee</span>
				<select class="ui-widget-content ui-corner-all" name="employee">
				<?php foreach ($this->employees as $cur_employee) {
					$selected = $_SESSION['user']->is($cur_employee) ? ' selected="selected"' : '';
					if ($cur_employee->in_group($this->location) || ($this->descendents && $cur_employee->is_descendent($this->location)))
						echo '<option value="'.((int) $cur_employee->guid).'"'.$selected.'>'.htmlspecialchars($cur_employee->name).'</option>"';
				} ?>
			</select></label>
		</div>
		<?php } else { ?>
		<div class="pf-element">
			<label><span class="pf-label">Employee</span>
				<?php echo htmlspecialchars($_SESSION['user']->name); ?></label>
		</div>
		<input type="hidden" name="employee" value="<?php echo (int) $_SESSION['user']->guid; ?>" />
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
			<span class="pf-label">Time</span>
			<span class="combobox">
				<input class="ui-widget-content ui-corner-all" type="text" name="interaction_time" size="20" value="" />
				<a href="javascript:void(0);" class="ui-icon ui-icon-triangle-1-s"></a>
				<select style="display: none;">
					<option value="12:00 AM">12:00 AM</option>
					<option value="1:00 AM">1:00 AM</option>
					<option value="2:00 AM">2:00 AM</option>
					<option value="3:00 AM">3:00 AM</option>
					<option value="4:00 AM">4:00 AM</option>
					<option value="5:00 AM">5:00 AM</option>
					<option value="6:00 AM">6:00 AM</option>
					<option value="7:00 AM">7:00 AM</option>
					<option value="8:00 AM">8:00 AM</option>
					<option value="9:00 AM">9:00 AM</option>
					<option value="10:00 AM">10:00 AM</option>
					<option value="11:00 AM">11:00 AM</option>
					<option value="12:00 PM">12:00 PM</option>
					<option value="1:00 PM">1:00 PM</option>
					<option value="2:00 PM">2:00 PM</option>
					<option value="3:00 PM">3:00 PM</option>
					<option value="4:00 PM">4:00 PM</option>
					<option value="5:00 PM">5:00 PM</option>
					<option value="6:00 PM">6:00 PM</option>
					<option value="7:00 PM">7:00 PM</option>
					<option value="8:00 PM">8:00 PM</option>
					<option value="9:00 PM">9:00 PM</option>
					<option value="10:00 PM">10:00 PM</option>
					<option value="11:00 PM">11:00 PM</option>
				</select>
			</span>
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