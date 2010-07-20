<?php
/**
 * Display a form to select a location.
 * 
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
?>
<style type="text/css" >
	/* <![CDATA[ */
	#p_muid_form {
		padding-left: 25px;
	}
	/* ]]> */
</style>
<script type='text/javascript'>
// <![CDATA[
	pines(function(){

		// Location Tree
		var location = $("#p_muid_form [name=location]");
		var location_saver = $("#p_muid_form [name=location_saver]");
		var location_tree = $("#p_muid_form div.location_tree");
		//var block_change = function() {
		//	if (location_saver.val() != 'individual')
		//		return false;
		//};
		location_tree
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
					"url" : "<?php echo addslashes(pines_url('com_jstree', 'groupjson')); ?>"
				}
			},
			"ui" : {
				"select_limit" : 1,
				"initially_select" : ["<?php echo ($this->location == 'all') ? (int) $_SESSION['user']->group->guid : (int) $this->location; ?>"]
			}
		});
		// TODO: How to recreate these?
		//	callback : {
		//		// The tree is disabled when searching all locations.
		//		beforechange : block_change,
		//		beforeclose : block_change,
		//		beforeopen : block_change,

		$("#p_muid_form [name=all_groups]").change(function(){
			var all_groups = $(this);
			if (all_groups.is(":checked") && all_groups.val() == "individual") {
				location_tree.removeClass("ui-priority-secondary");
				location_saver.val('individual');
			} else if (all_groups.is(":checked") && all_groups.val() == "allGroups") {
				location_tree.addClass("ui-priority-secondary");
				location_saver.val('all');
			}
		}).change();
	});
// ]]>
</script>
<form class="pf-form" method="post" id="p_muid_form" action="">
	<div class="pf-element">
		<label><input class="pf-field ui-widget-content" type="radio" name="all_groups" value="allGroups" checked="checked" />All Locations</label>
		<label><input class="pf-field ui-widget-content" type="radio" name="all_groups" value="individual" <?php echo ($this->location != 'all') ? 'checked="checked"' : ''; ?>/>Single Location</label>
	</div>
	<div class="pf-element location_tree" style="padding-bottom: 5px;"></div>
	<div class="pf-element">
		<input type="hidden" name="location" value="<?php echo htmlentities($this->location); ?>" />
		<input type="hidden" name="location_saver" value="<?php echo ($this->location == 'all') ? 'all' : 'individual'; ?>" />
	</div>
</form>