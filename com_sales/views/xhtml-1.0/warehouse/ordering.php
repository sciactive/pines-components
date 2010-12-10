<?php
/**
 * Displays pending warehouse items.
 *
 * @package Pines
 * @subpackage com_sales
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 * @author Hunter Perrin <hunter@sciactive.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
defined('P_RUN') or die('Direct access prohibited');
$this->title = 'Pending Warehouse Orders';
?>
<div class="pf-form">
	<div class="pf-element pf-heading">
		<h1>Items That Need to be Ordered</h1>
	</div>
	<div style="padding: 1em;">
		<?php foreach ($this->products as $cur_product) { ?>
		<div class="pf-element pf-heading">
			<h1><a href="<?php echo htmlspecialchars(pines_url('com_sales', 'product/edit', array('id' => $cur_product['entity']->guid))); ?>" onclick="window.open(this.href); return false;"><?php echo htmlspecialchars($cur_product['entity']->name); ?></a> x <?php echo htmlspecialchars($cur_product['quantity']); ?></h1>
			<p>SKU: <?php echo htmlspecialchars($cur_product['entity']->sku); ?></p>
		</div>
		<div class="pf-element">
			<span class="pf-label">Sales</span>
			<span class="pf-note">This item appears on these sales.</span>
			<div class="pf-group">
				<?php if ($cur_product['sales']) {
					foreach ($cur_product['sales'] as $cur_sale) { ?>
				<div class="pf-field"><?php echo htmlspecialchars($cur_sale->id); ?> <a href="<?php echo htmlspecialchars(pines_url('com_sales', 'sale/receipt', array('id' => $cur_sale->guid))); ?>" onclick="window.open(this.href); return false;">Receipt</a> <a href="<?php echo htmlspecialchars(pines_url('com_sales', 'sale/edit', array('id' => $cur_sale->guid))); ?>" onclick="window.open(this.href); return false;">Edit</a> <?php echo htmlspecialchars("({$cur_sale->user->name}, {$cur_sale->group->name})"); ?></div>
				<?php }
				} else { ?>
				<span class="pf-field">Not found in any sales.</span>
				<?php } ?>
			</div>
		</div>
		<div class="pf-element">
			<span class="pf-label">Locations Available</span>
			<span class="pf-note">This item is in the inventory of these locations.</span>
			<?php if ($cur_product['locations']) {
				foreach ($cur_product['locations'] as $cur_location) { ?>
			<span class="pf-field"><?php echo htmlspecialchars("{$cur_location->name} [{$cur_location->groupname}]"); ?></span>
			<?php }
			} else { ?>
			<span class="pf-field">Not found at any location.</span>
			<?php } ?>
		</div>
		<div class="pf-element">
			<span class="pf-label">Vendors Available</span>
			<span class="pf-note">This item is available from these vendors.</span>
			<?php if ($cur_product['entity']->vendors) {
				foreach ($cur_product['entity']->vendors as $cur_vendor) { ?>
			<span class="pf-field"><?php if (!empty($cur_vendor['link'])) { ?><a href="<?php echo htmlspecialchars($cur_vendor['link']); ?>" onclick="window.open(this.href); return false;"><?php } ?><?php echo htmlspecialchars($cur_vendor['entity']->name); ?><?php if (!empty($cur_vendor['link'])) { ?></a><?php } ?> (Vendor SKU: <?php echo htmlspecialchars($cur_vendor['sku']); ?>, Cost: $<?php echo htmlspecialchars($cur_vendor['cost']); ?>)</span>
			<?php }
			} else { ?>
			<span class="pf-field">No vendors are saved for this product.</span>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
	<div class="pf-element pf-heading">
		<h1>Items Already Available/Ordered</h1>
	</div>
	<div class="pf-element">
		<span class="pf-label">Items in Warehouse Stock</span>
		<span class="pf-field"><?php echo htmlspecialchars($this->in_stock); ?></span>
	</div>
	<div class="pf-element">
		<span class="pf-label">Items in Incoming POs</span>
		<span class="pf-field"><?php echo htmlspecialchars($this->in_pos); ?></span>
	</div>
	<div class="pf-element">
		<span class="pf-label">Items in Incoming Transfers</span>
		<span class="pf-field"><?php echo htmlspecialchars($this->in_transfers); ?></span>
	</div>
</div>