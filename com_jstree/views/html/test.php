<?php
/**
 * A test instance of jsTree.
 *
 * @package Components\jstree
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'jsTree Test';
$pines->com_jstree->load();
?>
<style type="text/css" >
	#p_muid_form {
		padding: 25px;
	}
</style>
<script type='text/javascript'>
	pines(function(){
		// Location Tree
		var location = $("#p_muid_form [name=location]");
		var location_tree = $("#p_muid_form div.location_tree");
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
				"data" : [
					{
						"data": "Root Node 1",
						"attr": {"id": "1"},
						"children": [
							{
								"data": "Node 1-1",
								"attr": {"id": "1-1"}
							},
							{
								"data": "Node 1-2",
								"attr": {"id": "1-2"},
								"children": [
									{
										"data": "Node 1-2-1",
										"attr": {"id": "1-2-1"},
										"children": [
											{
												"data": "Node 1-2-1-1",
												"attr": {"id": "1-2-1-1"}
											},
											{
												"data": "Node 1-2-1-2",
												"attr": {"id": "1-2-1-2"}
											},
											{
												"data": "Node 1-2-1-3",
												"attr": {"id": "1-2-1-3"}
											},
											{
												"data": "Node 1-2-1-4",
												"attr": {"id": "1-2-1-4"}
											}
										]
									},
									{
										"data": "Node 1-2-2",
										"attr": {"id": "1-2-2"},
										"children": [
											{
												"data": "Node 1-2-2-1",
												"attr": {"id": "1-2-2-1"}
											}
										]
									}
								]
							},
							{
								"data": "Node 1-3",
								"attr": {"id": "1-3"},
								"children": [
									{
										"data": "Node 1-3-1",
										"attr": {"id": "1-3-1"}
									}
								]
							}
						]
					},
					{
						"data": "Root Node 2",
						"attr": {"id": "2"},
						"children": [
							{
								"data": "Node 2-1",
								"attr": {"id": "2-1"}
							},
							{
								"data": "Node 2-2",
								"attr": {"id": "2-2"}
							},
							{
								"data": "Node 2-3",
								"attr": {"id": "2-3"},
								"children": [
									{
										"data": "Node 2-3-1",
										"attr": {"id": "2-3-1"}
									}
								]
							}
						]
					}
				]
			},
			"ui" : {
				"select_limit" : 1,
				"initially_select" : ["2-2"]
			}
		});
	});
</script>
<form class="pf-form" method="post" id="p_muid_form" action="">
	<div class="pf-element location_tree"></div>
	<div class="pf-element pf-layout-block">
		<span class="pf-label">Selection</span>
		<input class="pf-field" type="text" name="location" value="" />
	</div>
</form>