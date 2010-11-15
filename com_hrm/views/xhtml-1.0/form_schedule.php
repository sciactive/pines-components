<?php
/**
 * Display a form to edit a work schedule.
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
		width: 170px;
	}
	/* ]]> */
</style>
<script type='text/javascript'>
// <![CDATA[
	pines(function(){
		$("#p_muid_calendar").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true,
			onSelect: function(dateText){
				$("#p_muid_form [name=dates]").ptags_add(dateText);
			}
		});

		var timespan = $("[name=time_start_hour], [name=time_start_minute], [name=time_start_ampm], [name=time_end_hour], [name=time_end_minute], [name=time_end_ampm],", "#p_muid_form");
		$("#p_muid_form [name=all_day]").change(function(){
			if ($(this).is(":checked")) {
				timespan.addClass("ui-state-disabled").attr("disabled", "disabled");
			} else {
				timespan.removeClass("ui-state-disabled").removeAttr("disabled");
			}
		}).change();

		$("#p_muid_form [name=dates]").ptags();
	});
// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_hrm', 'saveschedule')); ?>">
	<div class="pf-element">
		<label><input class="pf-field" type="checkbox" name="all_day" value="ON" />All Day</label>
	</div>
	<div class="pf-element">
		<select class="ui-widget-content ui-corner-all" name="time_start_hour">
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="5">5</option>
			<option value="6">6</option>
			<option value="7">7</option>
			<option value="8">8</option>
			<option value="9" selected="selected">9</option>
			<option value="10">10</option>
			<option value="11">11</option>
			<option value="0">12</option>
		</select>:
		<select class="ui-widget-content ui-corner-all" name="time_start_minute">
			<option value="0" selected="selected">00</option>
			<option value="15">15</option>
			<option value="30">30</option>
			<option value="45">45</option>
		</select>
		<select class="ui-widget-content ui-corner-all" name="time_start_ampm">
			<option value="am" selected="selected">AM</option>
			<option value="pm">PM</option>
		</select>
	</div>
	<div class="pf-element">
		<select class="ui-widget-content ui-corner-all" name="time_end_hour">
			<option value="1">1</option>
			<option value="2">2</option>
			<option value="3">3</option>
			<option value="4">4</option>
			<option value="5" selected="selected">5</option>
			<option value="6">6</option>
			<option value="7">7</option>
			<option value="8">8</option>
			<option value="9">9</option>
			<option value="10">10</option>
			<option value="11">11</option>
			<option value="0">12</option>
		</select>:
		<select class="ui-widget-content ui-corner-all" name="time_end_minute">
			<option value="0" selected="selected">00</option>
			<option value="15">15</option>
			<option value="30">30</option>
			<option value="45">45</option>
		</select>
		<select class="ui-widget-content ui-corner-all" name="time_end_ampm">
			<option value="am">AM</option>
			<option value="pm" selected="selected">PM</option>
		</select>
	</div>
	<div class="pf-element pf-full-width">
		<span id="p_muid_calendar"></span>
	</div>
	<br class="pf-clearing" />
	<div class="pf-group">
		<input type="hidden" name="dates" size="24" value="" />
	</div>
	<?php if (isset($this->entity->guid)) { ?>
	<input type="hidden" name="employee" value="<?php echo $this->entity->guid; ?>" />
	<?php } ?>
</form>