// Help
function help(){
    alert('Click on an event to select/deselect it.');
}

// Duplicate Event(s)
function copy(){
	var events = $("#calendar").fullCalendar('clientEvents');
	var event_id;
	var copy_events = new Array();
	var copy_count = 0;
	// Find the selected event(s).
	jQuery.each(events, function(i, val) {
		if (val.selected) {
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
		add_events(copy_events);
	}
}

// Edit Event
function edit(){
    var events = $("#calendar").fullCalendar('clientEvents');
	var edit_event;
	var edit_count = 0;
	// Find the selected event(s).
	jQuery.each(events, function(i, val) {
		if (val.selected) {
			if (val.group)
				edit_event = val.guid;
			else
				edit_event = val.id;
			edit_count++;
		}
	});
	if (edit_count == 0) {
		alert('Please select an event to edit.');
	} else if (edit_count > 1) {
		alert('You may only edit one event at a time.');
	} else {
		alert('Editing ['+ edit_event +']');
		// Ensure that the url is set to editcalendar and specify the event id.
		var cur_location = location.toString().replace(/hrm\/.*/, 'hrm/');
		window.location = cur_location +'&id='+ edit_event;
	}
}

// Delete Event(s)
function del(){
	var events = $("#calendar").fullCalendar('clientEvents');
	var remove_events = new Array();
	var remove_count = 0;
	// Find the selected event(s).
	jQuery.each(events, function(i, val) {
		if (val.selected && val.group) {
			if (remove_events[remove_count-1] != val.id &&
				confirm(val.title + ' is a linked event, deleting it will remove the entire group.')) {
				remove_events[remove_count] = val.id;
				remove_count++;
			}
		} else if (val.selected && !val.group) {
			remove_events[remove_count] = val.id;
			remove_count++;
		}
	});
	if (remove_count == 0) {
		alert('Please select at least one event to delete.');
	} else {
		jQuery.each(remove_events, function(r, remove_event) {
			$("#calendar").fullCalendar('removeEvents', remove_event);
		});
		save_calendar();
		alert('Deleted Event(s).');
	}
}

// Clear Calendar
function clear(){
	if (confirm('Clear the entire calendar? You will lose all of your current events.')) {
		$("#calendar").fullCalendar('removeEvents');
		alert('Cleared the calendar.');
		save_calendar();
	}
}

// Unlink Event(s)
function unlink(){
	var events = $("#calendar").fullCalendar('clientEvents');
	var event_id;
	var unlink_count = 0;
	// Find the selected event(s).
	jQuery.each(events, function(i, val) {
		if (val.selected == true && val.group) {
			val.group = false;
			val.id = val.guid;
			unlink_count++;
		}
	});
	if (unlink_count == 0) {
		alert('Please select at least one bound event to unlink.');
	} else {
		save_calendar();
		$("#calendar").fullCalendar('refetchEvents');
		
	}
}