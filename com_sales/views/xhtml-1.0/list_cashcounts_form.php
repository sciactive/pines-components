<?php
/**
 * Display a form to filter cash counts by location and date.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');

$this->title = 'Location & Date';
?>
<style type="text/css" >
	/* <![CDATA[ */
	#cashcount_dates .form_input {
		width: 170px;
		text-align: center;
	}
	#cashcount_dates .form_date {
		width: 85%;
		text-align: center;
	}
	/* ]]> */
</style>
<script type='text/javascript'>
	// <![CDATA[
	pines(function(){
		$("#cashcount_dates [name=start_date], #cashcount_dates [name=end_date]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true
		});
		// Location Tree
		var location = $("#cashcount_dates [name=location]");
		$("#cashcount_dates div.location_tree").tree({
			rules : {
				multiple : false
			},
			data : {
				type : "json",
				opts : {
					method : "get",
					url : "<?php echo pines_url('com_sales', 'groupjson'); ?>"
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
<form class="pf-form" method="post" id="cashcount_dates" action="<?php echo htmlentities(pines_url('com_sales', 'listcashcounts')); ?>">
	<div class="pf-element location_tree" style="padding-bottom: 0px;"></div>
	<div class="pf-element" style="padding-bottom: 0px;">
		<span class="pf-note">Start</span>
		<input class="pf-field ui-corner-all ui-widget-content form_date" type="text" name="start_date" value="<?php echo ($this->start_date) ? format_date($this->start_date, null, 'Y-m-d') : format_date(time(), null, 'Y-m-d'); ?>" />
	</div>
	<div class="pf-element">
		<span class="pf-note">End</span>
		<input class="pf-field ui-corner-all ui-widget-content form_date" type="text" name="end_date" value="<?php echo ($this->end_date) ? format_date($this->end_date, null, 'Y-m-d') : format_date(time(), null, 'Y-m-d'); ?>" />
	</div>
	<div class="pf-element">
		<input type="hidden" name="location" />
		<input type="submit" value="Update &raquo;" class="ui-corner-all ui-state-default form_input" />
	</div>
</form>