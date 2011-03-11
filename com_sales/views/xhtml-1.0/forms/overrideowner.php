<?php
/**
 * Display a form to override the user/location a sale.
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
<script type='text/javascript'>
// <![CDATA[
	pines(function(){

		// Location Tree
		var location = $("#p_muid_form [name=location]");
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
				"initially_select" : ["<?php echo $this->entity->group->guid; ?>"]
			}
		});
	});
// ]]>
</script>
<form class="pf-form" id="p_muid_form" action="">
	<div class="pf-element pf-heading">
		<h1>User</h1>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Override User</span>
			<input class="pf-field ui-widget-content ui-corner-all salesperson_box" type="text" name="user" value="<?php echo isset($this->entity->user->guid) ? htmlspecialchars("{$this->entity->user->guid}: {$this->entity->user->name}") : ''; ?>" /></label>
	</div>
	<div class="pf-element pf-heading">
		<h1>Location</h1>
	</div>
	<div class="pf-element">
		<span class="pf-note">Override Location</span>
		<div class="pf-group">
			<div class="pf-field location_tree"></div>
		</div>
		<input type="hidden" name="location" value="<?php echo isset($this->entity->group->guid) ? $this->entity->group->guid : ''; ?>" />
	</div>
</form>