<?php
/**
 * Display a form to filter cash counts by location and date.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Location &amp; Date';
$pines->com_jstree->load();
?>
<style type="text/css" >
	#p_muid_form .form_date {
		width: 80%;
		text-align: center;
	}
</style>
<script type='text/javascript'>
	pines(function(){
		$("#p_muid_form [name=start_date], #p_muid_form [name=end_date]").datepicker({
			dateFormat: "yy-mm-dd",
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true
		});
		// Location Tree
		var location = $("#p_muid_form [name=location]");
		$("#p_muid_form .location_tree")
		.bind("select_node.jstree", function(e, data){
			location.val(data.inst.get_selected().attr("id").replace("p_muid_", ""));
		})
		.bind("before.jstree", function (e, data){
			if (data.func == "parse_json" && "args" in data && 0 in data.args && "attr" in data.args[0] && "id" in data.args[0].attr)
				data.args[0].attr.id = "p_muid_"+data.args[0].attr.id;
		})
		.bind("loaded.jstree", function(e, data){
			var path = data.inst.get_path("#"+data.inst.get_settings().ui.initially_select, true);
			if (!path.length) return;
			data.inst.open_node("#"+path.join(", #"), false, true);
		})
		.jstree({
			"plugins" : [ "themes", "json_data", "ui" ],
			"json_data" : {
				"ajax" : {
					"dataType" : "json",
					"url" : <?php echo json_encode(pines_url('com_jstree', 'groupjson')); ?>
				}
			},
			"ui" : {
				"select_limit" : 1,
				"initially_select" : ["<?php echo (int) $this->location; ?>"]
			}
		});
	});
</script>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_sales', 'cashcount/list')); ?>">
	<div class="pf-element location_tree" style="padding-bottom: 0px;"></div>
	<div class="pf-element" style="padding-bottom: 0px;">
		<span class="pf-note">Start</span>
		<input class="pf-field form_date" type="text" name="start_date" value="<?php echo ($this->start_date) ? htmlspecialchars(format_date($this->start_date, 'date_sort')) : htmlspecialchars(format_date(time(), 'date_sort')); ?>" />
	</div>
	<div class="pf-element">
		<span class="pf-note">End</span>
		<input class="pf-field form_date" type="text" name="end_date" value="<?php echo ($this->end_date) ? htmlspecialchars(format_date($this->end_date - 1, 'date_sort')) : htmlspecialchars(format_date(time(), 'date_sort')); ?>" />
	</div>
	<div class="pf-element">
		<input type="hidden" name="location" />
		<?php if ($this->finished) { ?>
		<input type="hidden" name="finished" value="true" />
		<?php } ?>
		<input type="submit" value="Update" class="btn btn-primary" />
	</div>
</form>