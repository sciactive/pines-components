<?php
/**
 * Display a form to quickly create a company schedule.
 * 
 * @package Pines
 * @subpackage com_calendar
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_lineup .form_select {
		width: 90%;
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
				$("#p_muid_lineup [name=shifts]").ptags_add(dateText+'|'+$("#p_muid_lineup [name=shift]").val()+'|'+$("#p_muid_lineup [name=employee]").val());
			}
		});

		$("#p_muid_lineup [name=shifts]").ptags();
	});
// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_lineup" action="<?php echo htmlspecialchars(pines_url('com_calendar', 'savelineup')); ?>">
	<div class="pf-element pf-full-width">
		<select class="ui-widget-content ui-corner-all form_select" name="employee">
			<?php // Load employees for this location.
			foreach ($this->employees as $cur_employee) {
				if (!$cur_employee->in_group($this->location))
					continue;
				echo '<option value="'.$cur_employee->guid.'">'.htmlspecialchars($cur_employee->name).'</option>"';
			} ?>
		</select>
	</div>
	<div class="pf-element pf-full-width">
		<select class="ui-widget-content ui-corner-all form_select" name="shift">
			<?php foreach ($pines->config->com_calendar->lineup_shifts as $cur_shift) {
				$shift = explode('-', $cur_shift);
				$shift_start = format_date(strtotime($shift[0]), 'time_short');
				$shift_end = format_date(strtotime($shift[1]), 'time_short'); ?>
				<option value="<?php echo $cur_shift; ?>"><?php echo $shift_start.' - '.$shift_end; ?></option>
			<?php } ?>
		</select>
	</div>
	<div class="pf-element pf-full-width">
		<span id="p_muid_calendar"></span>
	</div>
	<br class="pf-clearing" />
	<div class="pf-group">
		<input type="hidden" name="location" value="<?php echo $this->location->guid; ?>" />
		<input type="hidden" name="shifts" value="" />
	</div>
</form>