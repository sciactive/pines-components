<?php
/**
 * Shows a quote, invoice, or receipt for a sale.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright Hunter Perrin
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
if ($this->entity->status == 'quoted') {
	$this->doc_title = 'Quote';
} elseif ($this->entity->status == 'invoiced') {
	$this->doc_title = 'Invoice';
} elseif ($this->entity->status == 'paid') {
	$this->doc_title = 'Receipt';
} else {
	$this->doc_title = 'Sale';
}
?>
<style type="text/css">
	/* <![CDATA[ */
	#receipt_sale .data_col .name {
		font-weight: bold;
	}
	#receipt_sale .left_side {
		margin-bottom: 10px;
		float: left;
		clear: left;
	}
	#receipt_sale .right_side {
		margin-bottom: 10px;
		float: right;
		clear: right;
	}
	#receipt_sale .barcode h1 {
		margin-bottom: 0px;
		padding-right: 15px;
		text-align: right;
	}
	#receipt_sale .barcode img {
		margin-top: 0px;
	}
	#receipt_sale .right_text {
		text-align: right;
	}
	#receipt_sale .left_side div, #receipt_sale .right_side div {
		float: left;
	}
	#receipt_sale .left_side .data_col, #receipt_sale .right_side .data_col {
		float: left;
		margin-left: 10px;
		padding-right: 15px;
	}
	#receipt_sale .left_side span, #receipt_sale .right_side span {
		display: block;
	}
	#receipt_sale .location .aligner, #receipt_sale .customer .aligner {
		width: 55px;
	}
	#receipt_sale #item_list {
		text-align: left;
		border-bottom: 1px solid black;
		border-collapse: collapse;
	}
	#receipt_sale #item_list th {
		border-bottom: 1px solid black;
		padding: 2px;
	}
	#receipt_sale #item_list tr td p {
		margin: 0;
	}
	#receipt_sale .receipt_note, #receipt_sale .comments {
		font-size: 75%;
	}
	/* ]]> */
</style>
<div id="receipt_sale" class="pform pform_twocol">
	<?php
	// Sales rep and sales group entities.
	$sales_rep = user::factory((int) $this->entity->uid);
	$sales_group = $sales_rep->group;
	// Set the location of the group logo image.
	if (isset($sales_group))
		$group_logo = $sales_group->get_logo();
	// Document id number.
	// TODO: Change this to an incremental naming scheme.
	$doc_id = $sales_group->groupname . strtoupper(substr($this->doc_title, 0, 2)) . $this->entity->guid;
	?>
	<div class="left_side">
		<span><img src="<?php echo $group_logo; ?>" alt="<?php echo $pines->page->get_title(); ?>" /></span>
	</div>
	<div class="right_side barcode">
		<h1><?php echo $this->doc_title; ?></h1>
		<img src="<?php echo pines_url('com_barcode', 'image', array('code' => $doc_id, 'width' => '250')); ?>" alt="Barcode" />
	</div>
	<?php if (isset($sales_rep->guid)) { ?>
	<div class="left_side location">
		<div class="aligner">Location:</div>
		<div class="data_col">
			<span class="name"><?php echo $sales_group->name; ?></span>
			<?php if ($sales_group->address_type == 'us') { ?>
			<span><?php echo "{$sales_group->address_1}\n{$sales_group->address_2}"; ?></span>
			<span><?php echo $sales_group->city; ?>, <?php echo $sales_group->state; ?> <?php echo $sales_group->zip; ?></span>
			<?php } else { ?>
			<span><?php echo $sales_group->address_international; ?></span>
			<?php } ?>
			<span><?php echo pines_phone_format($sales_group->phone); ?></span>
		</div>
	</div>
	<?php } ?>
	<div class="right_side receipt_info">
		<div class="info_labels right_text">
			<span><?php echo $this->doc_title; ?> #:</span>
			<span>Date:</span>
			<span>Time:</span>
			<?php if (isset($sales_rep->guid)) { ?>
			<span>Sales Person:</span>
			<?php } ?>
		</div>
		<div class="data_col">
			<span><?php echo $doc_id; ?></span>
			<?php switch($this->entity->status) {
				case 'invoiced':
					echo '<span>'.pines_date_format($this->entity->invoice_date, null, 'Y-m-d').'</span>';
					echo '<span>'.pines_date_format($this->entity->invoice_date, null, 'g:i A T').'</span>';
					break;
				case 'paid':
					echo '<span>'.pines_date_format($this->entity->tender_date, null, 'Y-m-d').'</span>';
					echo '<span>'.pines_date_format($this->entity->tender_date, null, 'g:i A T').'</span>';
					break;
				default:
					echo '<span>'.pines_date_format($this->entity->p_cdate, null, 'Y-m-d').'</span>';
					echo '<span>'.pines_date_format($this->entity->p_cdate, null, 'g:i A T').'</span>';
					break;
			} ?>
			<?php if (isset($sales_rep->guid)) { ?>
				<span><?php echo $sales_rep->name; ?></span>
			<?php } ?>
		</div>
	</div>
<?php if ($pines->com_sales->com_customer && !is_null($this->entity->customer)) { ?>
	<div class="left_side customer">
		<div class="aligner">
			<span>Bill To:</span>
		</div>
		<div class="data_col">
			<span><strong>
				<?php echo $this->entity->customer->name; ?>
				<?php if (isset($this->entity->customer->company)) { ?>
					( <?php echo $this->entity->customer->company->name; ?> )
				<?php } ?>
			</strong></span>
			<?php if ($this->entity->customer->address_type == 'us') { if (!empty($this->entity->customer->address_1)) { ?>
			<span><?php echo $this->entity->customer->address_1.' '.$this->entity->customer->address_2; ?></span>
			<span><?php echo $this->entity->customer->city; ?>, <?php echo $this->entity->customer->state; ?> <?php echo $this->entity->customer->zip; ?></span>
			<?php } } else {?>
			<span><?php echo $this->entity->customer->address_international; ?></span>
			<?php } ?>
		</div>
	</div>
<?php } ?>
	<div class="element full_width left_side">
		<table id="item_list" width="100%">
			<thead>
				<tr>
					<th>SKU</th>
					<th>Item</th>
					<th>Description</th>
					<th class="right_text">Qty</th>
					<th class="right_text">Price</th>
					<th class="right_text">Total</th>
				</tr>
			</thead>
			<tbody>
				<?php if (is_array($this->entity->products)) { foreach ($this->entity->products as $cur_product) { ?>
				<tr>
					<td><?php echo $cur_product['entity']->sku; ?></td>
					<td><?php echo $cur_product['entity']->name; ?></td>
					<td><?php echo $cur_product['entity']->short_description; ?></td>
					<td class="right_text"><?php echo $cur_product['quantity']; ?></td>
					<td class="right_text">$<?php echo $pines->com_sales->round($cur_product['price'], $pines->config->com_sales->dec, true); ?><?php echo empty($cur_product['discount']) ? '' : " - {$cur_product['discount']}"; ?></td>
					<td class="right_text">$<?php echo $pines->com_sales->round($cur_product['line_total'], $pines->config->com_sales->dec, true); ?></td>
				</tr>
				<?php } } ?>
			</tbody>
		</table>
	</div>
	<div class="element full_width">
		<?php if ($this->entity->status == 'paid' && is_array($this->entity->payments)) { ?>
		<div class="left_side">
			<div>
				<span><strong>Payments:</strong></span>
			</div>
			<hr style="clear: both;" />
			<div class="right_text">
				<?php foreach ($this->entity->payments as $cur_payment) { ?>
				<span><?php echo $cur_payment['label']; ?>:</span>
				<?php } ?>
				<hr style="visibility: hidden;" />
				<span>Amount Tendered:</span>
				<span>Change:</span>
			</div>
			<div class="data_col right_text">
				<?php foreach ($this->entity->payments as $cur_payment) { ?>
				<span>$<?php echo $pines->com_sales->round($cur_payment['amount'], $pines->config->com_sales->dec, true); ?></span>
				<?php } ?>
				<hr />
				<span>$<?php echo $pines->com_sales->round($this->entity->amount_tendered, $pines->config->com_sales->dec, true); ?></span>
				<span>$<?php echo $pines->com_sales->round($this->entity->change, $pines->config->com_sales->dec, true); ?></span>
			</div>
		</div>
		<?php } ?>
		<div class="right_side">
			<div>
				<span><strong>Totals:</strong></span>
			</div>
			<hr style="clear: both;" />
			<div class="right_text">
				<span>Subtotal:</span>
				<?php if ($this->entity->item_fees > 0) { ?>
				<span>Item Fees:</span>
				<?php } ?>
				<span>Tax:</span>
				<hr style="visibility: hidden;" />
				<span><strong>Total: </strong></span>
			</div>
			<div class="data_col right_text">
				<span>$<?php echo $pines->com_sales->round($this->entity->subtotal, $pines->config->com_sales->dec, true); ?></span>
				<?php if ($this->entity->item_fees > 0) { ?>
				<span>$<?php echo $pines->com_sales->round($this->entity->item_fees, $pines->config->com_sales->dec, true); ?></span>
				<?php } ?>
				<span>$<?php echo $pines->com_sales->round($this->entity->taxes, $pines->config->com_sales->dec, true); ?></span>
				<hr />
				<span><strong>$<?php echo $pines->com_sales->round($this->entity->total, $pines->config->com_sales->dec, true); ?></strong></span>
			</div>
		</div>
	</div>
	<?php if (!empty($this->entity->comments)) { ?>
	<div class="element full_width">
		<div class="field">
			<span class="label">Comments:</span>
			<br />
			<span class="field comments"><?php echo $this->entity->comments; ?></span>
		</div>
	</div>
	<?php } ?>
	<div class="element full_width">
		<div class="field">
			<span class="label"><?php
				switch ($this->entity->status) {
					case 'quoted':
						echo $pines->config->com_sales->quote_note_label;
						break;
					case 'invoiced':
						echo $pines->config->com_sales->invoice_note_label;
						break;
					case 'paid':
						echo $pines->config->com_sales->receipt_note_label;
						break;
				}
			?></span>
			<br />
			<div class="field receipt_note"><?php
				switch ($this->entity->status) {
					case 'quoted':
						echo $pines->config->com_sales->quote_note_text;
						break;
					case 'invoiced':
						echo $pines->config->com_sales->invoice_note_text;
						break;
					case 'paid':
						echo $pines->config->com_sales->receipt_note_text;
						break;
				}
			?></div>
		</div>
	</div>
</div>