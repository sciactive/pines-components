<?php
/**
 * Shows a quote, invoice, or receipt for a sale or return.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
/* @var $pines pines *//* @var $this module */
defined('P_RUN') or die('Direct access prohibited');

$sale = $this->entity->has_tag('sale');

switch ($this->entity->status) {
	case 'quoted':
		$this->doc_title = $sale ? 'Quote' : 'Quoted Return';
		break;
	case 'invoiced':
		$this->doc_title = 'Invoice';
		break;
	case 'paid':
	case 'processed':
		$this->doc_title = $sale ? 'Receipt' : 'Return Receipt';
		break;
	case 'voided':
		$this->doc_title = $sale ? 'Sale Void' : 'Return Void';
		break;
	default:
		$this->doc_title = $sale ? 'Sale' : 'Return';
		break;
}
?>
<style type="text/css">
	#p_muid_receipt .data_col .name {
		font-weight: bold;
	}
	#p_muid_receipt .left_side {
		margin-bottom: 10px;
		float: left;
		clear: left;
	}
	#p_muid_receipt .right_side {
		margin-bottom: 10px;
		float: right;
		clear: right;
	}
	#p_muid_receipt .barcode h1 {
		margin-bottom: 0px;
		padding-right: 15px;
		text-align: right;
	}
	#p_muid_receipt .barcode img {
		margin-top: 0px;
	}
	#p_muid_receipt .right_text {
		text-align: right;
	}
	#p_muid_receipt .left_side div, #p_muid_receipt .right_side div {
		float: left;
	}
	#p_muid_receipt .data_col {
		float: left;
		margin-left: 10px;
		padding-right: 15px;
	}
	#p_muid_receipt .left_side span, #p_muid_receipt .right_side span {
		display: block;
	}
	#p_muid_receipt .aligner {
		text-align: right;
		width: 65px;
	}
	.p_muid_item_list {
		text-align: left;
		border-bottom: 1px solid black;
		border-collapse: collapse;
	}
	.p_muid_item_list th {
		border-bottom: 1px solid black;
		padding: 2px;
	}
	.p_muid_item_list tr td p {
		margin: 0;
	}
	#p_muid_receipt .receipt_note, #p_muid_receipt .comments {
		font-size: 75%;
	}
</style>
<div id="p_muid_receipt" class="pf-form pf-form-twocol">
	<?php
	// Sales rep and sales group entities.
	$sales_rep = (isset($this->entity->user) && !$this->entity->user->is($this->entity->customer)) ? $this->entity->user : null;
	$sales_group = $this->entity->group;
	// Set the location of the group logo image.
	if (isset($sales_group))
		$group_logo = $sales_group->get_logo(true);
	// Document id number.
	$doc_id = ($sale ? 'SA' : 'RE') . $this->entity->id;
	?>
	<div class="left_side">
		<span><img src="<?php echo htmlspecialchars($group_logo); ?>" alt="<?php echo htmlspecialchars($pines->config->system_name); ?>" /></span>
	</div>
	<div class="right_side barcode">
		<h1><?php echo htmlspecialchars($this->doc_title); ?></h1>
		<img src="<?php echo htmlspecialchars(pines_url('com_barcode', 'image', array('code' => $doc_id, 'height' => '60', 'width' => '300', 'style' => '850'), true)); ?>" alt="Barcode" />
	</div>
	<?php if (isset($sales_rep->guid)) { ?>
	<div class="left_side location">
		<div class="aligner">Location:</div>
		<div class="data_col">
			<span class="name"><?php echo htmlspecialchars($sales_group->name); ?></span>
			<?php if ($sales_group->address_type == 'us') { ?>
			<span><?php echo htmlspecialchars("{$sales_group->address_1}\n{$sales_group->address_2}"); ?></span>
			<span><?php echo htmlspecialchars($sales_group->city); ?>, <?php echo htmlspecialchars($sales_group->state); ?> <?php echo htmlspecialchars($sales_group->zip); ?></span>
			<?php } else { ?>
			<span><?php echo htmlspecialchars($sales_group->address_international); ?></span>
			<?php } ?>
			<span><?php echo htmlspecialchars(format_phone($sales_group->phone)); ?></span>
		</div>
	</div>
	<?php } ?>
	<div class="right_side receipt_info">
		<div class="right_text">
			<span><?php echo $sale ? 'Sale' : 'Return'; ?> #:</span>
			<span>Date:</span>
			<span>Time:</span>
			<?php if (!$sale && isset($this->entity->sale)) { ?><span>Sale:</span><?php } ?>
			<?php if (isset($sales_rep->guid)) { ?><span>Sales Rep:</span><?php } ?>
		</div>
		<div class="data_col">
			<span><?php echo htmlspecialchars($this->entity->id); ?></span>
			<?php switch($this->entity->status) {
				case 'invoiced':
					echo '<span>'.htmlspecialchars(format_date($this->entity->invoice_date, 'date_short')).'</span>';
					echo '<span>'.htmlspecialchars(format_date($this->entity->invoice_date, 'time_short')).'</span>';
					break;
				case 'paid':
					echo '<span>'.htmlspecialchars(format_date($this->entity->tender_date, 'date_short')).'</span>';
					echo '<span>'.htmlspecialchars(format_date($this->entity->tender_date, 'time_short')).'</span>';
					break;
				case 'processed':
					echo '<span>'.htmlspecialchars(format_date($this->entity->process_date, 'date_short')).'</span>';
					echo '<span>'.htmlspecialchars(format_date($this->entity->process_date, 'time_short')).'</span>';
					break;
				case 'voided':
					echo '<span>'.htmlspecialchars(format_date($this->entity->void_date, 'date_short')).'</span>';
					echo '<span>'.htmlspecialchars(format_date($this->entity->void_date, 'time_short')).'</span>';
					break;
				default:
					echo '<span>'.htmlspecialchars(format_date($this->entity->p_cdate, 'date_short')).'</span>';
					echo '<span>'.htmlspecialchars(format_date($this->entity->p_cdate, 'time_short')).'</span>';
					break;
			} ?>
			<?php if (!$sale && isset($this->entity->sale)) { ?><span><?php echo htmlspecialchars($this->entity->sale->id); ?></span><?php } ?>
			<?php if (isset($sales_rep->guid)) { ?><span><?php echo htmlspecialchars($sales_rep->name); ?></span><?php } ?>
		</div>
	</div>
	<?php if (isset($this->entity->shipping_address) && ($this->entity->has_tag('shipping_pending') || $this->entity->has_tag('shipping_shipped') || $this->entity->warehouse)) { ?>
	<div class="left_side customer">
		<div class="aligner">
			<span>Ship To:</span>
		</div>
		<div class="data_col">
			<span><strong>
				<?php echo htmlspecialchars($this->entity->shipping_address->name); ?>
			</strong></span>
			<?php if ($this->entity->shipping_address->address_type == 'us') { if (!empty($this->entity->shipping_address->address_1)) { ?>
			<span><?php echo htmlspecialchars($this->entity->shipping_address->address_1.' '.$this->entity->shipping_address->address_2); ?></span>
			<span><?php echo htmlspecialchars($this->entity->shipping_address->city); ?>, <?php echo htmlspecialchars($this->entity->shipping_address->state); ?> <?php echo htmlspecialchars($this->entity->shipping_address->zip); ?></span>
			<?php } } else {?>
			<span><?php echo htmlspecialchars($this->entity->shipping_address->address_international); ?></span>
			<?php } ?>
		</div>
	</div>
	<?php } if ($pines->config->com_sales->com_customer && isset($this->entity->customer)) { ?>
	<div class="left_side customer">
		<div class="aligner">
			<span>Bill To:</span>
		</div>
		<div class="data_col">
			<span><strong>
				<?php echo htmlspecialchars($this->entity->customer->name); ?>
				<?php if (isset($this->entity->customer->company->name)) {
					echo htmlspecialchars(" ( {$this->entity->customer->company->name} )");
				} ?>
			</strong></span>
			<?php if ($this->entity->customer->address_type == 'us') { if (!empty($this->entity->customer->address_1)) { ?>
			<span><?php echo htmlspecialchars($this->entity->customer->address_1.' '.$this->entity->customer->address_2); ?></span>
			<span><?php echo htmlspecialchars($this->entity->customer->city); ?>, <?php echo htmlspecialchars($this->entity->customer->state); ?> <?php echo htmlspecialchars($this->entity->customer->zip); ?></span>
			<?php } } else {?>
			<span><?php echo htmlspecialchars($this->entity->customer->address_international); ?></span>
			<?php } ?>
		</div>
	</div>
	<?php } ?>
	<div class="pf-element pf-full-width left_side">
		<table class="p_muid_item_list" style="width: 100%; margin: 0;">
			<thead>
				<tr>
					<th>SKU</th>
					<th>Item</th>
					<th>Serial</th>
					<th class="right_text">Qty</th>
					<th class="right_text">Price</th>
					<?php if (!$sale) { ?>
					<th class="right_text">Return Fee</th>
					<?php } ?>
					<th class="right_text">Total</th>
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
					<td><?php
					$text = array();
					if (isset($cur_product['serial']))
						$text[] = $cur_product['serial'];
					if (!$this->hide_warehouse_status && $sale && $cur_product['delivery'] == 'warehouse') {
						$quantity = $cur_product['quantity'] - (int) $cur_product['returned_quantity'];
						if ($this->entity->status == 'quoted') {
							$text[] = "({$quantity} warehouse)";
						} else {
							$fulfilled = 0;
							$shipped = 0;
							foreach ((array) $cur_product['stock_entities'] as $cur_stock) {
								if (!isset($cur_stock->guid) || $cur_stock->in_array((array) $cur_product['returned_stock_entities']))
									continue;
								$fulfilled++;
								if ($cur_stock->in_array((array) $cur_product['shipped_entities']))
									$shipped++;
							}
							if ($fulfilled) {
								$left = $fulfilled - $shipped;
								if ($left)
									$text[] = "($left to ship)";
							}
							$unfulfilled = $quantity - $fulfilled;
								if ($unfulfilled)
									$text[] = "($unfulfilled to fulfill)";
						}
					}
					echo htmlspecialchars(implode(' ', $text));
					?></td>
					<td class="right_text"><?php echo htmlspecialchars($cur_product['quantity']); ?></td>
					<td class="right_text">$<?php echo $pines->com_sales->round($cur_product['price'], true); ?><?php echo empty($cur_product['discount']) ? '' : htmlspecialchars(" - {$cur_product['discount']}"); ?></td>
					<?php if (!$sale) { ?>
					<td class="right_text">$<?php echo $pines->com_sales->round($cur_product['return_fee'], true); ?></td>
					<?php } ?>
					<td class="right_text">$<?php echo $pines->com_sales->round($cur_product['line_total'] - (float) $cur_product['return_fee'], true); ?></td>
				</tr>
				<?php } } ?>
			</tbody>
		</table>
	</div>
	<?php if ($this->entity->specials) { ?>
	<div class="pf-element pf-full-width left_side">
		<table class="p_muid_item_list" style="width: 100%; margin: 0;">
			<thead>
				<tr>
					<th>Specials</th>
					<th>Applied</th>
					<th class="right_text">Discount</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->entity->specials as $cur_special) { ?>
				<tr>
					<td style="width: 80%;"><?php echo htmlspecialchars(($cur_special['entity']->hide_code ? '' : "{$cur_special['code']} - ").$cur_special['name']); ?></td>
					<td style="white-space: pre;"><?php echo $cur_special['before_tax'] ? 'Before Tax' : 'After Tax'; ?></td>
					<td class="right_text">$<?php echo $pines->com_sales->round($cur_special['discount'], true); ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<?php } ?>
	<div class="pf-element pf-full-width">
		<?php if (is_array($this->entity->payments) && ($this->entity->status == 'paid' || $this->entity->status == 'processed' || $this->entity->status == 'voided')) { ?>
		<div class="left_side">
			<div><strong>Payments<?php if (!$sale) echo ' Returned' ?>:</strong></div>
			<hr style="clear: both;" />
			<div class="right_text">
				<?php foreach ($this->entity->payments as $cur_payment) { ?>
				<span><?php echo htmlspecialchars($cur_payment['label']); ?>:</span>
				<?php } ?>
				<hr style="visibility: hidden;" />
				<span>Amount <?php echo $sale ? 'Tendered' : 'Refunded'; ?>:</span>
				<?php if ($sale && $this->entity->change) { ?><span>Change:</span><?php } ?>
			</div>
			<div class="data_col right_text">
				<?php foreach ($this->entity->payments as $cur_payment) { ?>
				<span>$<?php echo $pines->com_sales->round($cur_payment['amount'], true); ?></span>
				<?php } ?>
				<hr />
				<span>$<?php echo $pines->com_sales->round($this->entity->amount_tendered, true); ?></span>
				<?php if ($sale && $this->entity->change) { ?><span>$<?php echo $pines->com_sales->round($this->entity->change, true); ?></span><?php } ?>
			</div>
		</div>
		<?php } ?>
		<div class="right_side">
			<div><strong>Totals:</strong></div>
			<hr style="clear: both;" />
			<div class="right_text">
				<span>Subtotal:</span>
				<?php if ($this->entity->total_specials > 0) { ?><span>Specials:</span><?php } ?>
				<?php if ($this->entity->item_fees > 0) { ?><span>Item Fees:</span><?php } ?>
				<span>Tax:</span>
				<?php if ($this->entity->return_fees > 0) { ?><span>Return Fees:</span><?php } ?>
				<hr style="visibility: hidden;" />
				<span><strong>Total: </strong></span>
			</div>
			<div class="data_col right_text">
				<span>$<?php echo $pines->com_sales->round($this->entity->subtotal, true); ?></span>
				<?php if ($this->entity->total_specials > 0) { ?><span>($<?php echo $pines->com_sales->round($this->entity->total_specials, true); ?>)</span><?php } ?>
				<?php if ($this->entity->item_fees > 0) { ?><span>$<?php echo $pines->com_sales->round($this->entity->item_fees, true); ?></span><?php } ?>
				<span>$<?php echo $pines->com_sales->round($this->entity->taxes, true); ?></span>
				<?php if ($this->entity->return_fees > 0) { ?><span>($<?php echo $pines->com_sales->round($this->entity->return_fees, true); ?>)</span><?php } ?>
				<hr />
				<span><strong>$<?php echo $pines->com_sales->round($this->entity->total, true); ?></strong></span>
			</div>
		</div>
	</div>
	<?php if (!empty($this->entity->comments)) { ?>
	<div class="pf-element pf-full-width">
		<div class="pf-field">
			<span class="pf-label">Comments:</span>
			<br />
			<span class="pf-field comments"><?php echo htmlspecialchars($this->entity->comments); ?></span>
		</div>
	</div>
	<?php }
	switch ($this->entity->status) {
		case 'quoted':
			$label = (string) $pines->config->com_sales->quote_note_label;
			$text = (string) $pines->config->com_sales->quote_note_text;
			break;
		case 'invoiced':
			$label = (string) $pines->config->com_sales->invoice_note_label;
			$text = (string) $pines->config->com_sales->invoice_note_text;
			break;
		case 'paid':
			$label = (string) $pines->config->com_sales->receipt_note_label;
			$text = (string) $pines->config->com_sales->receipt_note_text;
			break;
		case 'processed':
			$label = (string) $pines->config->com_sales->return_note_label;
			$text = (string) $pines->config->com_sales->return_note_text;
			break;
		default:
			$label = null;
			$text = null;
	}
	if (!empty($text)) { ?>
	<div class="pf-element pf-full-width">
		<span class="pf-label"><?php echo htmlspecialchars($label); ?></span>
		<br />
		<div class="pf-field receipt_note"><?php echo htmlspecialchars($text); ?></div>
	</div>
	<?php } ?>
</div>