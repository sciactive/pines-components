<?php
/**
 * Lists all calendar events and allows users to manipulate them.
 *
 * Built upon:
 * FullCalendar Created by Adam Shaw
 * http://arshaw.com/fullcalendar/
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
$this->title = 'Company Schedule [' . (isset($this->employee) ? $this->employee->name  : $this->location->name) . ']';
$timezone = $_SESSION['user']->get_timezone();
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_form .helper {
		background-position: left;
		background-repeat: no-repeat;
		padding-left: 16px;
		display: none;
	}
	/* ]]> */
</style>
<script type='text/javascript'>
	// <![CDATA[
	pines(function(){
		var view_changes = 0;
		pines.selected_event = '';
		var help = $.pnotify({
			pnotify_title: "Information",
			pnotify_text: "",
			pnotify_hide: false,
			pnotify_closer: false,
			pnotify_history: false,
			pnotify_animation: "none",
			pnotify_animate_speed: 0,
			pnotify_opacity: 1,
			pnotify_notice_icon: "",
			// Setting stack to false causes Pines Notify to ignore this notice when positioning.
			pnotify_stack: false,
			pnotify_after_init: function(pnotify){
				// Remove the notice if the user mouses over it.
				pnotify.mouseout(function(){
					pnotify.pnotify_remove();
				});
			},
			pnotify_before_open: function(pnotify){
				// This prevents the notice from displaying when it's created.
				pnotify.pnotify({
					pnotify_before_open: null
				});
				return false;
			}
		});

		// Create the calendar object.
		$('#p_muid_calendar').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			dragOpacity: {
				agenda: .5,
				'': 0.85
			},
			defaultView: <?php echo json_encode($this->view_type); ?>,
			firstDay: 1,
			selectable: true,
			theme: true,
			ignoreTimezone: false,
			editable: <?php echo gatekeeper('com_calendar/managecalendar') ? 'true' : 'false'; ?>,
			events: [<?php
				// Read in all existing events.
				$event_counter = 0;
				foreach ($this->events as $cur_event) {
					if (!gatekeeper('com_calendar/managecalendar') && $cur_event->private) {
						if (!isset($cur_event->employee->guid) && !$cur_event->group->is($this->location))
							continue;
						if (isset($cur_event->employee->guid) && !$cur_event->employee->is($_SESSION['user']))
							continue;
					}
					if (!isset($cur_event->user->guid))
						continue;
					if ($event_counter > 0)
						echo ',';
					$cur_start = format_date($cur_event->start, 'custom', 'G');
					if (!$cur_event->all_day && ($cur_start < $min_start || !isset($min_start)))
						$min_start = $cur_start;
					echo '{';
					if ($cur_event->event_id != 0) {
						echo 'group: true,';
						echo 'id: '. $cur_event->event_id .', ';
						echo '_id: '. $cur_event->event_id .', ';
						echo 'guid: '. $cur_event->guid .', ';
					} else {
						echo 'group: false,';
						echo 'id: '. $cur_event->guid .', ';
						echo '_id: '. $cur_event->guid .', ';
					}
					echo 'title: \''. addslashes($cur_event->title) .'\', ';
					echo 'start: \''. format_date($cur_event->start, 'custom', 'Y-m-d H:i', $timezone) .'\', ';
					echo 'end: \''. format_date($cur_event->end, 'custom', 'Y-m-d H:i', $timezone) .'\', ';
					if ((!gatekeeper('com_calendar/managecalendar') && (!$cur_event->user->is($_SESSION['user']) || $cur_event->appointment)) || $cur_event->time_off) {
						echo 'editable: false,';
					} else {
						echo 'editable: true,';
					}
					if (isset($cur_event->appointment->guid)) {
						echo 'appointment: '.$cur_event->appointment->guid.',';
						if ($cur_event->appointment->status == 'open') {
							if ($cur_event->appointment->action_date < strtotime('-3 days'))
								echo 'className: \'red\',';
							elseif ($cur_event->appointment->action_date < strtotime('-1 hour'))
								echo 'className: \'yellow\',';
							else
								echo 'className: \'greenyellow\',';
						} else {
							echo 'className: \''. addslashes($cur_event->color) .'\',';
						}
					} else {
						echo 'appointment: \'\',';
						echo 'className: \''. addslashes($cur_event->color) .'\',';
					}
					echo ($cur_event->all_day) ? 'allDay: true,' : 'allDay: false,';
					echo (!empty($cur_event->information)) ? 'info: '.json_encode($cur_event->information) : 'info: \'\'';
					echo '}';
					$event_counter++;
				} ?>],
			firstHour: <?php echo isset($min_start) ? $min_start : 8; ?>,
			minTime: <?php echo isset($min_start) ? $min_start : 8; ?>,
			select: function(start, end, allDay, jsEvent, view) {
				pines.com_calendar_new_event(start.toString(), end.toString());
			},
			eventClick: function(event,jsEvent,view) {
				if (event.editable == false && event.appointment == '') {
					alert(event.title+' is not editable.');
					return;
				}
				if (event.appointment != '')
					pines.com_calendar_edit_appointment(event.appointment);
				else
					pines.com_calendar_edit_event(event.id);
				pines.selected_event = $(this);
				pines.selected_event.addClass('ui-state-disabled');

			},
			eventDrop: function(event,dayDelta,minuteDelta,allDay,revertFunc) {
				event.selected = false;
				$("#p_muid_calendar").fullCalendar('refetchEvents');
				pines.com_calendar_save_calendar();
			},
			eventDragStop: function(event, jsEvent, ui, view) {
				var events = $("#p_muid_calendar").fullCalendar('clientEvents');
				$.each(events, function(i, val) {
					val.selected = false;
				});
				$("#p_muid_calendar").fullCalendar('refetchEvents');
			},
			eventResize: function(event,dayDelta,minuteDelta,revertFunc,jsEvent,ui,view) {
				event.selected = false;
				pines.com_calendar_save_calendar();
			},
			eventMouseover: function(event,jsEvent,view) {
				help.pnotify({ pnotify_title: event.title, pnotify_text: event.info });
				help.pnotify_display();
			},
			eventMouseout: function(event,jsEvent,view) {
				help.pnotify_remove(); help.pnotify({ pnotify_text: "" });
			},
			viewDisplay: function(view) {
				// The first couple of times this fires it is loading the initial calendar.
				if (view_changes < 2) {
					view_changes++;
				} else {
					alert('Loading Relevant Events');
					pines.get("<?php echo addslashes(pines_url('com_calendar', 'editcalendar')); ?>", {
						view_type: view.name,
						start: view.start.toString().replace(/[A-Za-z]+\s([A-Za-z\s\d]+)\s\d{2}\:.*/, '$1'),
						end: view.end.toString().replace(/[A-Za-z]+\s([A-Za-z\s\d]+)\s\d{2}\:.*/, '$1'),
						location: "<?php echo $this->location->guid; ?>",
						employee: "<?php echo $this->employee->guid; ?>",
						descendents: <?php echo $this->descendents ? 'true' : 'false'; ?>,
						filter: <?php echo json_encode($this->filter); ?>
					});
				}
			}
		});
		var current_date = $.fullCalendar.parseDate(<?php echo (int) $this->date[0]; ?>);
		$('#p_muid_calendar').fullCalendar('gotoDate', current_date);
	});
	// Add new events to the calendar, mostly for duplicating events.
	pines.com_calendar_add_events = function(events) {
		$.ajax({
			url: "<?php echo addslashes(pines_url('com_calendar', 'addevents')); ?>",
			type: "POST",
			dataType: "html",
			data: {"events": events},
			error: function(){
				pines.error("An error occured while trying to add events to the calendar.");
			},
			success: function(){
				pines.get("<?php echo addslashes(pines_url('com_calendar', 'editcalendar',
					array(
						'view_type' => $this->view_type,
						'start' => format_date($this->date[0], 'date_short'),
						'end' => format_date($this->date[1], 'date_short'),
						'location' => $this->location->guid,
						'employee' => $this->employee->guid,
						'descendents' => $this->descendents,
						'filter' => $this->filter
					)
				)); ?>");
			}
		});
	};

	// Save all of the calendar events by exporting the data to their entities.
	pines.com_calendar_save_calendar = function(refresh) {
		var events = $("#p_muid_calendar").fullCalendar('clientEvents');
		var events_dump = '';
		//var events_array = new Array();
		//var event_count = 0;
		$.each(events, function(i, val) {
			if (val.group) {
				events_dump += val.guid.toString() +'|'+ val.id.toString() +'|';
				//events_array[0] = val.guid.toString();
				//events_array[1] = val.id.toString();
			} else {
				events_dump += val.id.toString() + '|0|';
				//events_array[0] = val.id.toString();
				//events_array[1] = '0';
			}
			var event_start = val.start.toString().replace(/[A-Za-z]+\s([A-Za-z\s\d\:]+)\s.*/, '$1').replace(',', '');
			var event_end = val.end.toString().replace(/[A-Za-z]+\s([A-Za-z\s\d\:]+)\s.*/, '$1').replace(',', '');
			events_dump += event_start + '|' + event_end + '|' + val.allDay + ',';
			//events_array[2] = event_start;
			//events_array[3] = event_end;
			//events_array[4] = val.allDay;
			//events_dump[event_count] = events_array;
			//event_count++;
		});
		$.ajax({
			url: "<?php echo addslashes(pines_url('com_calendar', 'savecalendar')); ?>",
			type: "POST",
			dataType: "html",
			data: {"events": events_dump},
			error: function(){
				pines.error("An error occured while trying to save the calendar.");
			},
			success: function(data){
				if (data)
					alert(data);
				if (refresh || data) {
					pines.get('<?php echo addslashes(pines_url('com_calendar', 'editcalendar',
						array(
							'view_type' => $this->view_type,
							'start' => $this->start,
							'end' => $this->end,
							'location' => $this->location->guid,
							'employee' => $this->employee->guid,
							'descendents' => $this->descendents,
							'filter' => $this->filter
						)
					)); ?>');
				}
			}
		});
	};

	// Help
	pines.com_calendar_calendar_help = function(){
		alert('Click on an event to select/deselect it.');
	};

	// Duplicate Event(s)
	pines.com_calendar_copy_event = function() {
		var events = $("#p_muid_calendar").fullCalendar('clientEvents');
		var copy_events = new Array();
		var copy_count = 0;
		// Find the selected event(s).
		$.each(events, function(i, val) {
			if (val.selected && val.editable == false) {
				alert(val.title+' cannot be copied.');
			} else if (val.selected) {
				if (val.group)
					copy_events[copy_count] = val.guid;
				else
					copy_events[copy_count] = val.id;
				copy_count++;
			}
		});
		if (copy_count == 0) {
			alert('Please select at least one event to duplicate.');
		} else {
			pines.com_calendar_add_events(copy_events);
		}
	};

	// Delete Event(s)
	pines.com_calendar_delete_events = function() {
		var events = $("#p_muid_calendar").fullCalendar('clientEvents');
		var remove_events = new Array();
		var event_guids = new Array();
		var remove_count = 0;
		// Find the selected event(s).
		$.each(events, function(i, val) {
			if (val.selected && val.editable == false) {
				alert(val.title+' cannot be deleted.');
			} else if (val.selected && val.group) {
				if (remove_events[remove_count-1] != val.id &&
					confirm(val.title + ' is a linked event, deleting it will remove the entire group.')) {
					event_guids.push(val.guid);
					remove_events.push(val.id);
					remove_count++;
				}
			} else if (val.selected && !val.group) {
				event_guids.push(val.id);
				remove_events.push(val.id);
				remove_count++;
			}
		});
		if (remove_count == 0) {
			alert('Please select at least one event to delete.');
		} else {
			$.ajax({
				url: "<?php echo addslashes(pines_url('com_calendar', 'deleteevents')); ?>",
				type: "POST",
				dataType: "json",
				data: {"events": event_guids},
				error: function(){
					pines.error("An error occured while trying to delete events from the calendar.");
				},
				success: function(data) {
					$.each(remove_events, function(r, remove_event) {
						if (data && data.indexOf(remove_event) != -1)
							return;
						$("#p_muid_calendar").fullCalendar('removeEvents', remove_event);
					});
					if (data)
						pines.error('Some events could not be deleted.');
					else
						alert('Deleted Event(s).');
				}
			});
		}
	};

	// Clear Calendar
	pines.com_calendar_clear_calendar = function() {
		if (confirm('Clear the entire calendar? This will remove all events for this location/employee.')) {

			var events = $("#p_muid_calendar").fullCalendar('clientEvents');
			var event_guids = new Array();
			// Find the selected event(s).
			$.each(events, function(i, val) {
				if (val.group)
					event_guids.push(val.guid);
				else if (!val.group)
					event_guids.push(val.id);
			});

			$.ajax({
				url: "<?php echo addslashes(pines_url('com_calendar', 'deleteevents')); ?>",
				type: "POST",
				dataType: "json",
				data: {"events": event_guids},
				error: function(){
					pines.error("An error occured while trying to delete events from the calendar.");
				},
				success: function(data) {
					$("#p_muid_calendar").fullCalendar('removeEvents');
					if (data)
						pines.error('Some events could not be deleted.');
					else
						alert('Cleared the calendar.');
				}
			});
			pines.com_calendar_save_calendar();
		}
	};

	// Unlink Event(s)
	pines.com_calendar_unlink_events = function() {
		var events = $("#p_muid_calendar").fullCalendar('clientEvents');
		var unlink_count = 0;
		// Find the selected event(s).
		$.each(events, function(i, val) {
			if (val.selected == true && val.group) {
				val.group = false;
				val.id = val.guid;
				unlink_count++;
			}
		});
		if (unlink_count == 0)
			alert('Please select at least one bound event to unlink.');
		else
			pines.com_calendar_save_calendar(true);
	};
	// ]]>
</script>
<div id="p_muid_calendar"></div>