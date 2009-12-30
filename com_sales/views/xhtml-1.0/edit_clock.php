<?php
/**
 * Edits an employees timeclock history.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = "Edit Timeclock for {$this->user->name} [{$this->user->username}]";
?>
<script type="text/javascript">
	// <![CDATA[

	$(function(){
		var cur_entry;
		function format_time(timestamp) {
			var d = new Date();
			d.setTime(timestamp * 1000);
			return d.toLocaleString();
		}

		function clean_up() {
			// Sort by timestamp.
			$("#timeclock_edit").prepend($("#timeclock_edit div.element:not(.buttons)").get().sort(function(a, b){
				return $(a).find(".timestamp").text() - $(b).find(".timestamp").text();
			}));
			// Make sure statuses are sequential.
			$("#timeclock_edit .status:odd").html("in");
			$("#timeclock_edit .status:even").html("out");
			// Format dates.
			$("#timeclock_edit .time").each(function(){
				$(this).html(format_time($(this).next(".timestamp").html()));
			});
		}

		$("#timeclock_edit .time").each(function(){
			$(this).html(format_time($(this).next(".timestamp").html()));
		}).live("click", function(){
			cur_entry = $(this).closest(".element");
			var d = new Date();
			d.setTime(parseInt($(this).next(".timestamp").html()) * 1000);
			$("#cur_time").val(d.toTimeString());
			$("#cur_date").datepicker("setDate", d);
			$("#date_time_dialog").dialog("open");
		});

		$("#timeclock_edit div.element:not(.buttons) button").live("click", function(){
			$(this).closest(".element").animate({height: 0, opacity: 0}, "normal", function(){
				$(this).remove();
				clean_up();
			});
		});

		$("#timeclock_edit button.add-button").click(function(){
			var new_entry = $("#timeclock_entry_template").clone().removeAttr("id");
			$(this).before(new_entry);
			new_entry.find(".timestamp").html(Math.floor(new Date().getTime() / 1000));
			clean_up();
			new_entry.slideDown("normal");
		});

		$("#date_time_dialog").dialog({
			bgiframe: true,
			autoOpen: false,
			modal: true,
			buttons: {
				"Done": function() {
					var d  = $("#cur_date").datepicker("getDate");
					d.setTime(Date.parse(d.toDateString()+" "+$("#cur_time").val()));
					if (isNaN(d.getTime())) {
						alert("Please enter a valid date and time.");
						return;
					}
					cur_entry.find(".timestamp").html(Math.floor(d.getTime() / 1000));
					clean_up();
					$(this).dialog('close');
				}
			}
		});
		$("#cur_date").datepicker({changeMonth: true, changeYear: true});
	});

	// ]]>
</script>
<div class="pform" id="timeclock_edit">
	<div id="timeclock_entry_template" class="element full_width" style="display: none;">
		<div style="padding: 3px;" class="ui-helper-clearfix ui-widget-content ui-corner-all">
			<button style="float: right; margin: 3px;" class="ui-state-default ui-corner-all" onmouseover="$(this).addClass('ui-state-hover')" onmouseout="$(this).removeClass('ui-state-hover')">Delete</button>
			<span class="label time" style="cursor: pointer;"></span>
			<span class="note timestamp"></span>
			<span class="field status"></span>
		</div>
	</div>
<?php foreach($this->user->com_sales->timeclock as $key => $entry) { ?>
	<div class="element full_width">
		<div style="padding: 3px;" class="ui-helper-clearfix ui-widget-content ui-corner-all">
			<button style="float: right; margin: 3px;" class="ui-state-default ui-corner-all" onmouseover="$(this).addClass('ui-state-hover')" onmouseout="$(this).removeClass('ui-state-hover')">Delete</button>
			<span class="label time" style="cursor: pointer;"><?php echo $entry['time']; ?></span>
			<span class="note timestamp"><?php echo $entry['time']; ?></span>
			<span class="field status"><?php echo $entry['status']; ?></span>
		</div>
	</div>
<?php } ?>
	<button style="float: right; margin: 3px;" class="add-button ui-state-default ui-corner-all" onmouseover="$(this).addClass('ui-state-hover')" onmouseout="$(this).removeClass('ui-state-hover')">Add</button>
	<div id="date_time_dialog" title="Choose Date and Time">
		<span>Times are local to your timezone.</span><br /><br />
		<span>Date:</span><br />
		<div id="cur_date"></div><br /><br />
		<span>Time:</span><br />
		<input id="cur_time" type="text" size="24" />
	</div>
	<div>

	</div>
</div>