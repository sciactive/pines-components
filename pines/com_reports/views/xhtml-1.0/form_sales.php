<?php
/**
 * Display a form to view sales reports.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zhuber@gmail.com>
 * @copyright Zak Huber
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'New Report';
?>
<style type="text/css" >
	/* <![CDATA[ */
	.form_input {
		width: 170px;
		text-align: center;
	}
	.form_date {
		width: 98%;
		text-align: center;
	}
	/* ]]> */
</style>
<script type='text/javascript'>
// <![CDATA[
	$(function(){
		$("#report_details [name=start], #report_details [name=end]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true
		});
	});
// ]]>
</script>
<form class="pf-form" method="post" id="report_details" action="<?php echo htmlentities(pines_url('com_reports', 'reportsales')); ?>">
	<div class="pf-element" style="padding-bottom: 0px;">
		<span class="pf-note">Start</span>
		<input class="ui-corner-all form_date" type="text" name="start" value="<?php echo ($this->date[0]) ? pines_date_format($this->date[0], null, 'Y-m-d') : pines_date_format(time(), null, 'Y-m-d'); ?>" />
	</div>
	<div class="pf-element">
		<span class="pf-note">End</span>
		<input class="ui-corner-all form_date" type="text" name="end" value="<?php echo ($this->date[1]) ? pines_date_format($this->date[1], null, 'Y-m-d') : pines_date_format(time(), null, 'Y-m-d'); ?>" />
	</div>
	<div class="pf-element">
		<input type="submit" value="View Report &raquo;" class="ui-corner-all form_input" />
	</div>
</form>