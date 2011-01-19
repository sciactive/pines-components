<?php
/**
 * Provides a form for the user to fulfill a warehouse order.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Fulfilling Sale ['.htmlspecialchars($this->entity->id).']';
$this->note = 'Provide stock selection in this form.';
$pines->uploader->load();
$pines->com_jstree->load();
$warehouse = group::factory($pines->config->com_sales->warehouse_group);
?>
<form class="pf-form" method="post" action="<?php echo htmlspecialchars(pines_url('com_sales', 'warehouse/save')); ?>">
	<?php if (isset($this->entity->guid)) { ?>
	<div class="date_info" style="float: right; text-align: right;">
		<?php if (isset($this->entity->user)) { ?>
		<div>User: <span class="date"><?php echo htmlspecialchars("{$this->entity->user->name} [{$this->entity->user->username}]"); ?></span></div>
		<div>Group: <span class="date"><?php echo htmlspecialchars("{$this->entity->group->name} [{$this->entity->group->groupname}]"); ?></span></div>
		<?php } ?>
		<div>Created: <span class="date"><?php echo format_date($this->entity->p_cdate, 'full_short'); ?></span></div>
		<div>Modified: <span class="date"><?php echo format_date($this->entity->p_mdate, 'full_short'); ?></span></div>
	</div>
	<?php } ?>
	<style type="text/css">
		/* <![CDATA[ */
		#p_muid_products .entry {
			padding: .4em;
			float: left;
			margin: 0 .4em .4em 0;
		}
		#p_muid_products .entry a.remove {
			padding: .2em;
			float: right;
		}
		/* ]]> */
	</style>
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			var current_item;
			
			$("#p_muid_products").delegate("div.serial a.fulfill", "click", function(){
				current_item = $(this).closest("div.serial");
				serial_dialog.dialog("open");
			}).delegate("div.nonserial a.fulfill", "click", function(){
				current_item = $(this).closest("div.nonserial");
				quantity_box.val(current_item.find(".qty_left").text());
				location_dialog.dialog("open");
			}).delegate(".entry a.remove", "hover", function(){
				$(this).toggleClass("ui-state-hover");
				refresh_entries();
			}).delegate(".entry a.remove", "click", function(){
				$(this).closest(".entry").remove();
				refresh_entries();
			});

			var add_entry = function(serial, location, quantity){
				var loader;
				$.ajax({
					url: "<?php echo addslashes(pines_url('com_sales', 'stock/search')); ?>",
					type: "POST",
					dataType: "json",
					data: {"product": current_item.children(".product").text(), "serial": serial, "location": location, "quantity": quantity},
					beforeSend: function(){
						loader = $.pnotify({
							pnotify_title: 'Stock Search',
							pnotify_text: 'Retrieving stock from server...',
							pnotify_notice_icon: 'picon picon-throbber',
							pnotify_nonblock: true,
							pnotify_hide: false,
							pnotify_history: false
						});
					},
					complete: function(){
						loader.pnotify_remove();
					},
					error: function(XMLHttpRequest, textStatus){
						pines.error("An error occured while trying to lookup the stock:\n"+XMLHttpRequest.status+": "+textStatus);
					},
					success: function(data){
						if (!data) {
							alert("An error occured. Please try again.");
							return;
						}
						if (data.length == 0) {
							alert("No stock was found that matched your query.");
							return;
						}
						if (data.length < quantity)
							alert("Only "+data.length+" items were found that matched your query.");
						var entries = current_item.find(".entries");
						$.each(data, function(i, entry){
							entries.append("<div class=\"ui-widget-content ui-corner-all entry\"><a href=\"javascript:void(0);\" class=\"remove ui-state-default ui-corner-all\">X</a><div>Location: "+entry.location_name+"</div><span class=\"location\" style=\"display: none\">"+entry.location+"</span><span class=\"product\" style=\"display: none\">"+entry.product+"</span><div"+(entry.serial ? "" : " style=\"display: none\"")+">Serial: <span class=\"serial\">"+(entry.serial ? entry.serial : "")+"</span></div></div>");
						});
						refresh_entries();
					}
				});
			};

			var refresh_entries = function(){
				var entries = [];
				$("#p_muid_products").children(".product_entry").each(function(){
					var cur_product = $(this);
					var cur_entries = cur_product.find(".entries .entry");
					var key = cur_product.children(".key").text();
					// Update the quantity left.
					var qty_left = parseInt(cur_product.find(".qty").text()) - (parseInt(cur_product.find(".qty_done").text()) + cur_entries.length);
					cur_product.find(".qty_left").html(qty_left);
					if (qty_left < 1)
						cur_product.find(".fulfill").hide();
					else
						cur_product.find(".fulfill").show();
					cur_entries.each(function(){
						var cur_entry = $(this);
						entries.push({
							"key": key,
							"location": cur_entry.find(".location").text(),
							"product": cur_entry.find(".product").text(),
							"serial": cur_entry.find(".serial").text()
						});
					})
				});
				$("#p_muid_products_input").val(JSON.stringify(entries));
			};
			refresh_entries();

			var serial_dialog = $("#p_muid_serial_dialog").dialog({
				bgiframe: true,
				autoOpen: false,
				width: 450,
				modal: true,
				buttons: {
					"Done": function(){
						var serial = serial_box.val();
						if (serial == "") {
							alert("Please provide a serial number.");
							return;
						}
						add_entry(serial, null, 1);
						serial_dialog.dialog("close");
					}
				},
				close: function(){
					serial_box.val("");
				},
				open: function(){
					serial_box.focus().select();
				}
			});
			var serial_box = $("#p_muid_serial_number").keypress(function(e){
				if (e.keyCode == 13) {
					serial_dialog.dialog("option", "buttons").Done();
					return false;
				}
			});

			//var location_rollback;
			var location_dialog = $("#p_muid_location_dialog").dialog({
				bgiframe: true,
				autoOpen: false,
				width: 450,
				modal: true,
				buttons: {
					"Done": function(){
						var quantity = quantity_box.val();
						if (quantity == "") {
							alert("Please provide a quantity.");
							return;
						}
						quantity = parseInt(quantity);
						if (isNaN(quantity) || quantity < 1) {
							alert("Given quantity is invalid.");
							return;
						}
						var qty_left = parseInt(current_item.find(".qty_left").text());
						if (quantity > qty_left) {
							alert("Given quantity is too high. Only "+qty_left+" left to be fulfilled.");
							return;
						}
						add_entry("", location.val(), quantity);
						location_dialog.dialog("close");
					}
				},
				// This works, but it makes selecting the same node again more difficult.
				//close: function(){
				//	$.jstree.rollback(location_rollback);
				//},
				open: function(){
					//location_rollback = location_tree.jstree("get_rollback");
					location_tree.jstree("reopen");
					location_tree.jstree("set_focus");
				}
			});
			var quantity_box = $("#p_muid_quantity").keypress(function(e){
				if (e.keyCode == 13) {
					location_dialog.dialog("option", "buttons").Done();
					return false;
				}
			});
			// Location Tree
			var location = $("#p_muid_location_dialog [name=location]");
			var location_tree = $("#p_muid_location_dialog div.location_tree");
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
					"initially_select" : ["<?php echo (int) $warehouse->guid; ?>"]
				}
			});
		});
		// ]]>
	</script>
	<div class="pf-element">
		<span class="pf-label">Ship To</span>
		<div class="pf-group">
			<div class="pf-field">
				<div><strong><?php echo htmlspecialchars($this->entity->shipping_address->name); ?></strong></div>
				<?php if ($this->entity->shipping_address->address_type == 'us') { if (!empty($this->entity->shipping_address->address_1)) { ?>
				<div><?php echo htmlspecialchars($this->entity->shipping_address->address_1.' '.$this->entity->shipping_address->address_2); ?></div>
				<div><?php echo htmlspecialchars($this->entity->shipping_address->city); ?>, <?php echo htmlspecialchars($this->entity->shipping_address->state); ?> <?php echo htmlspecialchars($this->entity->shipping_address->zip); ?></div>
				<?php } } else {?>
				<pre><?php echo htmlspecialchars($this->entity->shipping_address->address_international); ?></pre>
				<?php } ?>
			</div>
		</div>
	</div>
	<div id="p_muid_products">
		<?php foreach ($this->entity->products as $cur_key => $cur_product) {
			if ($cur_product['delivery'] != 'warehouse')
				continue;
			// Is the product returned?
			if ((int) $cur_product['returned_quantity'] >= $cur_product['quantity'])
				continue;
			// Calculate quantity.
			$quantity = $cur_product['quantity'] - (int) $cur_product['returned_quantity'];
			// Calculate fulfilled.
			$fulfilled = count($cur_product['stock_entities']) - count((array) $cur_product['returned_stock_entities']);
			?>
		<div class="pf-element product_entry <?php echo $cur_product['entity']->serialized ? 'serial' : 'nonserial'; ?>">
			<span class="pf-label"><?php echo htmlspecialchars($cur_product['entity']->name); ?> <small>x <span class="qty"><?php echo htmlspecialchars($quantity); ?></span><span class="qty_left" style="display: none;"><?php echo $quantity - $fulfilled; ?></span> (<span class="qty_done"><?php echo $fulfilled; ?></span> already fulfilled)</small></span>
			<span class="pf-note">SKU: <?php echo htmlspecialchars($cur_product['entity']->sku); ?>, <?php echo $cur_product['entity']->serialized ? 'Serialized' : 'Non-Serialized'; ?></span>
			<a href="javascript:void(0);" class="pf-field fulfill">Fulfill</a>
			<div class="pf-group">
				<div class="pf-field">
					<div class="entries"></div>
					<br class="pf-clearing" />
				</div>
			</div>
			<div class="product" style="display: none;"><?php echo htmlspecialchars($cur_product['entity']->guid); ?></div>
			<div class="key" style="display: none;"><?php echo htmlspecialchars($cur_key); ?></div>
		</div>
		<?php } ?>
		<input type="hidden" name="products" id="p_muid_products_input" value="[]" />
	</div>
	<div id="p_muid_serial_dialog" title="Provide Serial" style="display: none;">
		<div class="pf-form">
			<div class="pf-element">
				<label><span class="pf-label">Serial Number</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" id="p_muid_serial_number" name="serial_number" size="24" value="" /></label>
			</div>
		</div>
		<br />
	</div>
	<div id="p_muid_location_dialog" title="Pick Location" style="display: none;">
		<div class="pf-form">
			<div class="pf-element">
				<span class="pf-label">Location</span>
				<div class="pf-group">
					<div class="pf-field location_tree"></div>
				</div>
			</div>
			<div class="pf-element">
				<label><span class="pf-label">Quantity</span>
					<input class="pf-field ui-widget-content ui-corner-all" type="text" id="p_muid_quantity" name="quantity" size="5" value="" /></label>
			</div>
			<input type="hidden" name="location" value="<?php echo htmlspecialchars($warehouse->guid); ?>" />
		</div>
		<br />
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" value="Submit" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_sales', 'warehouse/fulfill')); ?>');" value="Cancel" />
	</div>
</form>