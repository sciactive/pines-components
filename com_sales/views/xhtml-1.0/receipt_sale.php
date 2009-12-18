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
	$this->title = 'Quote';
} elseif ($this->entity->status == 'invoiced') {
	$this->title = 'Invoice';
} elseif ($this->entity->status == 'paid') {
	$this->title = 'Receipt';
} else {
	$this->title = 'Sale';
}
?>
<div id="receipt_sale" class="pform pform_twocol">
<?php if (isset($this->entity->p_cdate)) { ?>
	<div class="element">
		<span class="label">Ticket #</span>
		<span class="field"><?php echo $this->entity->guid; ?></span>
	</div>
<?php } ?>
<?php if (isset($this->entity->p_cdate)) { ?>
	<div class="element">
		<span class="label">Date</span>
		<span class="field"><?php echo date('Y-m-d', $this->entity->p_cdate); ?></span>
	</div>
<?php } ?>
<?php if (isset($this->entity->uid)) { ?>
	<div class="element">
		<span class="label">Sales Rep</span>
		<span class="field"><?php echo $config->user_manager->get_username($this->entity->uid); ?></span>
	</div>
<?php } ?>
<?php if (!is_null($this->entity->customer)) { ?>
	<div class="element heading">
		<h1>Customer</h1>
	</div>
	<div class="element">
		<span class="label">Customer</span>
		<span class="field"><?php echo $this->entity->customer->name; ?></span>
	</div>
	<?php if (!empty($this->entity->customer->email)) { ?>
		<div class="element">
			<span class="label">Email</span>
			<span class="field"><?php echo $this->entity->customer->email; ?></span>
		</div>
	<?php } ?>
	<?php if (!empty($this->entity->customer->company)) { ?>
		<div class="element">
			<span class="label">Company</span>
			<span class="field"><?php echo $this->entity->customer->company; ?></span>
		</div>
	<?php } ?>
	<?php if (!empty($this->entity->customer->address_1)) { ?>
		<div class="element">
			<span class="label">Address</span>
			<div class="group">
				<span class="field"><?php echo $this->entity->customer->address_1; ?></span>
				<?php if (!empty($this->entity->customer->address_2)) { ?>
				<span class="field"><?php echo $this->entity->customer->address_2; ?></span>
				<?php } ?>
				<span class="field"><?php echo $this->entity->customer->city; ?>, <?php echo $this->entity->customer->state; ?> <?php echo $this->entity->customer->zip; ?></span>
			</div>
		</div>
	<?php } ?>
	<?php if (!empty($this->entity->customer->phone_home) || !empty($this->entity->customer->phone_work) || !empty($this->entity->customer->phone_cell) || !empty($this->entity->customer->fax)) { ?>
		<div class="element">
			<span class="label">Phone</span>
			<span class="field">
				<?php if (!empty($this->entity->customer->phone_home)) { ?>
				Home: <?php echo $this->entity->customer->phone_home; ?>
				<?php } ?>
				<?php if (!empty($this->entity->customer->phone_work)) { ?>
				Work: <?php echo $this->entity->customer->phone_work; ?>
				<?php } ?>
				<?php if (!empty($this->entity->customer->phone_cell)) { ?>
				Cell: <?php echo $this->entity->customer->phone_cell; ?>
				<?php } ?>
				<?php if (!empty($this->entity->customer->fax)) { ?>
				Fax: <?php echo $this->entity->customer->fax; ?>
				<?php } ?>
			</span>
		</div>
	<?php } ?>
<?php } ?>
<div class="element heading">
	<h1>Products</h1>
</div>
<?php if (is_array($this->entity->products)) { foreach ($this->entity->products as $cur_product) { ?>
	<div class="element full_width">
		<span class="label"><?php echo $cur_product['entity']->name; ?></span>
		<?php if ($cur_product['delivery'] == 'shipped') { ?>
			<div class="note">This item is shipped.</div>
		<?php } ?>
		<div class="group">
			<div class="field" style="float: left; text-align: left;">
				<div>SKU: <?php echo $cur_product['entity']->sku; ?></div>
				<?php if (!empty($cur_product['serial'])) { ?>
				<div>Serial: <?php echo $cur_product['serial']; ?></div>
				<?php } ?>
			</div>
			<div class="field" style="float: right; text-align: right;">
				<div>x <?php echo $cur_product['quantity']; ?> at $<?php echo $config->run_sales->round($cur_product['price'], $config->com_sales->dec); ?><?php echo empty($cur_product['discount']) ? '' : " - {$cur_product['discount']}"; ?> = <?php echo $config->run_sales->round($cur_product['line_total'], $config->com_sales->dec); ?></div>
				<?php if ($cur_product['fees'] > 0.00) { ?>
				<div>(fees) <?php echo $config->run_sales->round($cur_product['fees'], $config->com_sales->dec); ?></div>
				<?php } ?>
			</div>
		</div>
	</div>
<?php } } ?>
<div class="element full_width">
	<span class="label">Ticket Totals</span>
	<div class="group">
		<div class="field" style="float: right; text-align: right;">
			<span class="label">Subtotal: </span><span class="field"><?php echo $config->run_sales->round($this->entity->subtotal, $config->com_sales->dec); ?></span><br />
			<span class="label">Item Fees: </span><span class="field"><?php echo $config->run_sales->round($this->entity->item_fees, $config->com_sales->dec); ?></span><br />
			<span class="label">Tax: </span><span class="field"><?php echo $config->run_sales->round($this->entity->taxes, $config->com_sales->dec); ?></span><br />
			<span class="label"><strong>Total</strong>: </span><span class="field"><?php echo $config->run_sales->round($this->entity->total, $config->com_sales->dec); ?></span>
		</div>
	</div>
</div>
<?php if ($this->entity->status == 'paid' && is_array($this->entity->payments)) { ?>
	<div class="element heading">
		<h1>Payments</h1>
	</div>
	<?php foreach ($this->entity->payments as $cur_payment) { ?>
	<div class="element full_width">
		<span class="label"><?php echo $cur_payment['entity']->name; ?></span>
		<div class="group">
			<div class="field" style="float: right; text-align: right;">
				<div><?php echo $config->run_sales->round($cur_payment['amount'], $config->com_sales->dec); ?></div>
			</div>
		</div>
	</div>
	<?php } ?>
	<div class="element full_width">
		<span class="label">Tendered</span>
		<div class="group">
			<div class="field" style="float: right; text-align: right;">
				<span class="label"><strong>Amount Tendered</strong>: </span><span class="field"><?php echo $config->run_sales->round($this->entity->amount_tendered, $config->com_sales->dec); ?></span><br />
				<span class="label"><strong>Change</strong>: </span><span class="field"><?php echo $config->run_sales->round($this->entity->change, $config->com_sales->dec); ?></span>
			</div>
		</div>
	</div>
<?php } ?>
</div>