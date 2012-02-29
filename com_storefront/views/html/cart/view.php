<?php
/**
 * Shows shopping cart contents.
 *
 * @package Pines
 * @subpackage com_storefront
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Shopping Cart';
?>
<?php if ($pines->com_storefront->cart()) { ?>
<div>
	<table style="width: 100%;">
		<thead>
			<tr>
				<th style="width: 10%;">SKU</th>
				<th style="width: 60%;">Name</th>
				<th style="width: 6%;">Qty</th>
				<th style="width: 12%;">Price</th>
				<th style="width: 12%;">Total</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($pines->com_storefront->cart() as $cur_item) {
				$subtotal += $cur_item['product']->unit_price * $cur_item['quantity'];
				?>
			<tr>
				<td><?php echo htmlspecialchars($cur_item['product']->sku); ?></td>
				<td><?php echo htmlspecialchars($cur_item['product']->name); ?></td>
				<td style="text-align: right;"><?php echo htmlspecialchars($cur_item['quantity']); ?></td>
				<td style="text-align: right;">$<?php echo htmlspecialchars($pines->com_sales->round($cur_item['product']->unit_price, true)); ?></td>
				<td style="text-align: right;">$<?php echo htmlspecialchars($pines->com_sales->round($cur_item['product']->unit_price * $cur_item['quantity'], true)); ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<br />
	<div style="padding: .5em;">Subtotal <small>(before tax/fees)</small><div style="float: right; text-align: right;"><?php echo $pines->com_storefront->format_price($subtotal, 'long'); ?></div></div>
	<br style="clear: both;" /><br />
	<button type="button" class="checkout btn btn-primary" style="float: right; clear: right;" onclick="pines.get(<?php echo htmlspecialchars(json_encode(pines_url('com_storefront', 'checkout/login'))); ?>);">Check-Out</button>
	<br style="height: 0; clear: both;" />
</div>
<?php } else { ?>
<div>
	There are no items in your cart.
</div>
<?php } ?>