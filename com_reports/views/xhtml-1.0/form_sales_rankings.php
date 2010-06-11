<?php
/**
 * Display a form to view sales rankings by location.
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
	#report_details .form_date {
		width: 80%;
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
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});
		// Loction Tree
		var location = $("#report_details [name=location]");
		$("#report_details .location_tree").tree({
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
<form class="pf-form" method="post" id="report_details" action="<?php echo htmlentities(pines_url('com_reports', 'viewsalesranking')); ?>">
	<div class="pf-element location_tree"></div>
	<div class="pf-element">
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<input type="hidden" name="location" />
		<input class="ui-corner-all ui-state-default" type="submit" value="View Report" />
	</div>
</form>