<?php
/**
 * Select a location view.
 *
 * @package Components\loan
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Angela Murrell <angela@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines */
defined('P_RUN') or die('Direct access prohibited');
?>
<style type="text/css" >
	#p_muid_form {
		padding-left: 25px;
	}
</style>
<script type='text/javascript'>
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
<form class="pf-form" method="post" id="p_muid_form" action="">
	<div class="pf-element">
		<label><input type="checkbox" name="descendants" value="ON" <?php echo $this->descendants ? 'checked="checked"' : ''; ?> /> Include Descendants</label>
		<input type="hidden" name="location" value="<?php echo htmlspecialchars($this->location); ?>" />
	</div>
	<div class="pf-element location_tree" style="padding-bottom: 5px;"></div>
</form>