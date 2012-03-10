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
$this->title = 'Company Schedule [' . htmlspecialchars(isset($this->employee) ? $this->employee->name  : $this->location->name) . ']';
$this->note = 'Timezone: '.htmlspecialchars($this->timezone);

if (isset($this->employee->guid))
	$subject = $this->employee;
else
	$subject = $this->location;

if (!isset($subject)) {
	echo 'No calendar storage available. You must be logged in and able to have a calendar.';
	return;
}
?>
<style type="text/css" >
	#p_muid_form .helper {
		background-position: left;
		background-repeat: no-repeat;
		padding-left: 16px;
		display: none;
	}
</style>
<script type='text/javascript'>
	pines(function(){
		pines.selected_event = '';
		// Create the calendar object.
		$('#p_muid_calendar').fullCalendar({
			header: {
				left: 'prevYear prev,next nextYear today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay'
			},
			buttonIcons: {
				prevYear: 'circle-arrow-w',
				prev: 'circle-triangle-w',
				next: 'circle-triangle-e',
				nextYear: 'circle-arrow-e'
			},
			dragOpacity: {
				agenda: .5,
				'': 0.85
			},
			defaultView: <?php echo json_encode($this->view_type); ?>,
			weekMode: "liquid",
			firstDay: 1,
			selectable: true,
			theme: true,
			ignoreTimezone: false,
			firstHour: <?php echo isset($min_start) ? $min_start : 8; ?>,
			editable: <?php echo json_encode(gatekeeper('com_calendar/managecalendar')); ?>,
			events: {
				url: <?php echo json_encode(pines_url('com_calendar', 'events_json')); ?>,
				type: "POST",
				data: {
					location: <?php echo json_encode((string) $this->location->guid); ?>,
					employee: <?php echo json_encode((string) $this->employee->guid); ?>,
					descendants: <?php echo json_encode($this->descendants ? 'true' : 'false'); ?>,
					filter: <?php echo json_encode((string) $this->filter); ?>
				}
			},
			eventRender: function(event, element){
				var header;
				if (event.allDay)
					header = "<div><strong>All Day</strong></div>";
				else
					header = "<div><strong>Start:</strong> <span>"+pines.safe($.fullCalendar.formatDate(event.start, "ddd MMM dS, yyyy h:mm tt"))+"</span></div><div><strong>End:</strong> <span>"+pines.safe($.fullCalendar.formatDate(event.end, "ddd MMM dS, yyyy h:mm tt"))+"</span></div>";
				element.popover({
					title: pines.safe(event.title),
					content: header+"<p>"+pines.safe(event.info)+"</p>",
					placement: "top"
				});
			},
			loading: function(isLoading){
				if (isLoading)
					$("#p_muid_loading").show();
				else
					$("#p_muid_loading").hide();
			},
			select: function(start, end, allDay) {
				pines.p_muid_new_event(start.toString(), end.toString(), allDay);
			},
			eventClick: function(event,jsEvent,view) {
				if (event.editable == false && event.appointment == '') {
					alert(event.title+' is not editable.');
					return;
				}
				if (event.appointment != '')
					pines.p_muid_edit_appointment(event.appointment);
				else
					pines.p_muid_edit_event(event.id);
				pines.selected_event = $(this);
				pines.selected_event.addClass('ui-state-disabled');
			},
			eventDrop: function(event,dayDelta,minuteDelta,allDay,revertFunc) {
				event.selected = false;
				pines.p_muid_save_calendar([event], false, revertFunc);
			},
			eventDragStart: function(event, jsEvent, ui, view) {
				view.element.find(".fc-event").popover('hide');
			},
			eventDragStop: function(event, jsEvent, ui, view) {
				view.element.find(".fc-event").popover('hide');
				var events = $("#p_muid_calendar").fullCalendar('clientEvents');
				$.each(events, function(i, val) {
					val.selected = false;
				});
			},
			eventResize: function(event,dayDelta,minuteDelta,revertFunc) {
				event.selected = false;
				pines.p_muid_save_calendar([event], false, revertFunc);
			},
			eventResizeStart: function(event, jsEvent, ui, view) {
				view.element.find(".fc-event").popover('hide');
			},
			eventResizeStop: function(event, jsEvent, ui, view) {
				view.element.find(".fc-event").popover('hide');
			}
		});
		var current_date = $.fullCalendar.parseDate(<?php echo strtotime(format_date((int) $this->date[0], 'custom', 'Y-m-d', $this->timezone)); ?>);
		$('#p_muid_calendar').fullCalendar('gotoDate', current_date);
	});

	// Add new events to the calendar, mostly for duplicating events.
	pines.p_muid_add_events = function(events){
		$.ajax({
			url: <?php echo json_encode(pines_url('com_calendar', 'addevents')); ?>,
			type: "POST",
			dataType: "html",
			data: {"events": events},
			error: function(){
				pines.error("An error occured while trying to add events to the calendar.");
			},
			success: function(){
				$('#p_muid_calendar').fullCalendar('refetchEvents');
			}
		});
	};

	// Save all of the calendar events (or just the ones specified) by exporting
	// the data to their entities.
	pines.p_muid_save_calendar = function(events, refresh, revertFunc){
		var struct = [];
		if (!events)
			events = $("#p_muid_calendar").fullCalendar('clientEvents');
		$.each(events, function(i, e) {
			var cur_struct = {};
			if (e.group) {
				cur_struct.id = e.guid;
				cur_struct._id = e.id;
			} else {
				cur_struct.id = e.id;
				cur_struct._id = 0;
			}
			cur_struct.start = e.start.toString().replace(/[A-Za-z]+\s([A-Za-z\s\d\:]+)\s.*/, '$1').replace(',', '');
			cur_struct.end = e.end.toString().replace(/[A-Za-z]+\s([A-Za-z\s\d\:]+)\s.*/, '$1').replace(',', '');
			cur_struct.all_day = e.allDay;
			struct.push(cur_struct);
		});
		$.ajax({
			url: <?php echo json_encode(pines_url('com_calendar', 'savecalendar')); ?>,
			type: "POST",
			dataType: "html",
			data: {"events": JSON.stringify(struct), "timezone": <?php echo json_encode($this->timezone); ?>},
			error: function(){
				pines.error("An error occured while trying to save the calendar.");
			},
			success: function(data){
				if (data) {
					alert(data);
					if (events.length == 1 && revertFunc)
						revertFunc();
				}
				if (refresh || data)
					$('#p_muid_calendar').fullCalendar('refetchEvents');
			}
		});
	};

	// Help
	pines.p_muid_calendar_help = function(){
		alert('Click on an event to select/deselect it.');
	};

	// Duplicate Event(s)
	pines.p_muid_copy_event = function(){
		var events = $("#p_muid_calendar").fullCalendar('clientEvents', function(e){
			if (e.selected && e.editable == false) {
				alert(e.title+' cannot be copied, because it is not editable.');
				return false;
			}
			return e.selected;
		}),
			copy_events = [];
		// Find the selected event(s).
		$.each(events, function(i, e) {
			copy_events.push((e.group) ? e.guid : e.id);
		});
		if (!copy_events.length)
			alert('Please select at least one event to duplicate.');
		else
			pines.p_muid_add_events(copy_events);
	};

	// Delete Event(s)
	pines.p_muid_delete_events = function(){
		var events = $("#p_muid_calendar").fullCalendar('clientEvents', function(e){
			if (e.selected && !e.editable) {
				alert(e.title+' cannot be deleted, because it is not editable.');
				return false;
			}
			return e.selected;
		}),
			remove_events = [],
			event_guids = [],
			remove_count = 0;
		$.each(events, function(i, e){
			if (e.group) {
				if (remove_events[remove_count-1] != e.id && confirm(e.title+' is a linked event, deleting it will remove the entire group. Are you sure you want to delete it?')) {
					event_guids.push(e.guid);
					remove_events.push(e.id);
					remove_count++;
				}
			} else {
				event_guids.push(e.id);
				remove_events.push(e.id);
				remove_count++;
			}
		});
		if (remove_count == 0) {
			alert('Please select at least one event to delete.');
			return;
		}
		$.ajax({
			url: <?php echo json_encode(pines_url('com_calendar', 'deleteevents')); ?>,
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
	};

	// Clear Calendar
	pines.p_muid_clear_calendar = function(){
		if (!confirm('Clear the entire calendar? This will remove all events for this location/employee.'))
			return;
		var events = $("#p_muid_calendar").fullCalendar('clientEvents'),
			event_guids = [];
		// Find the event(s)' GUIDs.
		$.each(events, function(i, e) {
			if (e.group)
				event_guids.push(e.guid);
			else
				event_guids.push(e.id);
		});

		$.ajax({
			url: <?php echo json_encode(pines_url('com_calendar', 'deleteevents')); ?>,
			type: "POST",
			dataType: "json",
			data: {"events": event_guids},
			error: function(){
				pines.error("An error occured while trying to delete events from the calendar.");
			},
			success: function(data) {
				$('#p_muid_calendar').fullCalendar('refetchEvents');
				if (data)
					pines.error('Some events could not be deleted.');
				else
					alert('Cleared the calendar.');
			}
		});
	};

	// Unlink Event(s)
	pines.p_muid_unlink_events = function(){
		var events = $("#p_muid_calendar").fullCalendar('clientEvents', function(e){
			if (e.selected && e.group && !e.editable) {
				alert(e.title+' cannot be unlinked, because it is not editable.');
				return false;
			}
			return e.selected && e.group;
		});
		// Unlink the events.
		$.each(events, function(i, e) {
			e.group = false;
			e.id = e.guid;
		});
		if (!events.length)
			alert('Please select at least one bound event to unlink.');
		else
			pines.p_muid_save_calendar(null, true);
	};
</script>
<div id="p_muid_calendar" style="position: relative;">
	<div id="p_muid_loading" style="position: absolute; right: 0; bottom: 0; height: 32px; width: 32px; background-position: center center; background-repeat: no-repeat;" class="alert alert-info picon-32 picon-throbber"></div>
</div>