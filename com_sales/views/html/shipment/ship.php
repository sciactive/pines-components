<?php
/**
 * Provides a form for the user to edit a shipment.
 *
 * @package Components\sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = (!isset($this->entity->guid)) ? 'Editing New Shipment' : 'Editing ['.htmlspecialchars($this->entity->id).']';
$this->note = 'Provide shipment details in this form.';

if ($this->entity->ref->has_tag('sale'))
	$ref_class = 'com_sales_sale';
elseif ($this->entity->ref->has_tag('transfer'))
	$ref_class = 'com_sales_transfer';
else
	$ref_class = 'entity';

?>
<style type="text/css" scoped="scoped">
	#p_muid_packing_list .item_box {
		float: left;
		font-size: .8em;
		padding: .5em;
		margin: 0 1em 1em 0;
	}
	#p_muid_packing_list .number_box {
		cursor: pointer;
		font-weight: bold;
		float: right;
	}
</style>
<form class="pf-form" method="post" id="p_muid_form" action="<?php echo htmlspecialchars(pines_url('com_sales', 'shipment/save')); ?>">
	<script type="text/javascript">
		pines(function(){
			var packing_list = $("#p_muid_packing_list");
			var packing_list_input = packing_list.children("input[name=packing_list]");

			packing_list.on("mouseenter", ".item_box", function(){
				var number_box = $(this).find(".number_box");
				number_box.addClass("btn-danger").data("text", number_box.html()).html("&nbsp;X&nbsp;");
			}).on("mouseleave", ".item_box", function(){
				var number_box = $(this).find(".number_box");
				number_box.removeClass("btn-danger").html(number_box.data("text"));
			}).on("click", ".number_box", function(){
				var item_box = $(this).closest(".item_box"),
					product = item_box.closest(".product"),
					ship_qty = product.find(".ship_quantity"),
					cur_qty = parseInt(ship_qty.text());
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
					var product = $(this),
						key = product.children(".key").text(),
						ship_qty = product.find(".ship_quantity"),
						items = product.find(".item_box"),
						stock_entries = [];
					ship_qty.html(items.length);
					items.each(function(){
						stock_entries.push($(this).children(".guid").text());
					});
					new_value[key] = stock_entries;
				});

				packing_list_input.val(JSON.stringify(new_value));
			};

			update_packing_list();
		});
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
			<select class="pf-field" name="shipper">
				<option value="null">-- None --</option>
				<?php foreach ($this->shippers as $cur_shipper) { ?>
				<option value="<?php echo (int) $cur_shipper->guid ?>"<?php echo $this->entity->shipper->guid == $cur_shipper->guid ? ' selected="selected"' : ''; ?>><?php echo htmlspecialchars($cur_shipper->name); ?></option>
				<?php } ?>
			</select></label>
	</div>
	<div class="pf-element">
		<script type="text/javascript">
			pines(function(){
				$("#p_muid_eta").datepicker({
					dateFormat: "yy-mm-dd",
					showOtherMonths: true,
					selectOtherMonths: true
				});
			});
		</script>
		<label><span class="pf-label">ETA</span>
			<input class="pf-field" type="text" id="p_muid_eta" name="eta" size="24" value="<?php echo ($this->entity->eta ? htmlspecialchars(format_date($this->entity->eta, 'date_sort')) : ''); ?>" /></label>
	</div>
	<div class="pf-element pf-full-width">
		<label><span class="pf-label">Tracking Number(s)</span>
			<span class="pf-note">One per line.</span>
			<span class="pf-group pf-full-width">
				<span class="pf-field" style="display: block;">
					<textarea style="width: 100%;" rows="3" cols="35" name="tracking_numbers"><?php echo isset($this->entity->tracking_numbers) ? htmlspecialchars(implode("\n", $this->entity->tracking_numbers)) : ''; ?></textarea>
				</span>
			</span></label>
	</div>
	<div class="pf-element pf-heading">
		<h3>New Packing List - <a data-entity="<?php echo htmlspecialchars($this->entity->ref->guid); ?>" data-entity-context="<?php echo htmlspecialchars($ref_class); ?>"><?php echo htmlspecialchars($this->entity->ref->info('name')); ?></a></h3>
		<?php if (!empty($this->entity->ref->comments)) { ?>
		<p>
			<a href="javascript:void(0);" onclick="$(this).parent().next().slideToggle(); return false;">Comments</a>
		</p>
		<p style="display: none;">
			<small><?php echo str_replace("\n", '<br />', htmlspecialchars($this->entity->ref->comments)); ?></small>
		</p>
		<?php } ?>
	</div>
	<div id="p_muid_packing_list">
		<?php foreach ($this->entity->products as $key => $cur_product) { ?>
		<div class="pf-element product">
			<div class="key" style="display: none"><?php echo htmlspecialchars($key); ?></div>
			<span class="pf-label"><a data-entity="<?php echo htmlspecialchars($cur_product['entity']->guid); ?>" data-entity-context="com_sales_product"><?php echo htmlspecialchars($cur_product['entity']->name); ?></a> [SKU: <?php echo htmlspecialchars($cur_product['entity']->sku); ?>]</span>
			<span class="pf-note">x <span class="ship_quantity"><?php echo htmlspecialchars(count($cur_product['stock_entities'])); ?></span></span>
			<div class="pf-group">
				<div class="pf-field">
					<?php if (!empty($cur_product['entity']->receipt_description)) { ?>
					<div><?php echo htmlspecialchars($cur_product['entity']->receipt_description); ?></div>
					<?php } ?>
					<div style="font-size: .9em;">Itemized Breakdown:</div>
					<?php foreach ($cur_product['stock_entities'] as $stock_key => $cur_stock) { ?>
					<div class="well item_box">
						<div class="guid" style="display: none"><?php echo htmlspecialchars($cur_stock->guid); ?></div>
						<button type="button" class="btn btn-mini number_box">#<?php echo (int) ($stock_key+1); ?></button>
						<div>Stock Entry: <a data-entity="<?php echo htmlspecialchars($cur_stock->guid); ?>" data-entity-context="com_sales_stock"><?php echo htmlspecialchars($cur_stock->guid); ?></a></div>
						<div>Shipped From: <a data-entity="<?php echo htmlspecialchars($cur_stock->location->guid); ?>" data-entity-context="group"><?php echo htmlspecialchars($cur_stock->location->name); ?></a></div>
						<?php if ($cur_product['entity']->serialized) { ?>
						<div>Serial: <?php echo htmlspecialchars($cur_stock->serial); ?></div>
						<?php } ?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php } ?>
		<input type="hidden" name="packing_list" value="" />
	</div>
	<div class="pf-element pf-full-width">
		<label><span class="pf-label">Notes</span>
			<span class="pf-group pf-full-width">
				<span class="pf-field" style="display: block;">
					<textarea style="width: 100%;" rows="3" cols="35" name="notes"><?php echo htmlspecialchars($this->entity->notes); ?></textarea>
				</span>
			</span></label>
	</div>
	<div class="pf-element pf-buttons">
		<?php if ( isset($this->entity->guid) ) { ?>
		<input type="hidden" name="id" value="<?php echo (int) $this->entity->guid ?>" />
		<?php } else { ?>
		<input type="hidden" name="ref_id" value="<?php echo (int) $this->entity->ref->guid ?>" />
		<?php } ?>
		<input class="pf-button btn btn-primary" type="submit" name="submit" value="Save" />
		<input class="pf-button btn" type="button" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_sales', 'shipment/list'))); ?>);" value="Cancel" />
	</div>
</form>