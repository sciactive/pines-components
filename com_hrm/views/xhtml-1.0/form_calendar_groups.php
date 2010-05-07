<?php
/**
 * Display a form to view schedules for different company divisions/locations.
 *
 * @package Pines
 * @subpackage com_hrm
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Company Schedule';
$pines->com_jstree->load();
?>
<style type="text/css" >
	/* <![CDATA[ */
	.form_input {
		width: 170px;
		text-align: center;
	}
	/* ]]> */
</style>
<script type='text/javascript'>
	// <![CDATA[
	pines(function(){
		// Location Tree
		var location = $("#report_details [name=location]");
		$("#report_details [name=location_tree]").tree({
			rules : {
				multiple : false
			},
			data : {
				type : "json",
				opts : {
					method : "get",
					url : "<?php echo pines_url('com_hrm', 'groupjson'); ?>"
				}
			},
			selected : ["<?php echo $this->location; ?>"],
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
<form class="pf-form" method="post" id="report_details" action="<?php echo htmlentities(pines_url('com_hrm', 'editcalendar')); ?>">
	<div class="pf-element" name="location_tree"></div>
	<div class="pf-element">
		<input type="hidden" name="location" value="<?php echo $this->location; ?>" />
		<input type="submit" value="View Schedule &raquo;" class="ui-corner-all ui-state-default form_input" />
	</div>
</form>