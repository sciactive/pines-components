<?php
/**
 * Display a form to select a timespan.
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
	#p_muid_form {
		padding-left: 25px;
	}
	#p_muid_form .form_date {
		width: 80%;
		text-align: center;
	}
	/* ]]> */
</style>
<script type='text/javascript'>
	// <![CDATA[
	pines(function(){
		$("#p_muid_start_date").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});
		$("#p_muid_end_date").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});

		var timespan = $("#p_muid_form div.timespan");
		$("#p_muid_form [name=all_time]").change(function(){
			var all_time = $(this);
			if (all_time.is(":checked") && all_time.val() == "timespan") {
				timespan.removeClass("ui-priority-secondary");
				$("#p_muid_form .form_date").removeAttr("disabled");
				$("#p_muid_form [name=timespan_saver]").val('timespan');
			} else if (all_time.is(":checked") && all_time.val() == "alltime") {
				timespan.addClass("ui-priority-secondary");
				$("#p_muid_form .form_date").attr("disabled", "disabled");
				$("#p_muid_form [name=timespan_saver]").val('alltime');
			}
		}).change();

	});
	// ]]>
</script>
<form class="pf-form" id="p_muid_form" action="">
	<div class="pf-element">
		<label><input class="pf-field" type="radio" name="all_time" value="alltime" <?php echo $this->all_time ? 'checked="checked"' : ''; ?>/>Entire History</label>
		<label><input class="pf-field" type="radio" name="all_time" value="timespan" <?php echo !$this->all_time ? 'checked="checked"' : ''; ?>/>Timespan</label>
	</div>
	<div class="timespan">
		<div class="pf-element">
			<span class="pf-note">Start</span><input class="ui-widget-content ui-corner-all form_date" type="text" id="p_muid_start_date" name="start_date" value="<?php echo isset($this->start_date) ? htmlspecialchars($this->start_date) : ''; ?>" />
		</div>
		<div class="pf-element">
			<span class="pf-note">End</span><input class="ui-widget-content ui-corner-all form_date" type="text" id="p_muid_end_date" name="end_date" value="<?php echo isset($this->end_date) ? htmlspecialchars($this->end_date) : ''; ?>" />
		</div>
		<input type="hidden" name="timespan_saver" value="<?php echo htmlspecialchars($this->all_time); ?>" />
	</div>
</form>