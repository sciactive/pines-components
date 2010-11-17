<?php
/**
 * Lists all calendar events and allows users to manipulate them.
 *
 * Built upon:
 *
 * FullCalendar Created by Adam Shaw
 * http://arshaw.com/fullcalendar/
 *
 * Very Simple Context Menu Plugin by Intekhab A Rizvi
 * http://intekhabrizvi.wordpress.com/
 * 
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Company Schedule [' . (isset($this->employee) ? $this->employee->name  : $this->location->name) . ']';
$timezone = $_SESSION['user']->get_timezone();
?>
<script type='text/javascript'>
	// <![CDATA[
	pines(function() {
		// Create the calendar object.
		$('#calendar').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			dragOpacity: {
				agenda: .5,
				'': 0.85
			},
			defaultView: 'agendaWeek',
			firstDay: 1,
			firstHour: 8,
			theme: true,
			ignoreTimezone: false,
			<?php if (gatekeeper('com_hrm/editcalendar')) { ?>
			editable: true,
			<?php } else { ?>
			editable: false,
			<?php } ?>
			events: [<?php
				// Read in all existing events.
				$event_counter = 0;
				foreach ($this->events as $cur_event) {
					if (!gatekeeper('com_hrm/managecalendar') && $cur_event->private) {
						if (!isset($cur_event->employee->guid) && !$cur_event->group->is($this->location))
							continue;
						if (isset($cur_event->employee->guid) && !$cur_event->employee->is($_SESSION['user']))
							continue;
					}

					if ($event_counter > 0)
						echo ',';
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
					echo 'className: \''. addslashes($cur_event->color) .'\',';
					echo ($cur_event->time_off || !gatekeeper('com_hrm/editcalendar')) ? 'editable: false,' : 'editable: true,';
					echo ($cur_event->all_day) ? 'allDay: true' : 'allDay: false';
					echo '}';
					$event_counter++;
				} ?>],
			eventClick: function(calEvent,jsEvent,view) {
				if (calEvent.selected == true) {
					calEvent.selected = false;
					$(this).removeClass('ui-state-disabled');
				} else {
					calEvent.selected = true;
					$(this).addClass('ui-state-disabled');
				}
			},
			eventDrop: function(event,dayDelta,minuteDelta,allDay,revertFunc) {
				event.selected = false;
				$("#calendar").fullCalendar('refetchEvents');
				pines.com_hrm_save_calendar();
			},
			eventDragStop: function( event, jsEvent, ui, view ) {
				var events = $("#calendar").fullCalendar('clientEvents');
				jQuery.each(events, function(i, val) {
					val.selected = false;
				});
				$("#calendar").fullCalendar('refetchEvents');
			},
			eventResize: function(event,dayDelta,minuteDelta,revertFunc,jsEvent,ui,view) {
				event.selected = false;
				pines.com_hrm_save_calendar();
			},
			viewDisplay: function(view) {
				// Deselect all events when changing the calendar timespan.
				var events = $("#calendar").fullCalendar('clientEvents');
				jQuery.each(events, function(i, val) {
					val.selected = false;
				});
				$("#calendar").fullCalendar('refetchEvents');
			}
		});
		<?php if (gatekeeper('com_hrm/editcalendar')) { ?>
			// Right-Click Menu.
			$('#calendar').vscontext({menuBlock: 'vs-context-menu'});
		<?php } ?>
	});
	// Add new events to the calendar, mostly for duplicating events.
	pines.com_hrm_add_events = function(events) {
		$.ajax({
			url: "<?php echo addslashes(pines_url('com_hrm', 'addevents')); ?>",
			type: "POST",
			dataType: "html",
			data: {"events": events},
			error: function(){
				pines.error("An error occured while trying to add events to the calendar.");
			},
			success: function(){
				pines.get("<?php echo addslashes(pines_url('com_hrm', 'editcalendar', array('location' => $this->location->guid, 'employee' => $this->employee->guid))); ?>");
			}
		});
	};

	// Save all of the calendar events by exporting the data to their entities.
	pines.com_hrm_save_calendar = function(refresh) {
		var events = $("#calendar").fullCalendar('clientEvents');
		var events_dump = '';
		//var events_array = new Array();
		//var event_count = 0;
		jQuery.each(events, function(i, val) {
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
			url: "<?php echo addslashes(pines_url('com_hrm', 'savecalendar')); ?>",
			type: "POST",
			dataType: "html",
			data: {"events": events_dump},
			error: function(){
				pines.error("An error occured while trying to save the calendar.");
			},
			success: function(){
				if (refresh)
					pines.get('<?php echo pines_url('com_hrm', 'editcalendar', array('location' => $this->location->guid, 'employee' => $this->employee->guid)); ?>');
			}
		});
	};

	// Help
	pines.com_hrm_calendar_help = function(){
		alert('Click on an event to select/deselect it.');
	};

	// Duplicate Event(s)
	pines.com_hrm_copy_event = function() {
		var events = $("#calendar").fullCalendar('clientEvents');
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
			pines.com_hrm_add_events(copy_events);
		}
	};

	// Edit Event
	pines.com_hrm_edit_event = function() {
		var events = $("#calendar").fullCalendar('clientEvents');
		var edit_event;
		var edit_count = 0;
		// Find the selected event(s).
		$.each(events, function(i, val) {
			if (val.selected) {
				if (val.editable == false)
					alert(val.title+' is not editable.');
				else if (val.group)
					edit_event = val.guid;
				else
					edit_event = val.id;
				if (typeof edit_event != 'undefined')
					edit_count++;
			}
		});
		if (edit_count == 0) {
			alert('Please select an event to edit.');
		} else if (edit_count > 1) {
			alert('You may only edit one event at a time.');
		} else {
			alert('Editing ['+ edit_event +']');
			var edit_url = '<?php echo pines_url('com_hrm', 'editcalendar', array('location' => $this->location->guid, 'employee' => $this->employee->guid)); ?>';
			pines.post(edit_url, { id: edit_event });
		}
	};

	// Delete Event(s)
	pines.com_hrm_delete_events = function() {
		var events = $("#calendar").fullCalendar('clientEvents');
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
				url: "<?php echo addslashes(pines_url('com_hrm', 'deleteevents')); ?>",
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
						$("#calendar").fullCalendar('removeEvents', remove_event);
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
	pines.com_hrm_clear_calendar = function() {
		if (confirm('Clear the entire calendar? This will remove all events for this location/employee.')) {

			var events = $("#calendar").fullCalendar('clientEvents');
			var event_guids = new Array();
			// Find the selected event(s).
			$.each(events, function(i, val) {
				if (val.group)
					event_guids.push(val.guid);
				else if (!val.group)
					event_guids.push(val.id);
			});

			$.ajax({
				url: "<?php echo addslashes(pines_url('com_hrm', 'deleteevents')); ?>",
				type: "POST",
				dataType: "json",
				data: {"events": event_guids},
				error: function(){
					pines.error("An error occured while trying to delete events from the calendar.");
				},
				success: function(data) {
					$("#calendar").fullCalendar('removeEvents');
					if (data)
						pines.error('Some events could not be deleted.');
					else
						alert('Cleared the calendar.');
				}
			});
			pines.com_hrm_save_calendar();
		}
	};

	// Unlink Event(s)
	pines.com_hrm_unlink_events = function() {
		var events = $("#calendar").fullCalendar('clientEvents');
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
			pines.com_hrm_save_calendar(true);
	};
	// ]]>
</script>
<div id="calendar">
	<?php if (gatekeeper('com_hrm/editcalendar')) { ?>
	<div class="vs-context-menu">
		<ul>
			<li class="copy"><a onclick="pines.com_hrm_copy_event();" id="menu_1">Duplicate</a></li>
			<li class="unlink"><a onclick="pines.com_hrm_unlink_events();" id="menu_2">Unlink</a></li>
			<li class="edit"><a onclick="pines.com_hrm_edit_event();" id="menu_3">Edit</a></li>
			<li class="delete seprator"><a onclick="pines.com_hrm_delete_events();" id="menu_4">Delete</a></li>
			<li class="clear seprator"><a onclick="pines.com_hrm_clear_calendar();" id="menu_5">Clear_All</a></li>
			<li class="help"><a onclick="pines.com_hrm_calendar_help();" id="menu_6">Help</a></li>
		</ul>
	</div>
	<?php } ?>
</div>