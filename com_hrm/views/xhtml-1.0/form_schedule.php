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

		var timespan = $("[name=time_start], [name=time_end]", "#p_muid_form");
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
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlentities(pines_url('com_hrm', 'saveschedule')); ?>">
	<div class="pf-element">
		<label><input class="pf-field" type="checkbox" name="all_day" value="ON" />All Day</label>
	</div>
	<div class="pf-element">
		<select class="ui-widget-content ui-corner-all" name="time_start">
			<option value="0">12:00 AM</option>
			<option value="1">1:00 AM</option>
			<option value="2">2:00 AM</option>
			<option value="3">3:00 AM</option>
			<option value="4">4:00 AM</option>
			<option value="5">5:00 AM</option>
			<option value="6">6:00 AM</option>
			<option value="7">7:00 AM</option>
			<option value="8">8:00 AM</option>
			<option value="9" selected="selected">9:00 AM</option>
			<option value="10">10:00 AM</option>
			<option value="11">11:00 AM</option>
			<option value="12">12:00 PM</option>
			<option value="13">1:00 PM</option>
			<option value="14">2:00 PM</option>
			<option value="15">3:00 PM</option>
			<option value="16">4:00 PM</option>
			<option value="17">5:00 PM</option>
			<option value="18">6:00 PM</option>
			<option value="19">7:00 PM</option>
			<option value="20">8:00 PM</option>
			<option value="21">9:00 PM</option>
			<option value="22">10:00 PM</option>
			<option value="23">11:00 PM</option>
		</select>
		<select class="ui-widget-content ui-corner-all" name="time_end">
			<option value="24">12:00 AM</option>
			<option value="1">1:00 AM</option>
			<option value="2">2:00 AM</option>
			<option value="3">3:00 AM</option>
			<option value="4">4:00 AM</option>
			<option value="5">5:00 AM</option>
			<option value="6">6:00 AM</option>
			<option value="7">7:00 AM</option>
			<option value="8">8:00 AM</option>
			<option value="9">9:00 AM</option>
			<option value="10">10:00 AM</option>
			<option value="11">11:00 AM</option>
			<option value="12">12:00 PM</option>
			<option value="13">1:00 PM</option>
			<option value="14">2:00 PM</option>
			<option value="15">3:00 PM</option>
			<option value="16">4:00 PM</option>
			<option value="17" selected="selected">5:00 PM</option>
			<option value="18">6:00 PM</option>
			<option value="19">7:00 PM</option>
			<option value="20">8:00 PM</option>
			<option value="21">9:00 PM</option>
			<option value="22">10:00 PM</option>
			<option value="23">11:00 PM</option>
		</select>
	</div>
	<div class="pf-element pf-full-width">
		<span id="p_muid_calendar"></span>
	</div>
	<div class="pf-group">
		<input type="hidden" name="dates" size="24" value="" />
	</div>
	<?php if (isset($this->entity->guid)) { ?>
	<input type="hidden" name="employee" value="<?php echo $this->entity->guid; ?>" />
	<?php } ?>
</form>