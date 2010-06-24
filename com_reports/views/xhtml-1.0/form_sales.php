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
	.form_date {
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
			selected : ["<?php echo $this->location; ?>"],
			callback : {
				onchange : function(NODE, TREE_OBJ) {
					location.val(TREE_OBJ.selected.attr("id"));
					update_employees(TREE_OBJ.selected.attr("id"));
				},
				check_move: function() {
					return false;
				}
			}
		});
	});
	
	// This function reloads the employees when switching between locations.
	function update_employees(group_id) {
		var employee = $("#p_muid_form [name=employee]");
		employee.empty();
		employee.append("<option value='all' selected='selected'>Entire Location</option>");
		<?php foreach ($this->employees as $cur_employee) { // Load employees for the current location.
			if (!isset($cur_employee->group))
				continue;
			$cur_select = (isset($this->employee->group) && $this->employee->is($cur_employee)) ? 'selected=\"selected\"' : ''; ?>
			if (group_id == <?php echo $cur_employee->group->guid; ?>) {
				employee.append("<option value='<?php echo $cur_employee->guid; ?>' <?php echo $cur_select; ?>><?php echo $cur_employee->name; ?></option>");
			}
		<?php } ?>
	}
// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlentities(pines_url('com_reports', 'reportsales')); ?>">
	<div class="pf-element location_tree"></div>
	<div class="pf-element">
		<select class="ui-widget-content" style="width: 100%;" name="employee"></select>
	</div>
	<div class="pf-element" style="padding-bottom: 0px;">
		<span class="pf-note">Start</span>
		<input class="ui-widget-content form_date" type="text" name="start" value="<?php echo ($this->date[0]) ? format_date($this->date[0], 'date_sort') : format_date(time(), 'date_sort'); ?>" />
	</div>
	<div class="pf-element">
		<span class="pf-note">End</span>
		<input class="ui-widget-content form_date" type="text" name="end" value="<?php echo ($this->date[1]) ? format_date($this->date[1], 'date_sort') : format_date(time(), 'date_sort'); ?>" />
	</div>
	<div class="pf-element">
		<input type="hidden" name="location" value="<?php echo $this->location; ?>" />
		<input class="ui-corner-all ui-state-default" type="submit" value="View Report" />
	</div>
</form>