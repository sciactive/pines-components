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
defined('P_RUN') or die('Direct access prohibited');
$this->title = "Edit Timeclock for {$this->entity->name}";
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
		var timezone = "<?php echo addslashes($this->entity->get_timezone()); ?>";
		var date_time_dialog = $("#p_muid_date_time_dialog");
		var add_time_dialog = $("#p_muid_add_time_dialog");

		var format_time = function(elem, timestamp) {
			elem.html("Formatting...");
			$.ajax({
				url: "<?php echo pines_url('system', 'date_format'); ?>",
				type: "POST",
				dataType: "text",
				data: {"timestamp": timestamp, "timezone": timezone, "type": "full_med"},
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
				return $(".timestamp", a).text() - $(".timestamp", b).text();
			}));
			// Make sure statuses are sequential.
			$(".entry:even .status", "#p_muid_timeclock_edit").html("in");
			$(".entry:odd .status", "#p_muid_timeclock_edit").html("out");
			save_to_form();
		};

		var save_to_form = function() {
			// Turn the entries into an array.
			var entries = [];
			$("div.entry", "#p_muid_timeclock_edit").each(function(){
				entries[entries.length] = {
					"time": parseInt($(".timestamp", this).text()),
					"status": $(".status", this).text()
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
			$("#p_muid_cur_time").val($(this).text());
			date_time_dialog.dialog("open");
		}).delegate("div.entry button", "click", function(){
			$(this).closest(".pf-element").animate({height: 0, opacity: 0}, "normal", function(){
				$(this).remove();
				clean_up();
			});
		});

		$("#p_muid_timeclock_edit button.add-button").click(function(){
			new_entry = $("#p_muid_timeclock_entry_template").clone(true).removeAttr("id").addClass("entry").insertBefore(this);
			new_entry.find(".timestamp").html(Math.floor(new Date().getTime() / 1000));
			format_time(new_entry.find(".time"), new_entry.find(".timestamp").text());
			clean_up();
			new_entry.slideDown("normal");
			$("#p_muid_new_time").val("now");
			add_time_dialog.dialog("open");
		});

		date_time_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				"Done": function() {
					$.ajax({
						url: "<?php echo pines_url('system', 'date_get_timestamp'); ?>",
						type: "POST",
						dataType: "text",
						data: {"date": $("#p_muid_cur_time").val(), "timezone": timezone},
						error: function(){
							pines.error("Couldn't get a timestamp from the server.");
							date_time_dialog.dialog('close');
						},
						success: function(data){
							date_time_dialog.dialog('close');
							cur_entry.find(".timestamp").html(data);
							format_time(cur_entry.find(".time"), data);
							cur_entry.children("div").addClass("ui-state-highlight");
							clean_up();
						}
					});
				}
			}
		});
		
		add_time_dialog.dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				"Done": function() {
					$.ajax({
						url: "<?php echo pines_url('system', 'date_get_timestamp'); ?>",
						type: "POST",
						dataType: "text",
						data: {"date": $("#p_muid_new_time").val(), "timezone": timezone},
						error: function(){
							pines.error("Couldn't get a timestamp from the server.");
							add_time_dialog.dialog('close');
						},
						success: function(data){
							add_time_dialog.dialog('close');
							new_entry.find(".timestamp").html(data);
							format_time(new_entry.find(".time"), data);
							new_entry.children("div").addClass("ui-state-highlight");
							clean_up();
						}
					});
				}
			}
		});

		save_to_form();
	});
	// ]]>
</script>
<div class="pf-form" id="p_muid_timeclock_edit">
	<?php foreach($this->entity->timeclock as $key => $entry) { ?>
	<div class="pf-element pf-full-width entry">
		<div class="ui-helper-clearfix ui-widget-content ui-corner-all">
			<button class="ui-state-default ui-corner-all">Delete</button>
			<span class="pf-label time"><?php echo format_date($entry['time'], 'full_med', '', $this->entity->get_timezone(true)); ?></span>
			<span class="pf-note">Timestamp: <span class="timestamp"><?php echo $entry['time']; ?></span></span>
			<span class="pf-field status"><?php echo $entry['status']; ?></span>
		</div>
	</div>
	<?php } ?>
	<div id="p_muid_timeclock_entry_template" class="pf-element pf-full-width" style="display: none;">
		<div class="ui-helper-clearfix ui-widget-content ui-corner-all">
			<button class="ui-state-default ui-corner-all">Delete</button>
			<span class="pf-label time"></span>
			<span class="pf-note">Timestamp: <span class="timestamp"></span></span>
			<span class="pf-field status"></span>
		</div>
	</div>
	<button class="add-button ui-state-default ui-corner-all">Add</button>
	<form method="post" id="p_muid_form" action="<?php echo htmlentities(pines_url('com_hrm', 'employee/timeclock/save')); ?>">
		<input type="hidden" name="clock" value="" />
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<div class="pf-element pf-buttons">
			<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
			<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlentities(pines_url('com_hrm', 'employee/timeclock/list')); ?>');" value="Cancel" />
		</div>
	</form>
</div>
<div id="p_muid_date_time_dialog" title="Adjust Time" style="display: none;">
	<span>Time:</span><br />
	<input id="p_muid_cur_time" type="text" size="24" /><br />
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
<div id="p_muid_add_time_dialog" title="Add a New Time" style="display: none;">
	<span>Time:</span><br />
	<input id="p_muid_new_time" type="text" size="24" /><br />
	<small>Relative times are calculated from now, so "-1 day" means 24 hours ago.</small><br />
	<br /><span>Examples:</span><br />
	<small>2 hours ago</small><br />
	<small>Jul 10 8:13</small><br />
	<small>10 July 2000 8:13 AM PST</small><br />
	<small>-1 day</small><br />
	<small>+1 week 2 days 4 hours 2 seconds</small><br />
	<small>next Thursday</small><br />
	<small>last Monday 4pm</small>
</div>