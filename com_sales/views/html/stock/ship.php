<?php
/**
 * Provides a form for the user to edit a shipment.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Editing Shipment ['.htmlspecialchars($this->entity->guid).']';
$this->note = 'Provide shipment details in this form.';
?>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_sales', 'stock/saveship')); ?>">
	<style type="text/css">
		/* <![CDATA[ */
		#p_muid_packing_list .number_box {
			cursor: pointer;
		}
		/* ]]> */
	</style>
	<script type="text/javascript">
		// <![CDATA[
		pines(function(){
			var packing_list = $("#p_muid_packing_list");
			var packing_list_input = packing_list.children("input[name=packing_list]");

			packing_list.delegate(".item_box", "mouseenter", function(){
				var number_box = $(this).find(".number_box");
				number_box.addClass("ui-state-default").data("text", number_box.html()).html("&nbsp;X&nbsp;");
			}).delegate(".item_box", "mouseleave", function(){
				var number_box = $(this).find(".number_box");
				number_box.removeClass("ui-state-default").html(number_box.data("text"));
			}).delegate(".number_box", "mouseenter", function(){
				$(this).addClass("ui-state-hover");
			}).delegate(".number_box", "mouseleave", function(){
				$(this).removeClass("ui-state-hover");
			}).delegate(".number_box", "click", function(){
				var item_box = $(this).closest(".item_box");
				var product = item_box.closest(".product");
				var ship_qty = product.find(".ship_quantity");
				var cur_qty = parseInt(ship_qty.text());
				if (cur_qty == 1) {
					if (product.siblings(".product").length == 0) {
						alert("This is the last product on the packing list. If it is removed, there will be nothing to ship.");
						return;
					}
					product.slideUp("fast", function(){
						$(this).remove();
						update_packing_list();
					});
				} else {
					item_box.slideUp("fast", function(){
						$(this).remove();
						update_packing_list();
					});
				}
			});

			var update_packing_list = function(){
				var new_value = {};
				packing_list.find(".product").each(function(){
					var product = $(this);
					var key = product.children(".key").text();
					var ship_qty = product.find(".ship_quantity");
					var items = product.find(".item_box");
					ship_qty.html(items.length);
					var stock_entries = [];
					items.each(function(){
						stock_entries.push($(this).children(".key").text());
					});
					new_value[key] = stock_entries;
				});

				packing_list_input.val(JSON.stringify(new_value));
			};

			update_packing_list();
		});
		// ]]>
	</script>
	<div class="pf-element">
		<span class="pf-label">Shipping Address</span>
		<div class="pf-group">
			<div class="pf-field">
				<strong><?php echo htmlspecialchars($this->entity->shipping_address->name); ?></strong><br />
				<?php if ($this->entity->shipping_address->address_type == 'us') { if (!empty($this->entity->shipping_address->address_1)) { ?>
				<?php echo htmlspecialchars($this->entity->shipping_address->address_1.' '.$this->entity->shipping_address->address_2); ?><br />
				<?php echo htmlspecialchars($this->entity->shipping_address->city); ?>, <?php echo htmlspecialchars($this->entity->shipping_address->state); ?> <?php echo htmlspecialchars($this->entity->shipping_address->zip); ?>
				<?php } } else { ?>
				<?php echo str_replace("\n", '<br />', htmlspecialchars($this->entity->shipping_address->address_international)); ?>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Shipper</span>
			<select class="pf-field ui-widget-content ui-corner-all" name="shipper">
				<option value="null">-- None --</option>
				<?php foreach ($this->shippers as $cur_shipper) { ?>
				<option value="<?php echo $cur_shipper->guid; ?>"<?php echo $this->entity->shipper->guid == $cur_shipper->guid ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_shipper->name); ?></option>
				<?php } ?>
			</select></label>
	</div>
	<div class="pf-element">
		<?php if (!$this->entity->final) { ?>
		<script type="text/javascript">
			// <![CDATA[
			pines(function(){
				$("#p_muid_eta").datepicker({
					dateFormat: "yy-mm-dd",
					showOtherMonths: true,
					selectOtherMonths: true
				});
			});
			// ]]>
		</script>
		<?php } ?>
		<label><span class="pf-label">ETA</span>
			<input class="pf-field ui-widget-content ui-corner-all" type="text" id="p_muid_eta" name="eta" size="24" value="<?php echo ($this->entity->eta ? format_date($this->entity->eta, 'date_sort') : ''); ?>" /></label>
	</div>
	<div class="pf-element pf-full-width">
		<label><span class="pf-label">Tracking Number(s)</span>
			<span class="pf-note">One per line.</span>
			<span class="pf-field pf-full-width"><textarea class="ui-widget-content ui-corner-all" style="width: 100%;" rows="3" cols="35" name="tracking_numbers"><?php echo isset($this->entity->tracking_numbers) ? implode("\n", $this->entity->tracking_numbers) : ''; ?></textarea></span></label>
	</div>
	<div class="pf-element">
		<label><span class="pf-label">Shipped</span>
			<span class="pf-note">Check this to ship the packing list below.</span>
			<input class="pf-field" type="checkbox" name="shipped" value="ON" /></label>
	</div>
	<?php switch ($this->type) {
		case 'sale':
		default: 
			?>
	<div class="pf-element pf-heading">
		<h1>Sale #<?php echo htmlspecialchars($this->entity->id); ?> Packing List</h1>
		<?php if ($this->entity->warehouse_pending) { ?>
		<p><strong>There are still unassigned warehouse items on this sale. It can only be partially shipped.</strong></p>
		<?php } ?>
		<p>
			<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'sale/receipt', array('id' => $this->entity->guid))); ?>" onclick="window.open(this.href); return false;">Receipt</a>
			<?php if (gatekeeper('com_sales/editsale')) { ?>
			<a href="<?php echo htmlspecialchars(pines_url('com_sales', 'sale/edit', array('id' => $this->entity->guid))); ?>" onclick="window.open(this.href); return false;">Edit</a>
			<?php } if (!empty($this->entity->comments)) { ?>
			<a href="javascript:void(0);" onclick="$(this).parent().next().slideToggle(); return false;">Comments</a>
			<?php } ?>
		</p>
		<p style="display: none;">
			<small><?php echo str_replace("\n", '<br />', htmlspecialchars($this->entity->comments)); ?></small>
		</p>
	</div>
	<div id="p_muid_packing_list">
			<?php foreach ($this->entity->products as $key => $cur_product) {
				if (!in_array($cur_product['delivery'], array('shipped', 'warehouse')))
					continue;
				// Calculate included stock entries.
				$stock_entries = $cur_product['stock_entities'];
				$shipped_stock_entries = (array) $cur_product['shipped_entities'];
				foreach ((array) $cur_product['returned_stock_entities'] as $cur_stock_entity) {
					$i = $cur_stock_entity->array_search($stock_entries);
					if (isset($i))
						unset($stock_entries[$i]);
					// If it's still in there, it was entered on the sale twice (fulfilled after returned once), so don't remove it from shipped.
					if (!$cur_stock_entity->in_array($stock_entries)) {
						$i = $cur_stock_entity->array_search($shipped_stock_entries);
						if (isset($i))
							unset($shipped_stock_entries[$i]);
					}
				}
				// Is the product already shipped?
				if (count($shipped_stock_entries) >= count($stock_entries))
					continue;
				?>
		<div class="pf-element product">
			<div class="key" style="display: none"><?php echo $key; ?></div>
			<span class="pf-label"><?php echo htmlspecialchars($cur_product['entity']->name); ?> [SKU: <?php echo htmlspecialchars($cur_product['entity']->sku); ?>]</span>
			<span class="pf-note">x <span class="ship_quantity"><?php echo htmlspecialchars(count($stock_entries) - count($shipped_stock_entries)); ?></span></span>
			<div class="pf-group">
				<div class="pf-field">
					<?php if (!empty($cur_product['entity']->receipt_description)) { ?>
					<div><?php echo htmlspecialchars($cur_product['entity']->receipt_description); ?></div>
					<?php } ?>
					<div style="font-size: .9em;">Itemized Breakdown:</div>
					<?php $i = 1; foreach ($stock_entries as $stock_key => $cur_stock) {
						if ($cur_stock->in_array($shipped_stock_entries))
							continue;
						?>
					<div class="ui-widget-content ui-corner-all item_box" style="float: left; font-size: .8em; padding: .4em; margin: 0 .4em .4em 0;">
						<div class="key" style="display: none"><?php echo $stock_key; ?></div>
						<div class="ui-widget-content ui-corner-all number_box" style="font-weight: bold; float: right;">#<?php echo $i; ?></div>
						<div>Shipped From: <?php echo htmlspecialchars($cur_stock->location->name); ?></div>
						<?php if ($cur_product['entity']->serialized) { ?>
						<div>Serial Number: <?php echo htmlspecialchars($cur_stock->serial); ?></div>
						<?php } ?>
					</div>
					<?php $i++; } ?>
				</div>
			</div>
		</div>
			<?php }
			break;
	} ?>
		<input type="hidden" name="packing_list" value="" />
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="type" value="<?php echo $this->type; ?>" />
		<input type="hidden" name="id" value="<?php echo $this->entity->guid; ?>" />
		<?php } ?>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" type="submit" name="submit" value="Save" />
		<input class="pf-button ui-state-default ui-priority-secondary ui-corner-all" type="button" onclick="pines.get('<?php echo htmlspecialchars(pines_url('com_sales', 'stock/shipments')); ?>');" value="Cancel" />
	</div>
</form>