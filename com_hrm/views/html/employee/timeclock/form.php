<?php
/**
 * Edits an employees timeclock history.
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
$this->title = 'Edit Timeclock for '.htmlspecialchars($this->entity->user->name);
$pines->com_datetimepicker->load();
?>
<style type="text/css">
	/* <![CDATA[ */
	#p_muid_timeclock_edit .entry > div {
		padding: 3px;
	}
	#p_muid_timeclock_edit .entry button {
		float: right;
		margin: 3px;
	}
	#p_muid_timeclock_edit .entry .time {
		cursor: pointer;
	}

	#p_muid_timeclock_edit button.add-button {
		float: right;
		margin: 3px;
	}
	/* ]]> */
</style>
<script type="text/javascript">
	// <![CDATA[
	pines(function(){
		var cur_entry;
		var new_entry;
		var timezone = <?php echo json_encode($this->entity->user->get_timezone()); ?>;
		var date_time_dialog = $("#p_muid_date_time_dialog");
		var add_time_dialog = $("#p_muid_add_time_dialog");

		var format_time = function(elem, timestamp) {
			elem.html("Formatting...");
			$.ajax({
				url: <?php echo json_encode(pines_url('system', 'date_format')); ?>,
				type: "POST",
				dataType: "text",
				data: {"timestamp": timestamp, "timezone": timezone, "type": "custom", "format": "d M Y <\\s\\t\\r\\o\\n\\g>h:i:s A</\\s\\t\\r\\o\\n\\g> T"},
				error: function(){
					elem.html("Couldn't format.");
				},
				success: function(data){
					elem.html(data);
				}
			});
		};
		var format_time_range = function(elem, start_timestamp, end_timestamp) {
			elem.html("Formatting...");
			$.ajax({
				url: <?php echo json_encode(pines_url('system', 'date_range_format')); ?>,
				type: "POST",
				dataType: "text",
				data: {"start_timestamp": start_timestamp, "end_timestamp": end_timestamp, "timezone": timezone},
				error: function(){
					elem.html("Couldn't format.");
				},
				success: function(data){
					elem.html(data);
				}
			});
		};

		var clean_up = function() {
			// Sort by timestamp.
			$("#p_muid_timeclock_edit").prepend($("div.entry", "#p_muid_timeclock_edit").get().sort(function(a, b){
				return $(".timestamp_in", a).text() - $(".timestamp_in", b).text();
			}));
			// Check that times don't overlap.
			var error = false;
			$("div.entry", "#p_muid_timeclock_edit").each(function(){
				var entry = $(this);
				entry.find(".error").hide();
				if (!entry.next().length)
					return;
				if (parseInt($(".timestamp_out", entry).text()) > parseInt($(".timestamp_in", entry.next()).text())) {
					entry.find(".error").show();
					error = true;
				}
			});
			if (error)
				$("#p_muid_submit").addClass("ui-state-disabled").attr("disabled", "disabled");
			else {
				$("#p_muid_submit").removeClass("ui-state-disabled").removeAttr("disabled");
				save_to_form();
			}
		};

		var save_to_form = function() {
			// Turn the entries into an array.
			var entries = [];
			$("div.entry", "#p_muid_timeclock_edit").each(function(){
				entries[entries.length] = {
					"in": parseInt($(".timestamp_in", this).text()),
					"out": parseInt($(".timestamp_out", this).text()),
					"comments": $(".comments", this).text(),
					"extras": $(".extras", this).text(),
					"guid": $(".guid", this).text()
				};
			});
			$("input[name=clock]", "#p_muid_form").val(JSON.stringify(entries));
		};

		$("#p_muid_timeclock_edit").delegate(".time", "mouseover", function(){
			$(this).closest("div").addClass("ui-state-hover");
		}).delegate(".time", "mouseout", function(){
			$(this).closest("div").removeClass("ui-state-hover");
		}).delegate(".time", "click", function(){
			cur_entry = $(this).closest(".pf-element");
			var time_in = $(this).children(".time_in").text();
			var time_out = $(this).children(".time_out").text();
			var date_in = new Date(time_in.replace(/ [a-z]+$/i, ''));
			var date_out = new Date(time_out.replace(/ [a-z]+$/i, ''));
			$("#p_muid_cur_time_in").val(time_in).datetimepicker('setDate', date_in);
			$("#p_muid_cur_time_out").val(time_out).datetimepicker('setDate', date_out);
			$("#p_muid_cur_comments").val($(".comments", cur_entry).text());
			date_time_dialog.dialog("open");
		}).delegate("div.entry button", "click", function(){
			$(this).closest(".pf-element").animate({height: 0, opacity: 0}, "normal", function(){
				$(this).remove();
				clean_up();
			});
		});

		$("#p_muid_timeclock_edit button.add-button").click(function(){
			new_entry = $("#p_muid_timeclock_entry_template").clone(true).removeAttr("id").addClass("entry").insertBefore(this);
			new_entry.find(".timestamp_in, .timestamp_out").html(Math.floor(new Date().getTime() / 1000));
			format_time(new_entry.find(".time_in, .time_out"), new_entry.find(".timestamp_in").text());
			format_time_range(new_entry.find(".time_range"), new_entry.find(".timestamp_in").text(), new_entry.find(".timestamp_out").text());
			clean_up();
			new_entry.slideDown("normal");
			$("#p_muid_new_time_in").val("now");
			$("#p_muid_new_time_out").val("now");
			add_time_dialog.dialog("open");
		});

		date_time_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 600,
			buttons: {
				"Done": function(){
					$.ajax({
						url: <?php echo json_encode(pines_url('system', 'date_get_timestamp')); ?>,
						type: "POST",
						dataType: "text",
						data: {"date": $("#p_muid_cur_time_in").val(), "timezone": timezone},
						error: function(){
							pines.error("Couldn't get a timestamp from the server.");
							date_time_dialog.dialog('close');
						},
						success: function(data_in){
							$.ajax({
								url: <?php echo json_encode(pines_url('system', 'date_get_timestamp')); ?>,
								type: "POST",
								dataType: "text",
								data: {"date": $("#p_muid_cur_time_out").val(), "timezone": timezone},
								error: function(){
									pines.error("Couldn't get a timestamp from the server.");
									date_time_dialog.dialog('close');
								},
								success: function(data_out){
									date_time_dialog.dialog('close');
									cur_entry.find(".comments").html($("#p_muid_cur_comments").val());
									cur_entry.find(".timestamp_in").html(data_in);
									cur_entry.find(".timestamp_out").html(data_out);
									format_time(cur_entry.find(".time_in"), data_in);
									format_time(cur_entry.find(".time_out"), data_out);
									format_time_range(cur_entry.find(".time_range"), data_in, data_out);
									cur_entry.children("div").addClass("ui-state-highlight");
									clean_up();
								}
							});
						}
					});
				}
			}
		});

		add_time_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			width: 600,
			buttons: {
				"Done": function(){
					$.ajax({
						url: <?php echo json_encode(pines_url('system', 'date_get_timestamp')); ?>,
						type: "POST",
						dataType: "text",
						data: {"date": $("#p_muid_new_time_in").val(), "timezone": timezone},
						error: function(){
							pines.error("Couldn't get a timestamp from the server.");
							add_time_dialog.dialog('close');
						},
						success: function(data_in){
							$.ajax({
								url: <?php echo json_encode(pines_url('system', 'date_get_timestamp')); ?>,
								type: "POST",
								dataType: "text",
								data: {"date": $("#p_muid_new_time_out").val(), "timezone": timezone},
								error: function(){
									pines.error("Couldn't get a timestamp from the server.");
									add_time_dialog.dialog('close');
								},
								success: function(data_out){
									add_time_dialog.dialog('close');
									new_entry.find(".comments").html($("#p_muid_new_comments").val());
									new_entry.find(".timestamp_in").html(data_in);
									new_entry.find(".timestamp_out").html(data_out);
									format_time(new_entry.find(".time_in"), data_in);
									format_time(new_entry.find(".time_out"), data_out);
									format_time_range(new_entry.find(".time_range"), data_in, data_out);
									new_entry.children("div").addClass("ui-state-highlight");
									clean_up();
								}
							});
						}
					});
				}
			}
		});

		// Bypass timepicker's keypress blocker.
		var _doKeyPress = $.datepicker._doKeyPress;
		$.datepicker._doKeyPress = function(event) {
			return true;
		};
		$("#p_muid_cur_time_in, #p_muid_cur_time_out, #p_muid_new_time_in, #p_muid_new_time_out, #p_muid_time_start, #p_muid_time_end").datetimepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true,
			ampm: true,
			showSecond: true,
			timeFormat: "hh:mm:ss TT",
			stepHour: 1,
			stepMinute: 1,
			stepSecond: 1
		});
		$.datepicker._doKeyPress = _doKeyPress;

		var time_select_dialog = $("#p_muid_time_select_dialog").dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			open: function(){
				$("#p_muid_time_start").val(<?php echo json_encode(format_date($this->time_start, 'custom', 'Y-m-d h:i:s A', $this->entity->user->get_timezone())); ?>);
				$("#p_muid_time_end").val(<?php echo json_encode(format_date($this->time_end, 'custom', 'Y-m-d h:i:s A', $this->entity->user->get_timezone())); ?>);
			},
			buttons: {
				"Go": function(){
					pines.get(<?php echo json_encode(pines_url('com_hrm', 'employee/timeclock/edit', array('id' => $this->entity->user->guid))); ?>, {
						"time_start": $("#p_muid_time_start").val(),
						"time_end": $("#p_muid_time_end").val()
					});
					$(this).dialog("close");
				}
			}
		});

		$("#p_muid_change_range").click(function(){
			time_select_dialog.dialog("open");
		});

		save_to_form();
	});
	// ]]>
</script>
<div class="pf-form">
	<div class="pf-element pf-full-width">
		<span class="pf-label">Current Time Range</span>
		<div class="pf-group">
			<div style="float: right;"><button type="button" class="ui-state-default ui-corner-all" id="p_muid_change_range">Change time range.</button></div>
			<div class="pf-field" style="font-family: monospace;">
				<?php echo htmlspecialchars(format_date($this->time_start, 'custom', 'D d M Y h:i:s A T', $this->entity->user->get_timezone())); ?>
				<br />
				<?php echo htmlspecialchars(format_date($this->time_end, 'custom', 'D d M Y h:i:s A T', $this->entity->user->get_timezone())); ?>
			</div>
		</div>
	</div>
	<fieldset id="p_muid_timeclock_edit" class="pf-group">
		<legend>Timeclock Entries</legend>
		<?php foreach($this->entries as $entry) { ?>
		<div class="pf-element pf-full-width entry">
			<div class="ui-helper-clearfix ui-widget-content ui-corner-all">
				<button class="ui-state-default ui-corner-all">Delete</button>
				<span class="pf-label time" style="width: auto; font-family: monospace;">
					<span class="time_in"><?php echo format_date($entry->in, 'custom', 'd M Y <\s\t\r\o\n\g>h:i:s A</\s\t\r\o\n\g> T', $this->entity->user->get_timezone(true)); ?></span>
					-
					<span class="time_out"><?php echo format_date($entry->out, 'custom', 'd M Y <\s\t\r\o\n\g>h:i:s A</\s\t\r\o\n\g> T', $this->entity->user->get_timezone(true)); ?></span>
				</span>
				<span class="pf-label time_range" style="width: auto; float: right; margin-right: 1em;">
					<span class="time_range"><?php echo format_date_range($entry->in, $entry->out, null, $this->entity->user->get_timezone(true)); ?></span>
				</span>
				<span class="pf-label comments" style="width: auto; clear: left;"><?php echo htmlspecialchars($entry->comments); ?></span>
				<span class="pf-note" style="width: auto;">Timestamps: <span class="timestamp_in"><?php echo htmlspecialchars($entry->in); ?></span> - <span class="timestamp_out"><?php echo htmlspecialchars($entry->out); ?></span></span><br class="pf-clearing" />
				<span class="pf-label ui-state-error ui-corner-all error" style="width: auto; display: none;">Overlaps with the next entry!</span>
			</div>
			<div class="guid" style="display: none;"><?php echo htmlspecialchars(json_encode($entry->guid)); ?></div>
			<div class="extras" style="display: none;"><?php echo htmlspecialchars(json_encode($entry->extras)); ?></div>
		</div>
		<?php } ?>
		<div id="p_muid_timeclock_entry_template" class="pf-element pf-full-width" style="display: none;">
			<div class="ui-helper-clearfix ui-widget-content ui-corner-all">
				<button class="ui-state-default ui-corner-all">Delete</button>
				<span class="pf-label time" style="width: auto; font-family: monospace;">
					<span class="time_in"></span>
					-
					<span class="time_out"></span>
				</span>
				<span class="pf-label time_range" style="width: auto; float: right; margin-right: 1em;">
					<span class="time_range"></span>
				</span>
				<span class="pf-label comments" style="width: auto; clear: left;"></span>
				<span class="pf-note" style="width: auto;">Timestamp: <span class="timestamp_in"></span> - <span class="timestamp_out"></span></span><br class="pf-clearing" />
				<span class="pf-label ui-state-error ui-corner-all error" style="width: auto; display: none;">Overlaps with the next entry!</span>
			</div>
			<div class="extras" style="display: none;">[]</div>
		</div>
		<button class="add-button ui-state-default ui-corner-all">Add</button>
	</fieldset>
	<form method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_hrm', 'employee/timeclock/save')); ?>">
		<input type="hidden" name="clock" value="" />
		<input type="hidden" name="time_start" value="<?php echo htmlspecialchars($this->time_start); ?>" />
		<input type="hidden" name="time_end" value="<?php echo htmlspecialchars($this->time_end); ?>" />
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->user->guid; ?>" />
		<div class="pf-element pf-buttons">
			<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" id="p_muid_submit" type="submit" value="Submit" />
			<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_hrm', 'employee/timeclock/list')); ?>');" value="Cancel" />
		</div>
	</form>
</div>
<div id="p_muid_date_time_dialog" title="Adjust Entry" style="display: none;">
	<div style="width: 100%;">
		<div style="width: 48%; float: left;">
			<span>Time In:</span><br />
			<input class="ui-widget-content ui-corner-all" id="p_muid_cur_time_in" type="text" size="24" /><br />
			<span>Time Out:</span><br />
			<input class="ui-widget-content ui-corner-all" id="p_muid_cur_time_out" type="text" size="24" /><br />
			<span>Comments:</span><br />
			<input class="ui-widget-content ui-corner-all" id="p_muid_cur_comments" type="text" size="24" /><br />
		</div>
		<div style="width: 48%; float: left; margin-left: 1%;">
			<small>All times entered here are interpreted using the employee's timezone.</small><br />
			<small>Relative times are calculated from now, so "-1 day" means this time, yesterday.</small><br />
			<br /><span>Examples:</span><br />
			<small>now</small><br />
			<small>10 September 2000 8:13 AM</small><br />
			<small>10 September 2000 8:13 AM +8 hours</small><br />
			<small>-1 day</small><br />
			<small>+1 week 2 days 4 hours 2 seconds</small><br />
			<small>next Thursday</small><br />
			<small>last Monday 4pm</small>
		</div>
	</div>
</div>
<div id="p_muid_add_time_dialog" title="Add a New Entry" style="display: none;">
	<div style="width: 100%;">
		<div style="width: 48%; float: left;">
			<span>Time In:</span><br />
			<input class="ui-widget-content ui-corner-all" id="p_muid_new_time_in" type="text" size="24" /><br />
			<span>Time Out:</span><br />
			<input class="ui-widget-content ui-corner-all" id="p_muid_new_time_out" type="text" size="24" /><br />
			<span>Comments:</span><br />
			<input class="ui-widget-content ui-corner-all" id="p_muid_new_comments" type="text" size="24" /><br />
		</div>
		<div style="width: 48%; float: left; margin-left: 1%;">
			<small>All times entered here are interpreted using the employee's timezone.</small><br />
			<small>Relative times are calculated from now, so "-1 day" means this time, yesterday.</small><br />
			<br /><span>Examples:</span><br />
			<small>2 hours ago</small><br />
			<small>Jul 10 8:13</small><br />
			<small>10 July 2000 8:13 AM PST</small><br />
			<small>-1 day</small><br />
			<small>+1 week 2 days 4 hours 2 seconds</small><br />
			<small>next Thursday</small><br />
			<small>last Monday 4pm</small>
		</div>
	</div>
</div>
<div id="p_muid_time_select_dialog" title="Select a Time Range" style="display: none;">
	<span>Start Time:</span><br />
	<input class="ui-widget-content ui-corner-all" id="p_muid_time_start" type="text" size="24" /><br />
	<span>End Time:</span><br />
	<input class="ui-widget-content ui-corner-all" id="p_muid_time_end" type="text" size="24" />
</div>