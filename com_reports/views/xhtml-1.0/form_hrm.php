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
$pines->com_jstree->load();
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_form .form_date {
		width: 80%;
		text-align: center;
	}
	/* ]]> */
</style>
<script type='text/javascript'>
	// <![CDATA[
	pines(function(){
		$("#p_muid_form [name=start], #p_muid_form [name=end]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});
		// Location Tree
		var location = $("#p_muid_form [name=location]");
		$("#p_muid_form .location_tree").tree({
			rules : {
				multiple : false
			},
			data : {
				type : "json",
				opts : {
					method : "get",
					url : "<?php echo pines_url('com_jstree', 'groupjson'); ?>"
				}
			},
			selected : ["<?php echo $this->location->guid; ?>"],
			callback : {
				onchange : function(NODE, TREE_OBJ) {
					location.val(TREE_OBJ.selected.attr("id"));
				},
				check_move: function() {
					return false;
				}
			}
		});
	});
	// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlentities(pines_url('com_reports', 'reportattendance')); ?>">
	<div class="pf-element location_tree" style="padding-bottom: 0px;"></div>
	<div class="pf-element" style="padding-bottom: 0px;">
		<span class="pf-note">Start</span>
		<input class="pf-field ui-widget-content form_date" type="text" name="start" value="<?php echo ($this->date[0]) ? format_date($this->date[0], 'date_sort') : format_date(time(), 'date_sort'); ?>" />
	</div>
	<div class="pf-element">
		<span class="pf-note">End</span>
		<input class="pf-field ui-widget-content form_date" type="text" name="end" value="<?php echo ($this->date[1]) ? format_date($this->date[1], 'date_sort') : format_date(time(), 'date_sort'); ?>" />
	</div>
	<div class="pf-element">
		<input type="hidden" name="location" value="<?php echo $this->location->guid; ?>" />
		<?php if (isset($this->employee)) { ?>
			<input type="hidden" name="employee" value="<?php echo $this->employee->guid; ?>" />
		<?php } ?>
		<input type="submit" value="View Report" class="ui-corner-all ui-state-default" />
	</div>
</form>