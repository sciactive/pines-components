<?php
/**
 * Provides a review of a sale.
 *
 * @package Pines
 * @subpackage com_storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Review Your Order';
?>
<style type="text/css">
	/* <![CDATA[ */
	#p_muid_item_list {
		text-align: left;
		border-collapse: collapse;
	}
	#p_muid_item_list th {
		padding: 2px;
	}
	#p_muid_item_list tr td p {
		margin: 0;
	}
	#p_muid_item_list .right_text {
		text-align: right;
	}
	/* ]]> */
</style>
<form id="p_muid_review" class="pf-form pf-form-twocol" method="POST" action="<?php echo htmlspecialchars(pines_url('com_storefront', 'checkout/reviewsave')); ?>">
	<div class="pf-element">
		<span class="pf-label" style="text-align: right;">Ship To&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
		<span class="pf-note" style="text-align: right;"><a href="<?php echo htmlspecialchars(pines_url('com_storefront', 'checkout/shipping', array('noskip' => 'true'))); ?>">Edit Address</a><span style="font-size: 143%; line-height: 1px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span></span>
		<div class="pf-group">
			<div class="pf-field">
				<strong><?php echo htmlspecialchars($this->entity->shipping_address->name); ?></strong><br />
				<?php if ($this->entity->shipping_address->address_type == 'us') { ?>
				<?php echo htmlspecialchars("{$this->entity->shipping_address->address_1}\n{$this->entity->shipping_address->address_2}"); ?><br />
				<?php echo htmlspecialchars($this->entity->shipping_address->city); ?>, <?php echo htmlspecialchars($this->entity->shipping_address->state); ?> <?php echo htmlspecialchars($this->entity->shipping_address->zip); ?>
				<?php } else { ?>
				<?php echo str_replace("\n", '<br />', htmlspecialchars($this->entity->shipping_address->address_international)); ?>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="pf-element">
		<span class="pf-label" style="text-align: right;">Bill To&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
		<div class="pf-group">
			<div class="pf-field">
				<strong>
					<?php echo htmlspecialchars($this->entity->customer->name); ?>
					<?php if (isset($this->entity->customer->company->name)) {
						echo htmlspecialchars(" ( {$this->entity->customer->company->name} )");
					} ?>
				</strong><br />
				<?php if ($this->entity->customer->address_type == 'us') { if (!empty($this->entity->customer->address_1)) { ?>
				<?php echo htmlspecialchars($this->entity->customer->address_1.' '.$this->entity->customer->address_2); ?><br />
				<?php echo htmlspecialchars($this->entity->customer->city); ?>, <?php echo htmlspecialchars($this->entity->customer->state); ?> <?php echo htmlspecialchars($this->entity->customer->zip); ?><br />
				<?php } } else {?>
				<?php echo str_replace("\n", '<br />', htmlspecialchars($this->entity->customer->address_international)); ?><br />
				<?php } ?>
				<?php echo format_phone($this->entity->customer->phone); ?>
			</div>
		</div>
	</div>
	<div class="pf-element pf-heading">
		<h1>Products</h1>
	</div>
	<div class="pf-element pf-full-width">
		<table id="p_muid_item_list" style="width: 100%;">
			<thead>
				<tr>
					<th>SKU</th>
					<th>Item</th>
					<th>Description</th>
					<th>Qty</th>
					<th>Price</th>
					<th>Total</th>
				</tr>
			</thead>
			<tbody>
				<?php if (is_array($this->entity->products)) { foreach ($this->entity->products as $cur_product) {
					if ($cur_product['entity']->hide_on_invoice)
						continue;
					?>
				<tr>
					<td><?php echo htmlspecialchars($cur_product['entity']->sku); ?></td>
					<td><?php echo htmlspecialchars($cur_product['entity']->name); ?></td>
					<td><?php echo !empty($cur_product['entity']->receipt_description) ? $cur_product['entity']->receipt_description : $cur_product['entity']->short_description; ?></td>
					<td class="right_text"><?php echo htmlspecialchars($cur_product['quantity']); ?></td>
					<td class="right_text">$<?php echo $pines->com_sales->round($cur_product['price'], true); ?><?php echo empty($cur_product['discount']) ? '' : htmlspecialchars(" - {$cur_product['discount']}"); ?></td>
					<td class="right_text">$<?php echo $pines->com_sales->round($cur_product['line_total'], true); ?></td>
				</tr>
				<?php } } ?>
			</tbody>
		</table>
	</div>
	<div class="pf-element pf-heading" style="height: 1px; font-size: 1px;">&nbsp;</div>
	<div class="pf-element pf-full-width" style="text-align: right;">
		<div>
			<span class="pf-label" style="width: 80%;">Subtotal</span>
			<span class="pf-field">$<?php echo $pines->com_sales->round($this->entity->subtotal, true); ?></span>
		</div>
		<?php if ($this->entity->item_fees > 0) { ?>
		<div>
			<span class="pf-label" style="width: 80%;">Item Fees</span>
			<span class="pf-field">$<?php echo $pines->com_sales->round($this->entity->item_fees, true); ?></span>
		</div>
		<?php } ?>
		<div>
			<span class="pf-label" style="width: 80%;">Tax</span>
			<span class="pf-field">$<?php echo $pines->com_sales->round($this->entity->taxes, true); ?></span>
		</div>
		<div>
			<strong class="pf-label" style="width: 80%;">Total</strong>
			<strong class="pf-field">$<?php echo $pines->com_sales->round($this->entity->total, true); ?></strong>
		</div>
	</div>
	<?php if (!$this->no_form) { ?>
	<div class="pf-element pf-heading">
		<h1>Paying With</h1>
	</div>
	<?php if (is_array($this->entity->payments)) { foreach ($this->entity->payments as $cur_payment) { ?>
	<div class="pf-element">
		<span class="pf-label"><?php echo htmlspecialchars($cur_payment['type']); ?></span>
		<span class="pf-field">$<?php echo $pines->com_sales->round($cur_payment['amount'], true); ?></span>
	</div>
	<?php } } ?>
	<div class="pf-element pf-full-width">
		<span class="pf-label">Order Comments</span>
		<textarea class="pf-field ui-widget-content ui-corner-all" rows="1" cols="35" name="comments"><?php echo $this->entity->comments; ?></textarea>
	</div>
	<div class="pf-element pf-buttons">
		<script type="text/javascript">
			// <![CDATA[
			pines(function(){$("#p_muid_submit").button()});
			// ]]>
		</script>
		<input class="pf-button ui-state-default ui-priority-primary ui-corner-all" id="p_muid_submit" type="submit" value="<?php echo htmlspecialchars($pines->config->com_storefront->complete_order_text); ?>" />
	</div>
	<?php } ?>
</form>