<?php
/**
 * Shows a quote, invoice, or receipt for a sale or return.
 *
 * @package Components\sales
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
<div id="p_muid_receipt">
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
	<table width="100%" cellspacing="0" cellpadding="0">
		<tr>
			<td valign="top" align="left">
				<span><img src="<?php echo htmlspecialchars($group_logo); ?>" alt="<?php echo htmlspecialchars($pines->config->system_name); ?>" /></span>
			</td>
			<td valign="top" align="right">
				<span><?php echo htmlspecialchars($this->doc_title); ?></span><br />
				<img src="<?php echo htmlspecialchars(pines_url('com_barcode', 'image', array('code' => $doc_id, 'height' => '60', 'width' => '300', 'style' => '850'), true)); ?>" alt="<?php echo htmlspecialchars($doc_id); ?>" />
			</td>
		</tr>
	</table>
	<br />
	<table width="100%" cellspacing="0" cellpadding="0">
		<tr>
			<?php if (isset($sales_rep->guid)) { ?>
			<td width="50%" valign="top" align="left">
				<h3>Location:</h3>
				<?php echo htmlspecialchars($sales_group->name); ?><br />
				<?php if ($sales_group->address_type == 'us') { ?>
				<?php echo htmlspecialchars($sales_group->address_1).'<br />'.htmlspecialchars($sales_group->address_2); ?><br />
				<?php echo htmlspecialchars($sales_group->city); ?>, <?php echo htmlspecialchars($sales_group->state); ?> <?php echo htmlspecialchars($sales_group->zip); ?>
				<?php } else { ?>
				<pre><?php echo htmlspecialchars($sales_group->address_international); ?></pre>
				<?php } ?>
				<br />
				<?php echo htmlspecialchars(format_phone($sales_group->phone)); ?>
			</td>
			<?php } ?>
			<td width="50%" valign="top" align="right">
				<table cellspacing="4" cellpadding="">
					<tr>
						<td align="right" valign="top" style="text-align: right;"><?php echo $sale ? 'Sale' : 'Return'; ?> #:</td>
						<td><?php echo htmlspecialchars($this->entity->id); ?></td>
					</tr>
					<tr>
						<td align="right" valign="top" style="text-align: right;">Date:</td>
						<td><?php switch($this->entity->status) {
							case 'invoiced':
								echo '<span>'.htmlspecialchars(format_date($this->entity->invoice_date, 'date_short')).'</span>';
								break;
							case 'paid':
								echo '<span>'.htmlspecialchars(format_date($this->entity->tender_date, 'date_short')).'</span>';
								break;
							case 'processed':
								echo '<span>'.htmlspecialchars(format_date($this->entity->process_date, 'date_short')).'</span>';
								break;
							case 'voided':
								echo '<span>'.htmlspecialchars(format_date($this->entity->void_date, 'date_short')).'</span>';
								break;
							default:
								echo '<span>'.htmlspecialchars(format_date($this->entity->p_cdate, 'date_short')).'</span>';
								break;
						} ?></td>
					</tr>
					<tr>
						<td align="right" valign="top" style="text-align: right;">Time:</td>
						<td><?php switch($this->entity->status) {
							case 'invoiced':
								echo '<span>'.htmlspecialchars(format_date($this->entity->invoice_date, 'time_short')).'</span>';
								break;
							case 'paid':
								echo '<span>'.htmlspecialchars(format_date($this->entity->tender_date, 'time_short')).'</span>';
								break;
							case 'processed':
								echo '<span>'.htmlspecialchars(format_date($this->entity->process_date, 'time_short')).'</span>';
								break;
							case 'voided':
								echo '<span>'.htmlspecialchars(format_date($this->entity->void_date, 'time_short')).'</span>';
								break;
							default:
								echo '<span>'.htmlspecialchars(format_date($this->entity->p_cdate, 'time_short')).'</span>';
								break;
						} ?></td>
					</tr>
					<?php if (!$sale && isset($this->entity->sale)) { ?>
					<tr>
						<td align="right" valign="top" style="text-align: right;">Sale:</td>
						<td><?php echo htmlspecialchars($this->entity->sale->id); ?></td>
					</tr>
					<?php } if (isset($sales_rep->guid)) { ?>
					<tr>
						<td align="right" valign="top" style="text-align: right;">Sales Rep:</td>
						<td>
							<span style="display: block; margin-right: .5em; float: left; border: 1px solid #DDDDDD; border-radius: 4px 4px 4px 4px; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075); line-height: 1; padding: 4px;"><img src="<?php echo htmlspecialchars($sales_rep->info('avatar')); ?>" alt="Avatar" title="Avatar by Gravatar" /></span>
							<?php echo htmlspecialchars($sales_rep->name); ?>
						</td>
					</tr>
					<?php } ?>
				</table>
			</td>
		</tr>
	</table>
	<br />
	<table width="100%" cellspacing="0" cellpadding="0">
		<tr>
			<?php if (isset($this->entity->shipping_address) && ($this->entity->has_tag('shipping_pending') || $this->entity->has_tag('shipping_shipped') || $this->entity->warehouse)) { ?>
			<td width="50%" valign="top" align="left">
				<h3>Ship To:</h3>
				<strong><?php echo htmlspecialchars($this->entity->shipping_address->name); ?></strong><br />
				<?php if ($this->entity->shipping_address->address_type == 'us') { if (!empty($this->entity->shipping_address->address_1)) { ?>
				<?php echo htmlspecialchars($this->entity->shipping_address->address_1).'<br />'.htmlspecialchars($this->entity->shipping_address->address_2); ?><br />
				<?php echo htmlspecialchars($this->entity->shipping_address->city); ?>, <?php echo htmlspecialchars($this->entity->shipping_address->state); ?> <?php echo htmlspecialchars($this->entity->shipping_address->zip); ?>
				<?php } } else {?>
				<pre><?php echo htmlspecialchars($this->entity->shipping_address->address_international); ?></pre>
				<?php } ?>
			</td>
			<?php } if ($pines->config->com_sales->com_customer && isset($this->entity->customer)) { ?>
			<td width="50%" valign="top" align="left">
				<h3>Bill To:</h3>
				<table>
					<tr>
						<td valign="top" style="padding-right: .5em;">
							<span style="display: block; float: left; border: 1px solid #DDDDDD; border-radius: 4px 4px 4px 4px; box-shadow: 0 1px 1px rgba(0, 0, 0, 0.075); line-height: 1; padding: 4px;"><img src="<?php echo htmlspecialchars($this->entity->customer->info('avatar')); ?>" alt="Avatar" title="Avatar by Gravatar" /></span>
						</td>
						<td>
							<strong>
								<?php echo htmlspecialchars($this->entity->customer->name);
								if (isset($this->entity->customer->company->name))
									echo htmlspecialchars(" ( {$this->entity->customer->company->name} )"); ?>
							</strong><br />
							<?php if ($this->entity->customer->address_type == 'us') { if (!empty($this->entity->customer->address_1)) { ?>
							<?php echo htmlspecialchars($this->entity->customer->address_1).'<br />'.htmlspecialchars($this->entity->customer->address_2); ?><br />
							<?php echo htmlspecialchars($this->entity->customer->city); ?>, <?php echo htmlspecialchars($this->entity->customer->state); ?> <?php echo htmlspecialchars($this->entity->customer->zip); ?>
							<?php } } else {?>
							<pre><?php echo htmlspecialchars($this->entity->customer->address_international); ?></pre>
							<?php } ?>
						</td>
					</tr>
				</table>
			</td>
			<?php } ?>
		</tr>
	</table>
	<br />
	<br />
	<br />
	<br />
	<table width="100%" cellpadding="3" cellspacing="2" style="border-bottom: 1px solid #333;">
		<tr>
			<th style="background-color: #dcf0f7; color: #577887; font-weight: normal; text-align: left;">SKU</th>
			<th style="background-color: #dcf0f7; color: #577887; font-weight: normal; text-align: left;">Item</th>
			<th style="background-color: #dcf0f7; color: #577887; font-weight: normal; text-align: left;">Serial</th>
			<th style="background-color: #dcf0f7; color: #577887; font-weight: normal; text-align: right;">Qty</th>
			<th style="background-color: #dcf0f7; color: #577887; font-weight: normal; text-align: right;">Price</th>
			<?php if (!$sale) { ?>
			<th style="background-color: #dcf0f7; color: #577887; font-weight: normal; text-align: right;">Return Fee</th>
			<?php } ?>
			<th style="background-color: #dcf0f7; color: #577887; font-weight: normal; text-align: right;">Total</th>
		</tr>
		<?php if (is_array($this->entity->products)) { foreach ($this->entity->products as $cur_product) {
			if ($cur_product['entity']->hide_on_invoice)
				continue;
			?>
		<tr>
			<td valign="top" style="text-align: left;"><?php echo htmlspecialchars($cur_product['entity']->sku); ?></td>
			<td valign="top" style="text-align: left;"><?php echo htmlspecialchars($cur_product['entity']->name); ?></td>
			<td valign="top" style="text-align: left;"><?php
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
			?>&nbsp;</td>
			<td valign="top" style="text-align: right;"><?php echo htmlspecialchars($cur_product['quantity']); ?></td>
			<td valign="top" style="text-align: right;">$<?php echo $pines->com_sales->round($cur_product['price'], true); ?><?php echo empty($cur_product['discount']) ? '' : htmlspecialchars(" - {$cur_product['discount']}"); ?></td>
			<?php if (!$sale) { ?>
			<td valign="top" style="text-align: right;">$<?php echo $pines->com_sales->round($cur_product['return_fee'], true); ?></td>
			<?php } ?>
			<td valign="top" style="text-align: right;">$<?php echo $pines->com_sales->round($cur_product['line_total'] - (float) $cur_product['return_fee'], true); ?></td>
		</tr>
		<?php } } ?>
	</table>
	<?php if ($this->entity->specials) { ?>
	<br />
	<table width="100%" cellpadding="3" cellspacing="2" style="border-bottom: 1px solid #333;">
		<tr>
			<th style="background-color: #dcf0f7; color: #577887; font-weight: normal; text-align: left;">Specials</th>
			<th style="background-color: #dcf0f7; color: #577887; font-weight: normal; text-align: left;">Applied</th>
			<th style="background-color: #dcf0f7; color: #577887; font-weight: normal; text-align: right;">Discount</th>
		</tr>
		<?php foreach ($this->entity->specials as $cur_special) { ?>
		<tr>
			<td valign="top" style="text-align: left; width: 80%;"><?php echo htmlspecialchars(($cur_special['entity']->hide_code ? '' : "{$cur_special['code']} - ").$cur_special['name']); ?></td>
			<td valign="top" style="text-align: left; white-space: pre;"><?php echo $cur_special['before_tax'] ? 'Before Tax' : 'After Tax'; ?></td>
			<td valign="top" style="text-align: right;">$<?php echo $pines->com_sales->round($cur_special['discount'], true); ?></td>
		</tr>
		<?php } ?>
	</table>
	<?php } ?>
	<br />
	<table width="100%" cellpadding="0" cellspacing="0">
		<tr>
			<td align="right" valign="top">
				<table cellpadding="4" cellspacing="0">
					<tr>
						<td>Subtotal:</td>
						<td align="right" style="text-align: right;">$<?php echo $pines->com_sales->round($this->entity->subtotal, true); ?></td>
					</tr>
					<?php if ($this->entity->total_specials > 0) { ?>
					<tr>
						<td>Specials:</td>
						<td align="right" style="text-align: right;">($<?php echo $pines->com_sales->round($this->entity->total_specials, true); ?>)</td>
					</tr>
					<?php } if ($this->entity->item_fees > 0) { ?>
					<tr>
						<td>Item Fees:</td>
						<td align="right" style="text-align: right;">$<?php echo $pines->com_sales->round($this->entity->item_fees, true); ?></td>
					</tr>
					<?php } ?>
					<tr>
						<td>Tax:</td>
						<td align="right" style="text-align: right;">$<?php echo $pines->com_sales->round($this->entity->taxes, true); ?></td>
					</tr>
					<?php if ($this->entity->return_fees > 0) { ?>
					<tr>
						<td>Return Fees:</td>
						<td align="right" style="text-align: right;">($<?php echo $pines->com_sales->round($this->entity->return_fees, true); ?>)</td>
					</tr>
					<?php } ?>
					<tr>
						<td style="border-top: 1px solid #333;"><strong>Total:</strong></td>
						<td align="right" style="border-top: 1px solid #333; text-align: right;"><strong>$<?php echo $pines->com_sales->round($this->entity->total, true); ?></strong></td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	<?php if (is_array($this->entity->payments) && ($this->entity->status == 'paid' || $this->entity->status == 'processed' || $this->entity->status == 'voided')) { ?>
	<div style="border-bottom: 1px solid #333;">&nbsp;</div>
	<h2><?php if (!$sale) echo 'Returned ' ?>Payment Details</h2>
	<table cellpadding="4" cellspacing="0">
		<?php foreach ($this->entity->payments as $cur_payment) { ?>
		<tr>
			<td><?php echo htmlspecialchars($cur_payment['label']); ?>:</td>
			<td align="right" style="text-align: right;">$<?php echo $pines->com_sales->round($cur_payment['amount'], true); ?></td>
		</tr>
		<?php } ?>
		<tr>
			<td style="border-top: 1px solid #333;">Amount <?php echo $sale ? 'Tendered' : 'Refunded'; ?>:</td>
			<td align="right" style="border-top: 1px solid #333; text-align: right;">$<?php echo $pines->com_sales->round($this->entity->amount_tendered, true); ?></td>
		</tr>
		<?php if ($sale && $this->entity->change) { ?>
		<tr>
			<td>Change:</td>
			<td align="right" style="text-align: right;">$<?php echo $pines->com_sales->round($this->entity->change, true); ?></td>
		</tr>
		<?php } ?>
	</table>
	<?php } if (!empty($this->entity->comments)) { ?>
	<h3>Comments</h3>
	<p style="font-size: .85em;"><?php echo htmlspecialchars($this->entity->comments); ?></p>
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
	<h3><?php echo htmlspecialchars($label); ?></h3>
	<p style="font-size: .85em;"><?php echo htmlspecialchars($text); ?></p>
	<?php } ?>
</div>