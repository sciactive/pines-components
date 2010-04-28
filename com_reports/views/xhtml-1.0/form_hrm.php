<?php
/**
 * Display a form to view sales reports.
 *
 * @package Pines
 * @subpackage com_reports
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
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
		width: 85%;
		text-align: center;
	}
	/* ]]> */
</style>
<script type='text/javascript'>
	// <![CDATA[
	pines(function(){
		$("#report_details [name=start], #report_details [name=end]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true
		});
		// Category Tree
		var location = $("#location");
		$("#location_tree").tree({
			rules : {
				multiple : false
			},
			data : {
				type : "json",
				opts : {
					method : "get",
					url : "<?php echo pines_url('com_reports', 'groupjson'); ?>"
				}
			},
			selected : ["<?php echo $this->location->guid; ?>"],
			callback : {
				onchange : function(NODE, TREE_OBJ) {
					location.val(TREE_OBJ.selected.attr("id"));
				},
				check_move: function(NODE, REF_NODE, TYPE, TREE_OBJ) {
					return false;
				}
			}
		});
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="report_details" action="<?php echo htmlentities(pines_url('com_reports', 'reportattendance')); ?>">
	<div class="pf-element" style="padding-bottom: 0px;" id="location_tree"></div>
	<div class="pf-element" style="padding-bottom: 0px;">
		<span class="pf-note">Start</span>
		<input class="pf-field ui-widget-content form_date" type="text" name="start" value="<?php echo ($this->date[0]) ? format_date($this->date[0], 'date_sort') : format_date(time(), 'date_sort'); ?>" />
	</div>
	<div class="pf-element">
		<span class="pf-note">End</span>
		<input class="pf-field ui-widget-content form_date" type="text" name="end" value="<?php echo ($this->date[1]) ? format_date($this->date[1], 'date_sort') : format_date(time(), 'date_sort'); ?>" />
	</div>
	<div class="pf-element">
		<input type="hidden" name="location" id="location" />
		<input type="submit" value="View Report &raquo;" class="ui-corner-all ui-state-default form_input" />
	</div>
</form>