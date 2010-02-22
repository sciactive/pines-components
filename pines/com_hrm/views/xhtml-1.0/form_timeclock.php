<?php
/**
 * Edits an employees timeclock history.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = "Edit Timeclock for {$this->entity->name}";
?>
<script type="text/javascript">
	// <![CDATA[
	$(function(){
		var cur_entry;
		var timezone = "<?php echo addslashes($this->entity->get_timezone()); ?>";
		function format_time(elem, timestamp) {
			elem.html("Formatting...");
			$.ajax({
				url: "<?php echo pines_url('system', 'date_format'); ?>",
				type: "POST",
				dataType: "text",
				data: {"timestamp": timestamp, "timezone": timezone},
				error: function(){
					elem.html("Couldn't format.");
				},
				success: function(data){
					elem.html(data);
				}
			});
		}

		function clean_up() {
			// Sort by timestamp.
			$("#timeclock_edit").prepend($("#timeclock_edit div.entry").get().sort(function(a, b){
				return $(a).find(".timestamp").text() - $(b).find(".timestamp").text();
			}));
			// Make sure statuses are sequential.
			$("#timeclock_edit .entry:even .status").html("in");
			$("#timeclock_edit .entry:odd .status").html("out");
			save_to_form();
		}

		function save_to_form() {
			// Turn the entries into an array.
			var entries = [];
			$("#timeclock_edit div.entry").each(function(){
				entries[entries.length] = {
					"time": parseInt($(this).find(".timestamp").text()),
					"status": $(this).find(".status").text()
				};
			});
			$("#timeclock_form input[name=clock]").val(JSON.stringify(entries));
		}

		$("#timeclock_edit .time").live("click", function(){
			cur_entry = $(this).closest(".element");
			$("#cur_time").val($(this).text());
			$("#date_time_dialog").dialog("open");
		});

		$("#timeclock_edit div.entry button").live("click", function(){
			$(this).closest(".element").animate({height: 0, opacity: 0}, "normal", function(){
				$(this).remove();
				clean_up();
			});
		});

		$("#timeclock_edit button.add-button").click(function(){
			var new_entry = $("#timeclock_entry_template").clone().addClass("entry").removeAttr("id");
			$(this).before(new_entry);
			new_entry.find(".timestamp").html(Math.floor(new Date().getTime() / 1000));
			format_time(new_entry.find(".time"), new_entry.find(".timestamp").text());
			clean_up();
			new_entry.slideDown("normal");
		});

		$("#date_time_dialog").dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				"Done": function() {
					$.ajax({
						url: "<?php echo pines_url('system', 'date_get_timestamp'); ?>",
						type: "POST",
						dataType: "text",
						data: {"date": $("#cur_time").val(), "timezone": timezone},
						error: function(){
							alert("Couldn't get a timestamp from the server.");
							$("#date_time_dialog").dialog('close');
						},
						success: function(data){
							$("#date_time_dialog").dialog('close');
							cur_entry.find(".timestamp").html(data);
							format_time(cur_entry.find(".time"), data);
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
<div class="pform" id="timeclock_edit">
<?php foreach($this->entity->timeclock as $key => $entry) { ?>
	<div class="element full_width entry">
		<div style="padding: 3px;" class="ui-helper-clearfix ui-widget-content ui-corner-all">
			<button style="float: right; margin: 3px;" class="ui-state-default ui-corner-all">Delete</button>
			<span class="label time" style="cursor: pointer;"><?php echo pines_date_format($entry['time'], $this->entity->get_timezone(true)); ?></span>
			<span class="note timestamp"><?php echo $entry['time']; ?></span>
			<span class="field status"><?php echo $entry['status']; ?></span>
		</div>
	</div>
<?php } ?>
	<div id="timeclock_entry_template" class="element full_width" style="display: none;">
		<div style="padding: 3px;" class="ui-helper-clearfix ui-widget-content ui-corner-all">
			<button style="float: right; margin: 3px;" class="ui-state-default ui-corner-all">Delete</button>
			<span class="label time" style="cursor: pointer;"></span>
			<span class="note timestamp"></span>
			<span class="field status"></span>
		</div>
	</div>
	<button style="float: right; margin: 3px;" class="add-button ui-state-default ui-corner-all">Add</button>
	<div id="date_time_dialog" title="Adjust Time">
		<span>Time:</span><br />
		<input id="cur_time" type="text" size="24" /><br />
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
	<form method="post" id="timeclock_form" action="<?php echo pines_url('com_hrm', 'savetimeclock'); ?>">
		<input type="hidden" name="clock" value="" />
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<div class="element buttons">
			<input class="button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
			<input class="button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="window.location='<?php echo pines_url('com_hrm', 'listtimeclocks'); ?>';" value="Cancel" />
		</div>
	</form>
</div>