<?php
/**
 * Display a form to request time off.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_form .form_center {
		text-align: center;
	}
	#p_muid_form .form_input {
		width: 90%;
	}
	#p_muid_requests {
		width: 100%;
		float: left;
		clear: both;
		margin-top: 20px;
	}
	#p_muid_requests .ui-state-highlight, #p_muid_requests .ui-state-error {
		border: 0;
		background: transparent none;
	}
	/* ]]> */
</style>
<script type='text/javascript'>
// <![CDATA[
	pines(function(){
		$("#p_muid_start").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});
		$("#p_muid_end").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});

		var timespan = $("[name=time_start], [name=time_end]", "#p_muid_form");
		$("#p_muid_form [name=all_day]").change(function(){
			if ($(this).is(":checked")) {
				timespan.addClass("ui-state-disabled").attr("disabled", "disabled");
			} else {
				timespan.removeClass("ui-state-disabled").removeAttr("disabled");
			}
		}).change();

		$("#p_muid_start").change(function(){
			var start_date = new Date($(this).val());
			var end_date = new Date($("#p_muid_end").val());
			if (start_date > end_date)
				$("#p_muid_end").val($(this).val());
		}).change();
	});
// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="">
	<div class="pf-element">
		<span class="pf-note">Reason for Time Off</span><input class="ui-widget-content form_input" type="text" id="p_muid_reason" name="reason" value="<?php echo $this->entity->reason; ?>" />
	</div>
	<?php
		if ($this->entity->guid) {
			$start_date = format_date($this->entity->start, 'date_sort');
			$start_time = format_date($this->entity->start, 'custom', 'H');
			$end_date = format_date($this->entity->end, 'date_sort');
			$end_time = format_date($this->entity->end, 'custom', 'H');
		}
	?>
	<div class="pf-element">
		<label><input class="pf-field ui-widget-content" type="checkbox" name="all_day" value="ON" <?php echo ($this->entity->all_day) ? 'checked="checked" ' : ''; ?>/>All Day</label>
	</div>
	<div class="pf-element pf-full-width">
		<span class="pf-note">Start</span><input class="ui-widget-content form_center" type="text" size="12" id="p_muid_start" name="start" value="<?php echo empty($start_date) ? format_date(time(), 'date_sort') : $start_date; ?>" />
		<select class="ui-widget-content" name="time_start">
			<option value="0" <?php echo ($start_time == '0') ? 'selected="selected"' : ''; ?>>12:00 AM</option>
			<option value="1" <?php echo ($start_time == '1') ? 'selected="selected"' : ''; ?>>1:00 AM</option>
			<option value="2" <?php echo ($start_time == '2') ? 'selected="selected"' : ''; ?>>2:00 AM</option>
			<option value="3" <?php echo ($start_time == '3') ? 'selected="selected"' : ''; ?>>3:00 AM</option>
			<option value="4" <?php echo ($start_time == '4') ? 'selected="selected"' : ''; ?>>4:00 AM</option>
			<option value="5" <?php echo ($start_time == '5') ? 'selected="selected"' : ''; ?>>5:00 AM</option>
			<option value="6" <?php echo ($start_time == '6') ? 'selected="selected"' : ''; ?>>6:00 AM</option>
			<option value="7" <?php echo ($start_time == '7') ? 'selected="selected"' : ''; ?>>7:00 AM</option>
			<option value="8" <?php echo ($start_time == '8') ? 'selected="selected"' : ''; ?>>8:00 AM</option>
			<option value="9" <?php echo ($start_time == '9' || empty($start_time)) ? 'selected="selected"' : ''; ?>>9:00 AM</option>
			<option value="10" <?php echo ($start_time == '10') ? 'selected="selected"' : ''; ?>>10:00 AM</option>
			<option value="11" <?php echo ($start_time == '11') ? 'selected="selected"' : ''; ?>>11:00 AM</option>
			<option value="12" <?php echo ($start_time == '12') ? 'selected="selected"' : ''; ?>>12:00 PM</option>
			<option value="13" <?php echo ($start_time == '13') ? 'selected="selected"' : ''; ?>>1:00 PM</option>
			<option value="14" <?php echo ($start_time == '14') ? 'selected="selected"' : ''; ?>>2:00 PM</option>
			<option value="15" <?php echo ($start_time == '15') ? 'selected="selected"' : ''; ?>>3:00 PM</option>
			<option value="16" <?php echo ($start_time == '16') ? 'selected="selected"' : ''; ?>>4:00 PM</option>
			<option value="17" <?php echo ($start_time == '17') ? 'selected="selected"' : ''; ?>>5:00 PM</option>
			<option value="18" <?php echo ($start_time == '18') ? 'selected="selected"' : ''; ?>>6:00 PM</option>
			<option value="19" <?php echo ($start_time == '19') ? 'selected="selected"' : ''; ?>>7:00 PM</option>
			<option value="20" <?php echo ($start_time == '20') ? 'selected="selected"' : ''; ?>>8:00 PM</option>
			<option value="21" <?php echo ($start_time == '21') ? 'selected="selected"' : ''; ?>>9:00 PM</option>
			<option value="22" <?php echo ($start_time == '22') ? 'selected="selected"' : ''; ?>>10:00 PM</option>
			<option value="23" <?php echo ($start_time == '23') ? 'selected="selected"' : ''; ?>>11:00 PM</option>
		</select>
	</div>
	<div class="pf-element">
		<span class="pf-note">End</span><input class="ui-widget-content form_center" type="text" size="12" id="p_muid_end" name="end" value="<?php echo empty($end_date) ? format_date(time(), 'date_sort') : $end_date; ?>" />
		<select class="ui-widget-content" name="time_end">
			<option value="0" <?php echo ($end_time == '0') ? 'selected="selected"' : ''; ?>>12:00 AM</option>
			<option value="1" <?php echo ($end_time == '1') ? 'selected="selected"' : ''; ?>>1:00 AM</option>
			<option value="2" <?php echo ($end_time == '2') ? 'selected="selected"' : ''; ?>>2:00 AM</option>
			<option value="3" <?php echo ($end_time == '3') ? 'selected="selected"' : ''; ?>>3:00 AM</option>
			<option value="4" <?php echo ($end_time == '4') ? 'selected="selected"' : ''; ?>>4:00 AM</option>
			<option value="5" <?php echo ($end_time == '5') ? 'selected="selected"' : ''; ?>>5:00 AM</option>
			<option value="6" <?php echo ($end_time == '6') ? 'selected="selected"' : ''; ?>>6:00 AM</option>
			<option value="7" <?php echo ($end_time == '7') ? 'selected="selected"' : ''; ?>>7:00 AM</option>
			<option value="8" <?php echo ($end_time == '8') ? 'selected="selected"' : ''; ?>>8:00 AM</option>
			<option value="9" <?php echo ($end_time == '9') ? 'selected="selected"' : ''; ?>>9:00 AM</option>
			<option value="10" <?php echo ($end_time == '10') ? 'selected="selected"' : ''; ?>>10:00 AM</option>
			<option value="11" <?php echo ($end_time == '11') ? 'selected="selected"' : ''; ?>>11:00 AM</option>
			<option value="12" <?php echo ($end_time == '12') ? 'selected="selected"' : ''; ?>>12:00 PM</option>
			<option value="13" <?php echo ($end_time == '13') ? 'selected="selected"' : ''; ?>>1:00 PM</option>
			<option value="14" <?php echo ($end_time == '14') ? 'selected="selected"' : ''; ?>>2:00 PM</option>
			<option value="15" <?php echo ($end_time == '15') ? 'selected="selected"' : ''; ?>>3:00 PM</option>
			<option value="16" <?php echo ($end_time == '16') ? 'selected="selected"' : ''; ?>>4:00 PM</option>
			<option value="17" <?php echo ($end_time == '17' || empty($end_time)) ? 'selected="selected"' : ''; ?>>5:00 PM</option>
			<option value="18" <?php echo ($end_time == '18') ? 'selected="selected"' : ''; ?>>6:00 PM</option>
			<option value="19" <?php echo ($end_time == '19') ? 'selected="selected"' : ''; ?>>7:00 PM</option>
			<option value="20" <?php echo ($end_time == '20') ? 'selected="selected"' : ''; ?>>8:00 PM</option>
			<option value="21" <?php echo ($end_time == '21') ? 'selected="selected"' : ''; ?>>9:00 PM</option>
			<option value="22" <?php echo ($end_time == '22') ? 'selected="selected"' : ''; ?>>10:00 PM</option>
			<option value="23" <?php echo ($end_time == '23') ? 'selected="selected"' : ''; ?>>11:00 PM</option>
		</select>
	</div>
	<div id="p_muid_requests" class="ui-widget-content ui-corner-all">
		<div class="pf-form" style="margin: 0 .5em .5em;">
			<div class="pf-element pf-heading">
				<h1><span class="ui-state-highlight">Pending</span>/<span class="ui-state-error">Declined</span> Requests</h1>
			</div>
			<?php foreach ($this->requests as $cur_request) {
			$style = ($cur_request->status == 'declined') ? 'ui-state-error' : 'ui-state-highlight'; ?>
			<div class="pf-element" style="padding-bottom: 0;">
				<span class="pf-note" style="width: auto;"><?php echo format_date($cur_request->start, 'date_short'); ?></span><a class="pf-field <?php echo $style; ?>" href="<?php echo htmlentities(pines_url('com_hrm', 'editcalendar', array('rto_id' => $cur_request->guid))); ?>"><?php echo $cur_request->reason; ?></a>
			</div>
			<?php } ?>
		</div>
	</div>
	<input type="hidden" name="employee" value="<?php echo $_SESSION['user']->guid; ?>" />
	<?php if (isset($this->entity->guid)) { ?>
	<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
	<?php } ?>
</form>