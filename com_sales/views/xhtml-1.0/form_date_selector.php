<?php
/**
 * Display a form to search a stock item's history.
 * 
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<style type="text/css" >
	/* <![CDATA[ */
	#date_details {
		padding-left: 25px;
	}
	.form_text {
		width: 155px;
		text-align: center;
	}
	.form_input {
		width: 170px;
		text-align: center;
	}
	.form_select {
		width: 170px;
	}
	/* ]]> */
</style>
<script type='text/javascript'>
// <![CDATA[
	pines(function(){
		$("#start_date").datepicker({
			dateFormat: "m/d/yy",
			changeMonth: true,
			changeYear: true
		});
		$("#end_date").datepicker({
			dateFormat: "m/d/yy",
			changeMonth: true,
			changeYear: true
		});

		var timespan = $("#date_details [name=timespan]");
		$("#date_details [name=all_time]").change(function(){
			var all_time = $(this);
			if (all_time.is(":checked") && all_time.val() == "timeSpan") {
				timespan.show();
				$("#date_details [name=timespan_saver]").val('timespan');
			} else if (all_time.is(":checked") && all_time.val() == "allTime") {
				timespan.hide();
				$("#date_details [name=timespan_saver]").val('alltime');
			}
		}).change();

	});
// ]]>
</script>
<form class="pf-form" id="date_details">
	<div class="pf-element">
		<label><input class="pf-field ui-widget-content" type="radio" name="all_time" value="timeSpan" checked="checked"/>Timespan</label>
		<label><input class="pf-field ui-widget-content" type="radio" name="all_time" value="allTime" <?php echo $this->all_time ? 'checked="checked"' : ''; ?>/>Entire History</label>
	</div>
	<div name="timespan">
		<div class="pf-element" style="padding-bottom: 0px;">
			<span class="pf-note">Start</span><input class="ui-widget-content form_text" type="text" id="start_date" name="start_date" value="<?php echo format_date($this->start_date, 'custom', 'm/d/Y'); ?>" />
		</div>
		<div class="pf-element" style="padding-bottom: 25px;">
			<span class="pf-note">End</span><input class="ui-widget-content form_text" type="text" id="end_date" name="end_date" value="<?php echo format_date($this->end_date, 'custom', 'm/d/Y'); ?>" />
		</div>
		<input type="hidden" name="timespan_saver" value="<?php echo $this->all_time; ?>" />
	</div>
</form>