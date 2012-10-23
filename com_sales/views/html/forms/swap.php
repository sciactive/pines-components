<?php
/**
 * Display a form to swap inventory on a sale.
 * 
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Zak Huber <zak@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
?>
<form class="pf-form" id="p_muid_form" action="">
	<script type="text/javascript">
		pines(function(){
			$("#p_muid_action").on("click", ".btn", function(){
				if ($(this).hasClass('item-swap')) {
					$("#p_muid_swap").show();
					$("#p_muid_warehouse").hide();
					$("#p_muid_action_input").val('swap');
				} else if ($(this).hasClass('item-remove')) {
					$("#p_muid_swap").hide();
					var item = $("[name=swap_item]:checked", "#p_muid_form");
					if (item.length && !item.hasClass("warehouse"))
						$("#p_muid_warehouse").show();
					else
						$("#p_muid_warehouse").hide();
					$("#p_muid_action_input").val('remove');
				}
			});
			$("#p_muid_form").on("click", "[name=swap_item]", function(){
				var item = $(this);
				if (!item.is(":checked"))
					return;
				$("#p_muid_new_item").empty();
				$("#p_muid_new_item_input").val('');
				if (item.hasClass("serialized")) {
					$(".need-serial", "#p_muid_swap").show();
					$(".need-location", "#p_muid_swap").hide();
				} else {
					$(".need-serial", "#p_muid_swap").hide().filter("[name=new_serial]").val("");
					$(".need-location", "#p_muid_swap").show();
				}
				if (item.hasClass("shipped"))
					$("#p_muid_shipped").show();
				else
					$("#p_muid_shipped").hide();
				if (!item.hasClass("warehouse") && $(".item-remove", "#p_muid_action").hasClass("active"))
					$("#p_muid_warehouse").show();
				else
					$("#p_muid_warehouse").hide();
			}).on("change keypress", "[name=new_serial]", function(e){
				if (e.type == 'keypress') {
					if (e.keyCode == 13) {
						e.preventDefault();
						e.stopPropagation();
					} else
						return;
				}
				new_item($(this).val());
			});

			var new_item = function(serial, location){
				var display = $("#p_muid_new_item"),
					input = $("#p_muid_new_item_input"),
					item = $("[name=swap_item]:checked", "#p_muid_form"),
					product = item.attr("data-product"),
					old_guid = item.attr("data-guid");
				display.empty();
				input.val('');
				if (!item.length) {
					display.html('<div class="alert alert-danger" style="margin-bottom: 0;">Please select an item first.</div>');
					return;
				}
				$.ajax({
					url: <?php echo json_encode(pines_url('com_sales', 'stock/search')); ?>,
					type: "POST",
					dataType: "json",
					data: {"not_guids": JSON.stringify([old_guid]), "product": product, "serial": serial, "location": location, "quantity": 1},
					beforeSend: function(){
						display.html('<div class="picon-throbber" style="width: 16px; height: 16px;"></div>');
					},
					complete: function(){
						display.children('.picon-throbber').remove();
					},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to lookup the stock:\n"+pines.safe(XMLHttpRequest.status)+": "+pines.safe(textStatus));
					},
					success: function(data){
						if (!data) {
							alert("An error occured. Please try again.");
							return;
						}
						if (data.length == 0) {
							display.html('<div class="alert alert-danger" style="margin-bottom: 0;">No stock was found that matched your query.</div>');
							return;
						}
						$.each(data, function(i, entry){
							display.html('<div class="well entry"><div>Stock Entry: <a data-entity="'+pines.safe(entry.guid)+'" data-entity-context="com_sales_stock" class="guid">'+pines.safe(entry.guid)+'</a></div><div>Location: <a data-entity="'+pines.safe(entry.location)+'" data-entity-context="group">'+pines.safe(entry.location_name)+'</a></div><div'+(entry.serial ? '' : ' style="display: none"')+'>Serial: <span class="serial">'+(entry.serial ? pines.safe(entry.serial) : '')+'</span></div></div>');
							input.val(entry.guid);
						});
					}
				});
			};


			// Location Tree
			var location_tree = $("#p_muid_swap div.location_tree");
			location_tree
			.bind("select_node.jstree", function(e, data){
				new_item('', data.inst.get_selected().attr("id").replace("p_muid_", ""));
			})
			.bind("before.jstree", function (e, data){
				if (data.func == "parse_json" && "args" in data && 0 in data.args && "attr" in data.args[0] && "id" in data.args[0].attr)
					data.args[0].attr.id = "p_muid_"+data.args[0].attr.id;
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
					"select_limit" : 1
				}
			});
		});
	</script>
	<div class="pf-element pf-heading">
		<div style="float: right;">
			<a data-entity="<?php echo htmlspecialchars($this->entity->guid); ?>" data-entity-context="com_sales_sale">Sale <?php echo htmlspecialchars($this->entity->id); ?></a>
		</div>
		<h3>Item to Swap/Remove</h3>
	</div>
	<?php foreach ($this->entity->products as $key => $cur_product) {
		if (!$cur_product['stock_entities'])
			continue; ?>
	<div class="pf-element">
		<span class="pf-label"><a data-entity="<?php echo htmlspecialchars($cur_product['entity']->guid); ?>" data-entity-context="com_sales_product"><?php echo htmlspecialchars("{$cur_product['entity']->name} [{$cur_product['entity']->sku}]"); ?></a></span>
		<div class="pf-group">
			<div class="pf-field">
				<?php foreach ($cur_product['stock_entities'] as $cur_stock) {
					if (!isset($cur_stock->guid) || $cur_stock->in_array((array) $cur_product['returned_stock_entities']))
						continue;
					$classes = '';
					if ($cur_product['delivery'] == 'warehouse')
						$classes .= 'warehouse';
					if ($cur_stock->in_array((array) $cur_product['shipped_entities']))
						$classes .= ' shipped';
					if ($cur_product['entity']->serialized)
						$classes .= ' serialized';
					?>
				<label style="display: block;"><input type="radio" name="swap_item" data-product="<?php echo htmlspecialchars($cur_product['entity']->guid); ?>" data-guid="<?php echo htmlspecialchars($cur_stock->guid); ?>" value="<?php echo htmlspecialchars($key.'_'.$cur_stock->guid); ?>" class="<?php echo $classes; ?>" /> <a data-entity="<?php echo htmlspecialchars($cur_stock->guid); ?>" data-entity-context="com_sales_stock">Entry <?php echo htmlspecialchars($cur_stock->guid . (!empty($cur_stock->serial) ? " (Serial: {$cur_stock->serial})" : '')); ?></a></label>
				<?php } ?>
			</div>
		</div>
	</div>
	<?php } ?>
	<div class="pf-element pf-heading">
		<h3>Action to Perform</h3>
	</div>
	<div class="pf-element" id="p_muid_action">
		<div class="btn-group" data-toggle="buttons-radio">
			<button type="button" class="btn item-swap active">Swap</button>
			<button type="button" class="btn item-remove">Remove</button>
		</div>
		<input id="p_muid_action_input" type="hidden" name="item_action" value="swap" />
	</div>
	<div class="pf-element pf-full-width" id="p_muid_swap">
		<style type="text/css" scoped="scoped">
			#p_muid_new_item .entry {
				float: left;
				font-size: .8em;
				padding: .5em;
				margin: 0;
			}
			#p_muid_new_item .entry > div {
				margin-right: 2em;
			}
		</style>
		<span class="pf-label need-serial" style="display: none;">New Item Serial</span>
		<span class="pf-label need-location">New Item Location</span>
		<div class="pf-group">
			<input class="pf-field need-serial" style="display: none;" type="text" name="new_serial" value="" />
			<div class="pf-field need-location location_tree"></div>
			<div class="pf-field" id="p_muid_new_item" style="padding-top: .5em;"></div>
		</div>
		<input id="p_muid_new_item_input" type="hidden" name="new_item" value="" />
	</div>
	<div class="pf-element" id="p_muid_warehouse" style="display: none; padding-bottom: 0;">
		<div class="alert" style="margin-bottom: 0;">
			The selected product will be converted to a warehouse order. Any
			other stock entries on that product will then be marked as shipped.
		</div>
	</div>
	<div class="pf-element" id="p_muid_shipped" style="display: none; padding-bottom: 0;">
		<div class="alert" style="margin-bottom: 0;">
			The selected item has been shipped. If you swap it, the new item
			will be marked as shipped. However, the shipment will still reflect
			the old item.
		</div>
	</div>
</form>