<?php
/**
 * Display a form to view sales reports.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
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
		$("#report_start").datepicker({
			dateFormat: "m/d/yy",
			changeMonth: true,
			changeYear: true
		});
	});
	$(function(){
		$("#report_end").datepicker({
			dateFormat: "m/d/yy",
			changeMonth: true,
			changeYear: true
		});
	});
// ]]>
</script>
<form class="pform" method="post" id="report_details" action="<?php echo pines_url('com_reports', 'reportsales'); ?>">
	<div class="element" style="padding-bottom: 0px;">
		<span class="note">Start</span>
		<input class="ui-corner-all form_date" type="text" id="report_start" name="report_start" value="<?php echo ($this->date[0]) ? date('n/j/Y', $this->date[0]) : date('n/j/Y'); ?>" />
	</div>
	<div class="element">
		<span class="note">End</span>
		<input class="ui-corner-all form_date" type="text" id="report_end" name="report_end" value="<?php echo ($this->date[1]) ? date('n/j/Y', $this->date[1]) : date('n/j/Y'); ?>" />
	</div>
	<div class="element">
			<input type="submit" value="View Report &raquo;" class="ui-corner-all form_input" />
	</div>
</form>